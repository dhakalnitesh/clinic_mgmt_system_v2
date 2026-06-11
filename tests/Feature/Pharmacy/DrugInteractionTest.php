<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Pharmacy\DrugInteraction;
use App\Models\Pharmacy\Generic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrugInteractionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Generic $generic1;
    private Generic $generic2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->generic1 = Generic::create(['name' => 'Paracetamol', 'is_active' => true]);
        $this->generic2 = Generic::create(['name' => 'Warfarin', 'is_active' => true]);
    }

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('pharmacy.drug-interactions.index'));

        $response->assertOk();
    }

    public function test_interaction_can_be_created(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('pharmacy.drug-interactions.store'),
            [
                'generic_id_1' => $this->generic1->id,
                'generic_id_2' => $this->generic2->id,
                'severity' => 'moderate',
                'description' => 'Increased bleeding risk',
                'management' => 'Monitor INR closely',
            ]
        );

        $this->assertDatabaseHas('drug_interactions', [
            'generic_id_1' => $this->generic1->id,
            'generic_id_2' => $this->generic2->id,
            'severity' => 'moderate',
        ]);

        $response->assertRedirect();
    }

    public function test_interaction_can_be_updated(): void
    {
        $interaction = DrugInteraction::create([
            'generic_id_1' => $this->generic1->id,
            'generic_id_2' => $this->generic2->id,
            'severity' => 'minor',
            'description' => 'Minor interaction',
        ]);

        $response = $this->actingAs($this->user)->put(
            route('pharmacy.drug-interactions.update', $interaction),
            [
                'generic_id_1' => $this->generic1->id,
                'generic_id_2' => $this->generic2->id,
                'severity' => 'severe',
                'description' => 'Severe interaction - contraindicated',
                'management' => 'Do not co-prescribe',
            ]
        );

        $this->assertDatabaseHas('drug_interactions', [
            'id' => $interaction->id,
            'severity' => 'severe',
        ]);

        $response->assertRedirect();
    }

    public function test_interaction_can_be_deleted(): void
    {
        $interaction = DrugInteraction::create([
            'generic_id_1' => $this->generic1->id,
            'generic_id_2' => $this->generic2->id,
            'severity' => 'minor',
            'description' => 'Test',
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('pharmacy.drug-interactions.destroy', $interaction)
        );

        $this->assertDatabaseMissing('drug_interactions', ['id' => $interaction->id]);
        $response->assertRedirect();
    }
}
