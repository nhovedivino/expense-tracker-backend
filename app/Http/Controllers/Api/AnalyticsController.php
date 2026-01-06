<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get database-specific month extraction function
     */
    private function getMonthFunction()
    {
        $driver = DB::getDriverName();
        
        return match($driver) {
            'mysql' => 'MONTH(date)',
            'sqlite' => "CAST(strftime('%m', date) as INTEGER)",
            'pgsql' => 'EXTRACT(month FROM date)',
            default => "CAST(strftime('%m', date) as INTEGER)"
        };
    }
    /**
     * Get monthly expense analysis
     */
    public function monthlyAnalysis(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->month;
        $user = $request->user();

        // Get expenses for the specified month
        $expenses = $user->expenses()
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->get();

        // Calculate total expenses
        $totalExpenses = $expenses->sum('amount');

        // Group by category
        $categoryBreakdown = $expenses->groupBy('category')->map(function ($items) {
            return [
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'average' => $items->avg('amount')
            ];
        });

        // Get previous month for comparison
        $previousMonth = date('Y-m', strtotime($month . '-01 -1 month'));
        $previousMonthExpenses = $user->expenses()
            ->whereYear('date', substr($previousMonth, 0, 4))
            ->whereMonth('date', substr($previousMonth, 5, 2))
            ->sum('amount');

        // Calculate percentage change
        $percentageChange = 0;
        if ($previousMonthExpenses > 0) {
            $percentageChange = (($totalExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100;
        }

        return response()->json([
            'month' => $month,
            'total_expenses' => $totalExpenses,
            'previous_month_total' => $previousMonthExpenses,
            'percentage_change' => round($percentageChange, 2),
            'category_breakdown' => $categoryBreakdown,
            'expense_count' => $expenses->count()
        ]);
    }

    /**
     * Get yearly expense summary
     */
    public function yearlyAnalysis(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        $year = $request->year;
        $user = $request->user();

        // Get monthly breakdown for the year
        $monthFunction = $this->getMonthFunction();
        $monthlyBreakdown = $user->expenses()
            ->whereYear('date', $year)
            ->select(
                DB::raw($monthFunction . ' as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw($monthFunction))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fill in missing months with zero
        $completeBreakdown = [];
        for ($month = 1; $month <= 12; $month++) {
            $completeBreakdown[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'total' => isset($monthlyBreakdown[$month]) ? $monthlyBreakdown[$month]->total : 0,
                'count' => isset($monthlyBreakdown[$month]) ? $monthlyBreakdown[$month]->count : 0
            ];
        }

        // Get category breakdown for the year
        $categoryBreakdown = $user->expenses()
            ->whereYear('date', $year)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        $totalYearExpenses = $user->expenses()->whereYear('date', $year)->sum('amount');

        return response()->json([
            'year' => $year,
            'total_expenses' => $totalYearExpenses,
            'monthly_breakdown' => $completeBreakdown,
            'category_breakdown' => $categoryBreakdown
        ]);
    }

    /**
     * Get expense categories summary
     */
    public function categoriesSummary(Request $request)
    {
        $user = $request->user();

        $categories = $user->expenses()
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'), DB::raw('AVG(amount) as average'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Get savings vs expenses comparison
     */
    public function savingsVsExpenses(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $user = $request->user();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $totalSavings = $user->savings()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpenses = $user->expenses()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netAmount = $totalSavings - $totalExpenses;

        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'total_savings' => (float) $totalSavings,
            'total_expenses' => $totalExpenses,
            'net_amount' => $netAmount,
            'savings_rate' => $totalSavings > 0 ? round(($totalSavings / ($totalSavings + $totalExpenses)) * 100, 2) : 0
        ]);
    }

    /**
     * Get total savings
     */
    public function totalSavings(Request $request)
    {
        $user = $request->user();
        
        $totalSavings = $user->savings()->sum('amount');
        $savingsCount = $user->savings()->count();
        $averageSaving = $savingsCount > 0 ? $totalSavings / $savingsCount : 0;

        // Get recent savings (last 5)
        $recentSavings = $user->savings()
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get(['amount', 'description', 'date']);

        return response()->json([
            'total_savings' => (float) $totalSavings,
            'savings_count' => $savingsCount,
            'average_saving' => round($averageSaving, 2),
            'recent_savings' => $recentSavings
        ]);
    }

    /**
     * Get total monthly expenses
     */
    public function totalMonthlyExpenses(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->month;
        $user = $request->user();

        $totalExpenses = $user->expenses()
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->sum('amount');

        $expensesCount = $user->expenses()
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->count();

        $averageExpense = $expensesCount > 0 ? $totalExpenses / $expensesCount : 0;

        // Get top categories for the month
        $topCategories = $user->expenses()
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'month' => $month,
            'total_expenses' => $totalExpenses,
            'expenses_count' => $expensesCount,
            'average_expense' => round($averageExpense, 2),
            'top_categories' => $topCategories
        ]);
    }

    /**
     * Get total yearly expenses
     */
    public function totalYearlyExpenses(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        $year = $request->year;
        $user = $request->user();

        $totalExpenses = $user->expenses()
            ->whereYear('date', $year)
            ->sum('amount');

        $expensesCount = $user->expenses()
            ->whereYear('date', $year)
            ->count();

        $averageExpense = $expensesCount > 0 ? $totalExpenses / $expensesCount : 0;

        // Get monthly totals for the year
        $monthFunction = $this->getMonthFunction();
        $monthlyTotals = $user->expenses()
            ->whereYear('date', $year)
            ->select(
                DB::raw($monthFunction . ' as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy(DB::raw($monthFunction))
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        // Get top categories for the year
        $topCategories = $user->expenses()
            ->whereYear('date', $year)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Calculate average monthly expense
        $averageMonthlyExpense = $totalExpenses / 12;

        return response()->json([
            'year' => $year,
            'total_expenses' => $totalExpenses,
            'expenses_count' => $expensesCount,
            'average_expense' => round($averageExpense, 2),
            'average_monthly_expense' => round($averageMonthlyExpense, 2),
            'monthly_totals' => $monthlyTotals,
            'top_categories' => $topCategories
        ]);
    }
}
