<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Invoice::with('items', 'customer');

        // Only admins see all invoices; regular users see invoices where they are the customer
        if ($user && $user->role !== 'admin') {
            $query->where('customer_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer id or name (admin use)
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        } elseif ($request->has('customer_name')) {
            $name = $request->customer_name;
            $query->whereHas('customer', function($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%');
            });
        }

        $invoices = $query->latest()->get();

        // Attach a public PDF URL for any invoice that has an S3 key/url saved.
        foreach ($invoices as $inv) {
            if ($inv->invoice_pdf_url) {
                $stored = $inv->invoice_pdf_url;
                if (filter_var($stored, FILTER_VALIDATE_URL)) {
                    $inv->pdf_url = $stored;
                } else {
                    $bucketUrl = config('filesystems.disks.s3.url');
                    if ($bucketUrl) {
                        $inv->pdf_url = rtrim($bucketUrl, '/') . '/' . $stored;
                    } else {
                        $region = config('filesystems.disks.s3.region');
                        $bucket = config('filesystems.disks.s3.bucket');
                        $inv->pdf_url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$stored}";
                    }
                }
            }
        }

        return response()->json($invoices);
    }

    /**
     * Export the specified invoice as a PDF.
     */
    public function export($id)
    {
        $invoice = Invoice::with('items', 'payments', 'customer')->find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found'
            ], 404);
        }

        // Only allow export if requester is admin or owner (creator) or the customer
        $user = auth()->user();
        if ($user && $user->role !== 'admin' && $invoice->user_id !== $user->id && $invoice->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Only paid invoices can be exported/uploaded
        if ($invoice->status !== 'PAID') {
            return response()->json(['message' => 'Only paid invoices can be exported'], 400);
        }

        try {
            $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice])
                ->setPaper('a4', 'portrait');

            $content = $pdf->output();
            $filename = 'invoice_' . $invoice->id . '_' . time() . '.pdf';
            $path = 'invoices/' . $filename;

            Storage::disk('s3')->put($path, $content);
            // Build the public URL (using AWS_URL from config if set, else default S3 URL)
            $bucketUrl = config('filesystems.disks.s3.url');
            if ($bucketUrl) {
                $publicUrl = rtrim($bucketUrl, '/') . '/' . $path;
            } else {
                $region = config('filesystems.disks.s3.region');
                $bucket = config('filesystems.disks.s3.bucket');
                $publicUrl = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
            }

            // Save the S3 key/path (not the full URL) and timestamp on invoice record
            $invoice->update([
                'invoice_pdf_url' => $path,
                'pdf_uploaded_at' => now(),
            ]);

            // Attach public URL to invoice model for response
            $invoice->pdf_url = $publicUrl;

            return response()->json([
                'message' => 'PDF generated and uploaded',
                'url' => $publicUrl,
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate or upload PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:users,id',
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

            $user = auth()->user();
            // Only admins can create invoices (route also protected)
            if ($user && $user->role !== 'admin') {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'customer_name' => optional(\App\Models\User::find($request->customer_id))->name,
                'due_date' => $request->due_date,
                'status' => 'DRAFT',
                'user_id' => $user ? $user->id : null,
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

        // If an S3 key/path is stored in invoice_pdf_url, compute the public URL for clients
        if ($invoice->invoice_pdf_url) {
            $stored = $invoice->invoice_pdf_url;
            // If the stored value already looks like a full URL, use it directly
            if (filter_var($stored, FILTER_VALIDATE_URL)) {
                $invoice->pdf_url = $stored;
            } else {
                $bucketUrl = config('filesystems.disks.s3.url');
                if ($bucketUrl) {
                    $invoice->pdf_url = rtrim($bucketUrl, '/') . '/' . $stored;
                } else {
                    $region = config('filesystems.disks.s3.region');
                    $bucket = config('filesystems.disks.s3.bucket');
                    $invoice->pdf_url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$stored}";
                }
            }
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

        // Only admins can submit invoices
        $user = auth()->user();
        if ($user && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
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

        // Only admins can update invoices
        $user = auth()->user();
        if ($user && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($invoice->status !== 'DRAFT') {
            return response()->json([
                'message' => 'Only draft invoices can be updated'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|exists:users,id',
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

            $updateData = $request->only(['customer_id', 'due_date']);
            if (isset($updateData['customer_id'])) {
                $updateData['customer_name'] = optional(\App\Models\User::find($updateData['customer_id']))->name;
            }
            $invoice->update($updateData);

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

        // Only admins can delete invoices (also enforced by middleware)
        $user = auth()->user();
        if ($user && $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
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


