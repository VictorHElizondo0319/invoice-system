<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating an invoice.
     */
    public function test_can_create_invoice(): void
    {
        $response = $this->postJson('/api/invoices', [
            'customer_name' => 'John Doe',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Web Development',
                    'qty' => 10,
                    'price' => 100.00
                ],
                [
                    'description' => 'Design Services',
                    'qty' => 5,
                    'price' => 50.00
                ]
            ]
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'invoice' => [
                         'id',
                         'customer_name',
                         'due_date',
                         'status',
                         'total_amount',
                         'items'
                     ]
                 ]);

        $this->assertDatabaseHas('invoices', [
            'customer_name' => 'John Doe',
            'total_amount' => 1250.00
        ]);
    }

    /**
     * Test validation when creating invoice.
     */
    public function test_invoice_validation(): void
    {
        $response = $this->postJson('/api/invoices', [
            'customer_name' => '',
            'due_date' => 'invalid-date',
            'items' => []
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_name', 'due_date', 'items']);
    }

    /**
     * Test retrieving an invoice.
     */
    public function test_can_retrieve_invoice(): void
    {
        $invoice = Invoice::factory()->create();
        InvoiceItem::factory()->count(2)->create(['invoice_id' => $invoice->id]);

        $response = $this->getJson("/api/invoices/{$invoice->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'customer_name',
                     'items',
                     'payments'
                 ]);
    }

    /**
     * Test submitting an invoice.
     */
    public function test_can_submit_invoice(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'DRAFT']);

        $response = $this->putJson("/api/invoices/{$invoice->id}/submit");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'SUBMITTED'
        ]);
    }

    /**
     * Test cannot submit already submitted invoice.
     */
    public function test_cannot_submit_non_draft_invoice(): void
    {
        $invoice = Invoice::factory()->create(['status' => 'SUBMITTED']);

        $response = $this->putJson("/api/invoices/{$invoice->id}/submit");

        $response->assertStatus(400);
    }

    /**
     * Test listing invoices with filters.
     */
    public function test_can_filter_invoices(): void
    {
        Invoice::factory()->create(['status' => 'PAID', 'customer_name' => 'Alice']);
        Invoice::factory()->create(['status' => 'SUBMITTED', 'customer_name' => 'Bob']);

        $response = $this->getJson('/api/invoices?status=PAID');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }
}


