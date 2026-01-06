<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SavingController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\AnalyticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Savings routes
    Route::apiResource('savings', SavingController::class);

    // Expenses routes
    Route::apiResource('expenses', ExpenseController::class);

    // Analytics routes
    Route::prefix('analytics')->group(function () {
        Route::get('/monthly', [AnalyticsController::class, 'monthlyAnalysis']);
        Route::get('/yearly', [AnalyticsController::class, 'yearlyAnalysis']);
        Route::get('/categories', [AnalyticsController::class, 'categoriesSummary']);
        Route::get('/savings-vs-expenses', [AnalyticsController::class, 'savingsVsExpenses']);
        Route::get('/total-savings', [AnalyticsController::class, 'totalSavings']);
        Route::get('/total-monthly-expenses', [AnalyticsController::class, 'totalMonthlyExpenses']);
        Route::get('/total-yearly-expenses', [AnalyticsController::class, 'totalYearlyExpenses']);
    });
});