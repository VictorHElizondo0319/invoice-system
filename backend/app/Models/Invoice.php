<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_id',
        'due_date',
        'status',
        'total_amount',
        'paid_amount',
        'invoice_pdf_url',
        'pdf_uploaded_at',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    protected $casts = [
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function calculateTotal()
    {
        return $this->items()->sum(\DB::raw('qty * price'));
    }

    public function isPaid()
    {
        return $this->status === 'PAID';
    }

    public function getOutstandingAmountAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
}


