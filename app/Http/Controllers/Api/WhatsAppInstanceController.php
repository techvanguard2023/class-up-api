<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EvolutionApiService;
use App\Models\WhatsAppInstance;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WhatsAppInstanceController extends Controller
{
    private EvolutionApiService $evolutionService;

    public function __construct(EvolutionApiService $evolutionService)
    {
        $this->evolutionService = $evolutionService;
    }

    /**
     * Create a new WhatsApp instance for a school
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'webhook_url' => 'nullable|url',
            'token' => 'nullable|string',
            'number' => 'nullable|string',
        ]);

        $school = School::findOrFail($request->school_id);
        $instanceName = $school->slug;

        $config = [];
        if ($request->webhook_url) {
            $config['webhook'] = [
                'url' => $request->webhook_url,
                'byEvents' => true,
                'base64' => true,
                'events' => ['MESSAGES_UPSERT', 'MESSAGES_UPDATE', 'MESSAGES_DELETE'],
            ];
        }

        if ($request->token) {
            $config['token'] = $request->token;
        }

        if ($request->number) {
            $config['number'] = $request->number;
        }

        try {
            $response = $this->evolutionService->createInstance($instanceName, $config);

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao criar instância',
                    'error' => $response['message'],
                ], 400);
            }

            $instanceData = $response['data']['response'] ?? $response['data'];

            WhatsAppInstance::create([
                'school_id' => $school->id,
                'instance_name' => $instanceName,
                'instance_id' => $instanceData['instanceId'] ?? null,
                'api_key' => $instanceData['apikey'] ?? null,
                'status' => 'pending',
                'server_url' => $instanceData['serverUrl'] ?? null,
                'integration' => $instanceData['integration'] ?? 'WHATSAPP-BAILEYS',
                'webhook_url' => $request->webhook_url,
            ]);

            return response()->json([
                'message' => 'Instância criada com sucesso',
                'data' => $instanceData,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar instância',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all instances for a school
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexSchoolInstances(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $instances = WhatsAppInstance::where('school_id', $request->school_id)->get();

        return response()->json([
            'data' => $instances,
        ]);
    }

    /**
     * Fetch all instances from Evolution API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAll()
    {
        try {
            $response = $this->evolutionService->fetchInstances();

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao buscar instâncias',
                    'error' => $response['message'],
                ], 400);
            }

            return response()->json([
                'data' => $response['data']['response'] ?? $response['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar instâncias',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get API information
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfo()
    {
        try {
            $response = $this->evolutionService->getInformation();

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao obter informações',
                    'error' => $response['message'],
                ], 400);
            }

            return response()->json([
                'data' => $response['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter informações',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Connect to an instance (get QR code)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connect(Request $request)
    {
        $request->validate([
            'instance_name' => 'required|string',
        ]);

        try {
            $response = $this->evolutionService->connectInstance($request->instance_name);

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao conectar instância',
                    'error' => $response['message'],
                ], 400);
            }

            WhatsAppInstance::where('instance_name', $request->instance_name)
                ->update(['status' => 'connecting']);

            return response()->json([
                'message' => 'Instância conectada com sucesso',
                'data' => $response['data']['response'] ?? $response['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao conectar instância',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an instance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $request->validate([
            'instance_name' => 'required|string',
        ]);

        try {
            $response = $this->evolutionService->deleteInstance($request->instance_name);

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao deletar instância',
                    'error' => $response['message'],
                ], 400);
            }

            WhatsAppInstance::where('instance_name', $request->instance_name)->delete();

            return response()->json([
                'message' => 'Instância deletada com sucesso',
                'data' => $response['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar instância',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set webhook for an instance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setWebhook(Request $request)
    {
        $request->validate([
            'instance_name' => 'required|string',
            'url' => 'required|url',
            'events' => 'array',
            'events.*' => 'string',
            'enabled' => 'boolean',
            'webhookByEvents' => 'boolean',
            'webhookBase64' => 'boolean',
        ]);

        try {
            $response = $this->evolutionService->setWebhook(
                instanceName: $request->instance_name,
                url: $request->url,
                events: $request->input('events', ['MESSAGES_UPSERT', 'MESSAGES_UPDATE', 'MESSAGES_DELETE']),
                enabled: $request->boolean('enabled', true),
                webhookByEvents: $request->boolean('webhookByEvents', true),
                webhookBase64: $request->boolean('webhookBase64', true)
            );

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao configurar webhook',
                    'error' => $response['message'],
                ], 400);
            }

            WhatsAppInstance::where('instance_name', $request->instance_name)
                ->update(['webhook_url' => $request->url, 'status' => 'connected']);

            return response()->json([
                'message' => 'Webhook configurado com sucesso',
                'data' => $response['data']['response'] ?? $response['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao configurar webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get webhook configuration for an instance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWebhook(Request $request)
    {
        $request->validate([
            'instance_name' => 'required|string',
        ]);

        try {
            $response = $this->evolutionService->findWebhook($request->instance_name);

            if (!$response['success']) {
                return response()->json([
                    'message' => 'Falha ao obter webhook',
                    'error' => $response['message'],
                ], 400);
            }

            return response()->json([
                'message' => 'Webhook obtido com sucesso',
                'data' => $response['data']['response'] ?? $response['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
