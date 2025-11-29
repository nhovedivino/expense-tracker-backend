<?php

namespace Tests\Feature;

use App\Models\Saving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        return [$user, $token];
    }

    public function test_user_can_create_saving()
    {
        [$user, $token] = $this->authenticatedUser();

        $savingData = [
            'amount' => 1000.50,
            'description' => 'Monthly salary savings',
            'date' => '2024-01-15'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/savings', $savingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'saving' => ['id', 'amount', 'description', 'date', 'user_id']
            ]);

        $this->assertDatabaseHas('savings', [
            'user_id' => $user->id,
            'amount' => 1000.50,
            'description' => 'Monthly salary savings'
        ]);
    }

    public function test_user_cannot_create_saving_with_invalid_data()
    {
        [$user, $token] = $this->authenticatedUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/savings', [
            'amount' => -100,
            'description' => '',
            'date' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'description', 'date']);
    }

    public function test_user_can_view_their_savings()
    {
        [$user, $token] = $this->authenticatedUser();
        
        Saving::factory()->count(3)->create(['user_id' => $user->id]);
        Saving::factory()->count(2)->create(); // Other user's savings

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/savings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_single_saving()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $saving = Saving::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/savings/' . $saving->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'amount', 'description', 'date']);
    }

    public function test_user_cannot_view_other_users_saving()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $otherUserSaving = Saving::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/savings/' . $otherUserSaving->id);

        $response->assertStatus(403);
    }

    public function test_user_can_update_their_saving()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $saving = Saving::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'amount' => 1500.00,
            'description' => 'Updated savings description',
            'date' => '2024-02-15'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/savings/' . $saving->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'saving']);

        $this->assertDatabaseHas('savings', [
            'id' => $saving->id,
            'amount' => 1500.00,
            'description' => 'Updated savings description'
        ]);
    }

    public function test_user_can_delete_their_saving()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $saving = Saving::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson('/api/savings/' . $saving->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Saving deleted successfully']);

        $this->assertDatabaseMissing('savings', ['id' => $saving->id]);
    }
}
