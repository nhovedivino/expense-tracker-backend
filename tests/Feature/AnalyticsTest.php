<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        return [$user, $token];
    }

    public function test_user_can_get_monthly_analysis()
    {
        [$user, $token] = $this->authenticatedUser();
        
        // Create expenses for January 2024
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'category' => 'Food',
            'date' => '2024-01-15'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 50.00,
            'category' => 'Transport',
            'date' => '2024-01-20'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/monthly?month=2024-01');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'month',
                'total_expenses',
                'previous_month_total',
                'percentage_change',
                'category_breakdown',
                'expense_count'
            ])
            ->assertJson([
                'month' => '2024-01',
                'total_expenses' => 150.00,
                'expense_count' => 2
            ]);
    }

    public function test_monthly_analysis_requires_valid_month()
    {
        [$user, $token] = $this->authenticatedUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/monthly?month=invalid-month');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['month']);
    }

    public function test_user_can_get_yearly_analysis()
    {
        [$user, $token] = $this->authenticatedUser();
        
        // Create expenses for different months in 2024
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'category' => 'Food',
            'date' => '2024-01-15'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'category' => 'Transport',
            'date' => '2024-02-15'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/yearly?year=2024');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'year',
                'total_expenses',
                'monthly_breakdown',
                'category_breakdown'
            ])
            ->assertJson([
                'year' => 2024,
                'total_expenses' => 300.00
            ]);
    }

    public function test_user_can_get_categories_summary()
    {
        [$user, $token] = $this->authenticatedUser();
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'category' => 'Food'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'category' => 'Food'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 50.00,
            'category' => 'Transport'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'categories' => [
                    '*' => ['category', 'total', 'count', 'average']
                ]
            ]);
    }

    public function test_user_can_get_savings_vs_expenses_comparison()
    {
        [$user, $token] = $this->authenticatedUser();
        
        // Create savings and expenses for the same period
        Saving::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000.00,
            'date' => '2024-01-15'
        ]);
        
        Expense::factory()->create([
            'user_id' => $user->id,
            'amount' => 300.00,
            'date' => '2024-01-20'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/savings-vs-expenses?start_date=2024-01-01&end_date=2024-01-31');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'period' => ['start_date', 'end_date'],
                'total_savings',
                'total_expenses',
                'net_amount',
                'savings_rate'
            ])
            ->assertJson([
                'total_savings' => 1000.00,
                'total_expenses' => 300.00,
                'net_amount' => 700.00
            ]);
    }

    public function test_savings_vs_expenses_requires_valid_dates()
    {
        [$user, $token] = $this->authenticatedUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/savings-vs-expenses?start_date=invalid&end_date=2024-01-31');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_analytics_requires_authentication()
    {
        $response = $this->getJson('/api/analytics/monthly?month=2024-01');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/yearly?year=2024');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/categories');
        $response->assertStatus(401);

        $response = $this->getJson('/api/analytics/savings-vs-expenses?start_date=2024-01-01&end_date=2024-01-31');
        $response->assertStatus(401);
    }
}
