<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get summary analytics/KPIs.
     */
    public function summary()
    {
        $totalInvoices = Invoice::count();
        $totalPaid = Invoice::where('status', 'PAID')->count();
        $totalUnpaid = Invoice::whereIn('status', ['DRAFT', 'SUBMITTED', 'OVERDUE'])->count();
        
        $totalRevenue = Invoice::where('status', 'PAID')->sum('total_amount');
        $totalOutstanding = Invoice::whereIn('status', ['SUBMITTED', 'OVERDUE'])
            ->sum(DB::raw('total_amount - paid_amount'));
        
        $revenueByStatus = Invoice::select('status', DB::raw('SUM(total_amount) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'total_revenue' => number_format($totalRevenue, 2, '.', ''),
            'total_outstanding' => number_format($totalOutstanding, 2, '.', ''),
            'revenue_by_status' => $revenueByStatus
        ]);
    }

    /**
     * Get detailed analytics.
     */
    public function analytics()
    {
        $invoicesByStatus = Invoice::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $recentInvoices = Invoice::with('items')
            ->latest()
            ->limit(10)
            ->get();

        $overdueInvoices = Invoice::where('due_date', '<', now())
            ->whereIn('status', ['SUBMITTED'])
            ->count();

        return response()->json([
            'invoices_by_status' => $invoicesByStatus,
            'recent_invoices' => $recentInvoices,
            'overdue_invoices' => $overdueInvoices
        ]);
    }
}


