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

    public function test_user_can_get_total_savings()
    {
        [$user, $token] = $this->authenticatedUser();
        
        // Create multiple savings
        Saving::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000.00
        ]);
        
        Saving::factory()->create([
            'user_id' => $user->id,
            'amount' => 500.00
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/total-savings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_savings',
                'savings_count',
                'average_saving',
                'recent_savings'
            ])
            ->assertJson([
                'total_savings' => 1500.00,
                'savings_count' => 2,
                'average_saving' => 750.00
            ]);
    }

    public function test_user_can_get_total_monthly_expenses()
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
        ])->getJson('/api/analytics/total-monthly-expenses?month=2024-01');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'month',
                'total_expenses',
                'expenses_count',
                'average_expense',
                'top_categories'
            ])
            ->assertJson([
                'month' => '2024-01',
                'total_expenses' => 150.00,
                'expenses_count' => 2,
                'average_expense' => 75.00
            ]);
    }

    public function test_user_can_get_total_yearly_expenses()
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
        ])->getJson('/api/analytics/total-yearly-expenses?year=2024');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'year',
                'total_expenses',
                'expenses_count',
                'average_expense',
                'average_monthly_expense',
                'monthly_totals',
                'top_categories'
            ])
            ->assertJson([
                'year' => 2024,
                'total_expenses' => 300.00,
                'expenses_count' => 2,
                'average_expense' => 150.00,
                'average_monthly_expense' => 25.00
            ]);
    }

    public function test_total_endpoints_require_valid_parameters()
    {
        [$user, $token] = $this->authenticatedUser();

        // Test invalid month format
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/total-monthly-expenses?month=invalid');
        $response->assertStatus(422);

        // Test invalid year
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/analytics/total-yearly-expenses?year=1999');
        $response->assertStatus(422);
    }
}
