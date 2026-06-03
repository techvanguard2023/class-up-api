<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Income;
use App\Models\PaymentNotification;
use App\Models\StudentPaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuardianPaymentController extends Controller
{
    /**
     * Responsável informa um pagamento realizado.
     * POST /guardians/subscriptions/payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'         => 'required|integer|exists:students,id',
            'payment_plan_id'    => 'required|integer|exists:student_payment_plans,id',
            'payment_date'       => 'required|date',
            'amount'             => 'required|numeric|min:0.01',
            'notes'              => 'nullable|string|max:500',
            'receipt'            => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user     = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['message' => 'Responsável não encontrado.'], 404);
        }

        $studentPaymentPlan = StudentPaymentPlan::with('schoolPaymentPlan')
            ->where('id', $validated['payment_plan_id'])
            ->where('student_id', $validated['student_id'])
            ->first();

        if (!$studentPaymentPlan) {
            return response()->json(['message' => 'Plano de pagamento não encontrado.'], 404);
        }

        // Upload do comprovante se enviado
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $notification = PaymentNotification::create([
            'school_id'               => $guardian->school_id,
            'student_id'              => $validated['student_id'],
            'student_payment_plan_id' => $validated['payment_plan_id'],
            'guardian_id'             => $guardian->id,
            'amount'                  => $validated['amount'],
            'payment_date'            => $validated['payment_date'],
            'notes'                   => $validated['notes'] ?? null,
            'receipt_path'            => $receiptPath,
            'status'                  => 'pending',
        ]);

        return response()->json([
            'message'      => 'Pagamento informado com sucesso! Aguarde a confirmação da escola.',
            'notification' => $notification->load(['student', 'guardian', 'studentPaymentPlan.schoolPaymentPlan']),
        ], 201);
    }

    /**
     * Responsável lista seus pagamentos informados.
     * GET /guardians/subscriptions/payment
     */
    public function index(Request $request)
    {
        $user     = $request->user();
        $guardian = Guardian::where('user_id', $user->id)->first();

        if (!$guardian) {
            return response()->json(['message' => 'Responsável não encontrado.'], 404);
        }

        $notifications = PaymentNotification::where('guardian_id', $guardian->id)
            ->with(['student', 'studentPaymentPlan.schoolPaymentPlan', 'confirmedBy'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Admin lista TODAS as notificações de pagamento pendentes da escola.
     * GET /payment-notifications
     */
    public function adminIndex(Request $request)
    {
        $user   = $request->user();
        $school = $user->school;

        $status = $request->query('status', 'pending');

        $query = PaymentNotification::where('school_id', $school->id)
            ->with(['student', 'guardian', 'studentPaymentPlan.schoolPaymentPlan', 'confirmedBy'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return response()->json($query->get());
    }

    /**
     * Admin confirma pagamento → cria registro em incomes.
     * POST /payment-notifications/{id}/confirm
     */
    public function confirm(Request $request, $id)
    {
        $user   = $request->user();
        $school = $user->school;

        $notification = PaymentNotification::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($notification->status !== 'pending') {
            return response()->json(['message' => 'Esta notificação já foi processada.'], 422);
        }

        // Confirma a notificação
        $notification->update([
            'status'       => 'confirmed',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ]);

        // Cria o registro de receita automaticamente
        Income::create([
            'school_id'      => $school->id,
            'description'    => 'Mensalidade - ' . $notification->student->name . ' (' . ($notification->studentPaymentPlan->schoolPaymentPlan->name ?? 'Plano') . ')',
            'amount'         => $notification->amount,
            'category'       => 'mensalidade',
            'date'           => $notification->payment_date,
            'payment_method' => 'informado_pelo_responsavel',
            'received_from'  => $notification->guardian->name,
            'status'         => 'received',
            'notes'          => $notification->notes,
        ]);

        return response()->json([
            'message'      => 'Pagamento confirmado e lançado nas receitas com sucesso!',
            'notification' => $notification->fresh(['student', 'guardian', 'confirmedBy']),
        ]);
    }

    /**
     * Admin rejeita pagamento informado.
     * POST /payment-notifications/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user   = $request->user();
        $school = $user->school;

        $notification = PaymentNotification::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($notification->status !== 'pending') {
            return response()->json(['message' => 'Esta notificação já foi processada.'], 422);
        }

        $notification->update([
            'status'           => 'rejected',
            'confirmed_by'     => $user->id,
            'confirmed_at'     => now(),
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'message'      => 'Pagamento rejeitado.',
            'notification' => $notification->fresh(),
        ]);
    }
}
