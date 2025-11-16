<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test processing a payment for an invoice.
     */
    public function test_can_process_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'SUBMITTED',
            'total_amount' => 1000.00,
            'paid_amount' => 0
        ]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000.00
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'payment' => [
                         'id',
                         'invoice_id',
                         'amount',
                         'transaction_id',
                         'status'
                     ],
                     'invoice'
                 ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
            'status' => 'SUCCESS'
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'PAID',
            'paid_amount' => 1000.00
        ]);
    }

    /**
     * Test partial payment.
     */
    public function test_can_process_partial_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'SUBMITTED',
            'total_amount' => 1000.00,
            'paid_amount' => 0
        ]);

        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500.00
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'SUBMITTED',
            'paid_amount' => 500.00
        ]);
    }

    /**
     * Test cannot pay draft invoice.
     */
    public function test_cannot_pay_draft_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'DRAFT',
            'total_amount' => 1000.00
        ]);

        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000.00
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'message' => 'Cannot pay a draft invoice. Please submit it first.'
                 ]);
    }

    /**
     * Test cannot overpay invoice.
     */
    public function test_cannot_overpay_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'SUBMITTED',
            'total_amount' => 1000.00,
            'paid_amount' => 0
        ]);

        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1500.00
        ]);

        $response->assertStatus(400)
                 ->assertJsonFragment([
                     'message' => 'Payment amount exceeds outstanding balance'
                 ]);
    }

    /**
     * Test cannot pay already paid invoice.
     */
    public function test_cannot_pay_already_paid_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'PAID',
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00
        ]);

        $response = $this->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100.00
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test payment validation.
     */
    public function test_payment_validation(): void
    {
        $response = $this->postJson('/api/payments', [
            'invoice_id' => 999,
            'amount' => -50
        ]);

        $response->assertStatus(422);
    }
}


