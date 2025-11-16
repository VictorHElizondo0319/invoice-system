<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('items');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer name
        if ($request->has('customer_name')) {
            $query->where('customer_name', 'LIKE', '%' . $request->customer_name . '%');
        }

        $invoices = $query->latest()->get();

        return response()->json($invoices);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'due_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $invoice = Invoice::create([
                'customer_name' => $request->customer_name,
                'due_date' => $request->due_date,
                'status' => 'DRAFT'
            ]);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'price' => $item['price']
                ]);
                $totalAmount += $item['qty'] * $item['price'];
            }

            $invoice->update(['total_amount' => $totalAmount]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with('items', 'payments')->find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        return response()->json($invoice);
    }

    /**
     * Submit an invoice.
     */
    public function submit($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft invoices can be submitted'
            ], 400);
        }

        $invoice->update(['status' => 'SUBMITTED']);

        return response()->json([
            'message' => 'Invoice submitted successfully',
            'invoice' => $invoice
        ]);
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft invoices can be updated'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'sometimes|string|max:255',
            'due_date' => 'sometimes|date|after_or_equal:today',
            'items' => 'sometimes|array|min:1',
            'items.*.description' => 'required_with:items|string',
            'items.*.qty' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $invoice->update($request->only(['customer_name', 'due_date']));

            if ($request->has('items')) {
                // Delete old items
                $invoice->items()->delete();

                // Create new items
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'qty' => $item['qty'],
                        'price' => $item['price']
                    ]);
                    $totalAmount += $item['qty'] * $item['price'];
                }

                $invoice->update(['total_amount' => $totalAmount]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->status === 'PAID') {
            return response()->json([
                'message' => 'Paid invoices cannot be deleted'
            ], 400);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }
}


