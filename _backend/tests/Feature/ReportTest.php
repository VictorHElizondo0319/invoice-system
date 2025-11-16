<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Invoice;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting summary report.
     */
    public function test_can_get_summary_report(): void
    {
        Invoice::factory()->count(3)->create(['status' => 'PAID', 'total_amount' => 1000]);
        Invoice::factory()->count(2)->create(['status' => 'SUBMITTED', 'total_amount' => 500]);
        Invoice::factory()->create(['status' => 'DRAFT', 'total_amount' => 200]);

        $response = $this->getJson('/api/reports/summary');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_invoices',
                     'total_paid',
                     'total_unpaid',
                     'total_revenue',
                     'total_outstanding',
                     'revenue_by_status'
                 ]);

        $data = $response->json();
        $this->assertEquals(6, $data['total_invoices']);
        $this->assertEquals(3, $data['total_paid']);
    }

    /**
     * Test health check endpoint.
     */
    public function test_health_check(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'timestamp',
                     'service',
                     'checks' => [
                         'database',
                         'application'
                     ]
                 ]);
    }
}


