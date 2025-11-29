<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        return [$user, $token];
    }

    public function test_user_can_create_expense()
    {
        [$user, $token] = $this->authenticatedUser();

        $expenseData = [
            'amount' => 50.75,
            'description' => 'Grocery shopping',
            'category' => 'Food',
            'date' => '2024-01-15'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/expenses', $expenseData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'expense' => ['id', 'amount', 'description', 'category', 'date', 'user_id']
            ]);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'amount' => 50.75,
            'description' => 'Grocery shopping',
            'category' => 'Food'
        ]);
    }

    public function test_user_cannot_create_expense_with_invalid_data()
    {
        [$user, $token] = $this->authenticatedUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/expenses', [
            'amount' => -50,
            'description' => '',
            'category' => '',
            'date' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'description', 'category', 'date']);
    }

    public function test_user_can_view_their_expenses()
    {
        [$user, $token] = $this->authenticatedUser();
        
        Expense::factory()->count(5)->create(['user_id' => $user->id]);
        Expense::factory()->count(3)->create(); // Other user's expenses

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_filter_expenses_by_category()
    {
        [$user, $token] = $this->authenticatedUser();
        
        Expense::factory()->create(['user_id' => $user->id, 'category' => 'Food']);
        Expense::factory()->create(['user_id' => $user->id, 'category' => 'Transport']);
        Expense::factory()->create(['user_id' => $user->id, 'category' => 'Food']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/expenses?category=Food');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_filter_expenses_by_date_range()
    {
        [$user, $token] = $this->authenticatedUser();
        
        Expense::factory()->create(['user_id' => $user->id, 'date' => '2024-01-15']);
        Expense::factory()->create(['user_id' => $user->id, 'date' => '2024-01-20']);
        Expense::factory()->create(['user_id' => $user->id, 'date' => '2024-02-15']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/expenses?start_date=2024-01-01&end_date=2024-01-31');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_update_their_expense()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $expense = Expense::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'amount' => 75.50,
            'description' => 'Updated grocery shopping',
            'category' => 'Food & Dining',
            'date' => '2024-02-15'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson('/api/expenses/' . $expense->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'expense']);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 75.50,
            'description' => 'Updated grocery shopping',
            'category' => 'Food & Dining'
        ]);
    }

    public function test_user_can_delete_their_expense()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $expense = Expense::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson('/api/expenses/' . $expense->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Expense deleted successfully']);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_user_cannot_access_other_users_expense()
    {
        [$user, $token] = $this->authenticatedUser();
        
        $otherUserExpense = Expense::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/expenses/' . $otherUserExpense->id);

        $response->assertStatus(403);
    }
}
