<?php
namespace Tests\Feature\Pharmacy;

use App\Models\User;
use App\Models\Pharmacy\DrugInteraction;
use App\Models\Pharmacy\Generic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugDrugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug(): void
    {
        file_put_contents('php://stderr', "Route URL: " . route('pharmacy.drug-interactions.index') . "\n");
        
        $user = User::factory()->create();
        $g1 = Generic::create(['name' => 'A', 'is_active' => true]);
        $g2 = Generic::create(['name' => 'B', 'is_active' => true]);

        $interaction = DrugInteraction::create([
            'generic_id_1' => $g1->id,
            'generic_id_2' => $g2->id,
            'severity' => 'minor',
            'description' => 'Test',
        ]);

        file_put_contents('php://stderr', "Update route: " . route('pharmacy.drug-interactions.update', $interaction) . "\n");

        $response = $this->actingAs($user)->put(
            route('pharmacy.drug-interactions.update', $interaction),
            [
                'generic_id_1' => $g1->id,
                'generic_id_2' => $g2->id,
                'severity' => 'severe',
                'description' => 'Severe interaction',
                'management' => 'Do not co-prescribe',
            ]
        );

        file_put_contents('php://stderr', "Redirect URL: " . ($response->isRedirection() ? $response->headers->get('Location') : 'N/A') . "\n");

        $interaction->refresh();
        file_put_contents('php://stderr', "Severity: " . $interaction->severity . "\n");

        $this->assertTrue(true);
    }
}
