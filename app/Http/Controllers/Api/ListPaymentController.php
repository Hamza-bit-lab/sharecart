<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GroceryList;
use App\Models\ListPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListPaymentController extends Controller
{
    /**
     * Get all payments for a list.
     */
    public function index(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('view', $list);
        }

        $payments = $list->payments()
            ->with('user')
            ->orderBy('paid_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments->map(fn($p) => $this->serializePayment($p))->values(),
            ],
        ]);
    }

    /**
     * Add a payment to a list.
     */
    public function store(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $paymentData = [
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency'] ?? 'EUR'),
            'paid_at' => $data['paid_at'] ?? now(),
        ];

        if ($request->user()) {
            $paymentData['user_id'] = $request->user()->id;
            $paymentData['guest_name'] = null;
        } else {
            $paymentData['user_id'] = null;
            $paymentData['guest_name'] = $request->attributes->get('guest_display_name');
        }

        $payment = $list->payments()->create($paymentData);

        return response()->json([
            'success' => true,
            'message' => 'Payment added.',
            'data' => [
                'payment' => $this->serializePayment($payment->fresh('user')),
            ],
        ], 201);
    }

    /**
     * Update a payment.
     */
    public function update(Request $request, GroceryList $list, ListPayment $payment): JsonResponse
    {
        if ($payment->list_id !== $list->id) {
            return response()->json(['message' => 'Payment not found in this list.'], 404);
        }

        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payment->update([
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency'] ?? $payment->currency),
            'paid_at' => $data['paid_at'] ?? $payment->paid_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated.',
            'data' => [
                'payment' => $this->serializePayment($payment->fresh('user')),
            ],
        ]);
    }

    /**
     * Remove a payment.
     */
    public function destroy(Request $request, GroceryList $list, ListPayment $payment): JsonResponse
    {
        if ($payment->list_id !== $list->id) {
            return response()->json(['message' => 'Payment not found in this list.'], 404);
        }

        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment removed.',
        ]);
    }

    protected function serializePayment(ListPayment $payment): array
    {
        return [
            'id' => $payment->id,
            'list_id' => $payment->list_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => $payment->paid_at->toIso8601String(),
            'created_at' => $payment->created_at->toIso8601String(),
            'payer' => $payment->user ? [
                'id' => $payment->user->id,
                'name' => $payment->user->name,
            ] : [
                'id' => null,
                'name' => $payment->guest_name,
            ],
        ];
    }
}
