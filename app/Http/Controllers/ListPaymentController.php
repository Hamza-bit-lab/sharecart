<?php

namespace App\Http\Controllers;

use App\Models\GroceryList;
use App\Models\ListPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListPaymentController extends Controller
{
    /**
     * Add a payment to a list via web.
     */
    public function store(Request $request, GroceryList $list): RedirectResponse
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
            $guestNames = $request->session()->get('guest_names', []);
            $paymentData['guest_name'] = $guestNames[$list->id] ?? 'Guest';
        }

        $list->payments()->create($paymentData);

        return redirect()->route('lists.show', $list)->with('success', 'Payment added.');
    }

    /**
     * Update a payment.
     */
    public function update(Request $request, GroceryList $list, ListPayment $payment): RedirectResponse
    {
        if ($payment->list_id !== $list->id) {
            abort(404);
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

        return redirect()->route('lists.show', $list)->with('success', 'Payment updated.');
    }

    /**
     * Remove a payment.
     */
    public function destroy(Request $request, GroceryList $list, ListPayment $payment): RedirectResponse
    {
        if ($payment->list_id !== $list->id) {
            abort(404);
        }

        $this->authorize('update', $list);

        $payment->delete();

        return redirect()->route('lists.show', $list)->with('success', 'Payment removed.');
    }
}
