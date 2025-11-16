<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'qty',
        'price'
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->qty * $this->price;
    }
}


