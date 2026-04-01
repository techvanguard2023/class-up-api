<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EvolutionApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.evolution.url');
        $this->apiKey = config('services.evolution.key');

        if (!$this->baseUrl || !$this->apiKey) {
            throw new Exception('Evolution API URL or Key not configured.');
        }
    }

    /**
     * Create a new WhatsApp instance
     *
     * @param string $instanceName
     * @param array $config
     * @return array
     */
    public function createInstance(string $instanceName, array $config = []): array
    {
        $payload = array_merge(
            [
                'instanceName' => $instanceName,
                'integration' => 'WHATSAPP-BAILEYS',
                'qrcode' => true,
                'rejectCall' => true,
                'groupsIgnore' => true,
                'alwaysOnline' => true,
                'readMessages' => true,
                'readStatus' => true,
                'syncFullHistory' => true,
            ],
            $config
        );

        return $this->makeRequest('POST', '/instance/create', $payload);
    }

    /**
     * Fetch all instances
     *
     * @return array
     */
    public function fetchInstances(): array
    {
        return $this->makeRequest('GET', '/instance/fetchInstances');
    }

    /**
     * Get instance information
     *
     * @return array
     */
    public function getInformation(): array
    {
        return $this->makeRequest('GET', '/');
    }

    /**
     * Connect to an instance (get QR code and pairing code)
     *
     * @param string $instanceName
     * @return array
     */
    public function connectInstance(string $instanceName): array
    {
        return $this->makeRequest('GET', "/instance/connect/{$instanceName}");
    }

    /**
     * Delete an instance
     *
     * @param string $instanceName
     * @return array
     */
    public function deleteInstance(string $instanceName): array
    {
        return $this->makeRequest('DELETE', "/instance/delete/{$instanceName}");
    }

    /**
     * Set webhook for an instance
     *
     * @param string $instanceName
     * @param string $url
     * @param array $events
     * @param bool $enabled
     * @param bool $webhookByEvents
     * @param bool $webhookBase64
     * @return array
     */
    public function setWebhook(
        string $instanceName,
        string $url,
        array $events = ['MESSAGES_UPSERT', 'MESSAGES_UPDATE', 'MESSAGES_DELETE'],
        bool $enabled = true,
        bool $webhookByEvents = true,
        bool $webhookBase64 = true
    ): array {
        $payload = [
            'webhook' => [
                'enabled' => (bool) $enabled,
                'url' => $url,
                'webhookByEvents' => (bool) $webhookByEvents,
                'webhookBase64' => (bool) $webhookBase64,
                'events' => $events,
            ]
        ];

        return $this->makeRequest('POST', "/webhook/set/{$instanceName}", $payload);
    }

    /**
     * Find webhook configuration for an instance
     *
     * @param string $instanceName
     * @return array
     */
    public function findWebhook(string $instanceName): array
    {
        return $this->makeRequest('GET', "/webhook/find/{$instanceName}");
    }

    /**
     * Get connection state of an instance
     *
     * @param string $instanceName
     * @return array
     */
    public function getConnectionState(string $instanceName): array
    {
        return $this->makeRequest('GET', "/instance/connectionState/{$instanceName}");
    }

    /**
     * Make HTTP request to Evolution API
     *
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @return array
     */
    private function makeRequest(string $method, string $endpoint, ?array $payload = null): array
    {
        try {
            $url = $this->baseUrl . $endpoint;

            $request = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ]);

            if ($method === 'POST' || $method === 'PUT') {
                $response = $request->{strtolower($method)}($url, $payload ?? []);
            } else {
                $response = $request->{strtolower($method)}($url);
            }

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Evolution API Error', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'status' => $response->status(),
                    'error' => $data['error'] ?? 'Unknown error',
                    'message' => $data['message'] ?? $response->body(),
                ];
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (Exception $e) {
            Log::error('Evolution API Exception', [
                'method' => $method,
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Exception',
                'message' => $e->getMessage(),
            ];
        }
    }
}
