<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('transactions', TransactionController::class)->except(['show']);

    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::post('/debts', [DebtController::class, 'store'])->name('debts.store');
    Route::patch('/debts/{debt}/mark-paid', [DebtController::class, 'markPaid'])->name('debts.markPaid');
Route::delete('/debts/{debt}', [DebtController::class, 'destroy'])->name('debts.destroy');
    Route::get('/export/excel', [ExportController::class, 'exportExcel'])->name('export.excel');
Route::get('/savings', [SavingsGoalController::class, 'index'])->name('savings.index');
Route::post('/savings', [SavingsGoalController::class, 'store'])->name('savings.store');
Route::patch('/savings/{savingsGoal}', [SavingsGoalController::class, 'update'])->name('savings.update');
Route::delete('/savings/{savingsGoal}', [SavingsGoalController::class, 'destroy'])->name('savings.destroy');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';