<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Process a mock payment for an invoice.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $invoice = Invoice::find($request->invoice_id);

            if ($invoice->status === 'DRAFT') {
                return response()->json([
                    'message' => 'Cannot pay a draft invoice. Please submit it first.'
                ], 400);
            }

            if ($invoice->status === 'PAID') {
                return response()->json([
                    'message' => 'Invoice is already paid'
                ], 400);
            }

            $outstandingAmount = $invoice->total_amount - $invoice->paid_amount;

            if ($request->amount > $outstandingAmount) {
                return response()->json([
                    'message' => 'Payment amount exceeds outstanding balance',
                    'outstanding_amount' => $outstandingAmount
                ], 400);
            }

            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'payment_method' => 'mock',
                'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                'status' => 'SUCCESS'
            ]);

            // Update invoice
            $newPaidAmount = $invoice->paid_amount + $request->amount;
            $invoice->paid_amount = $newPaidAmount;

            if ($newPaidAmount >= $invoice->total_amount) {
                $invoice->status = 'PAID';
            }

            $invoice->save();

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'invoice' => $invoice->load('items', 'payments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for an invoice.
     */
    public function index(Request $request)
    {
        $query = Payment::with('invoice');

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        $payments = $query->latest()->get();

        return response()->json($payments);
    }
}


