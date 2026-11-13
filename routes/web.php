<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MasterMenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScreenerController;
use App\Http\Controllers\TransactionDailyController;
use App\Http\Controllers\TransactionHourlyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Departments Routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])
            ->middleware('can:departments.view')->name('index');

        Route::post('/', [DepartmentController::class, 'store'])
            ->middleware('can:departments.create')->name('store');

        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])
            ->middleware('can:departments.update')->name('edit');

        Route::put('/{department}', [DepartmentController::class, 'update'])
            ->middleware('can:departments.update')->name('update');

        Route::delete('/{department}', [DepartmentController::class, 'destroy'])
            ->middleware('can:departments.delete')->name('destroy');
    });
    // Users Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('can:users.view')->name('index');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('can:users.create')->name('store');

        Route::get('/{user}/edit', [UserController::class, 'edit'])
            ->middleware('can:users.update')->name('edit');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('can:users.update')->name('update');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('can:users.delete')->name('destroy');
    });

    // Roles Routes
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('can:roles.view')->name('index');

        Route::post('/', [RoleController::class, 'store'])
            ->middleware('can:roles.create')->name('store');

        Route::get('/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('can:roles.update')->name('edit');

        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('can:roles.update')->name('update');

        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('can:roles.delete')->name('destroy');
    });
    // User-Roles Routes
    Route::prefix('user-roles')->name('user-roles.')->group(function () {
        Route::get('/', [UserRoleController::class, 'index'])
            ->middleware('can:user-roles.view')->name('index');

        Route::get('/{user}/edit', [UserRoleController::class, 'edit'])
            ->middleware('can:user-roles.update')->name('edit');

        Route::put('/{user}', [UserRoleController::class, 'update'])
            ->middleware('can:user-roles.update')->name('update');
    });
    // User-Permissions Routes
    Route::prefix('user-permissions')->name('user-permissions.')->group(function () {
        Route::get('/', [UserPermissionController::class, 'index'])
            ->middleware('can:user-permissions.view')->name('index');

        Route::get('/{user}/edit', [UserPermissionController::class, 'edit'])
            ->middleware('can:user-permissions.update')->name('edit');

        Route::put('/{user}', [UserPermissionController::class, 'update'])
            ->middleware('can:user-permissions.update')->name('update');
    });
    // Master-Menus Routes
    Route::prefix('master-menus')->name('master-menus.')->group(function () {
        Route::get('/', [MasterMenuController::class, 'index'])
            ->middleware('can:master-menus.view')->name('index');

        Route::post('/', [MasterMenuController::class, 'store'])
            ->middleware('can:master-menus.create')->name('store');

        Route::get('/{masterMenu}/edit', [MasterMenuController::class, 'edit'])
            ->middleware('can:master-menus.update')->name('edit');

        Route::put('/{masterMenu}', [MasterMenuController::class, 'update'])
            ->middleware('can:master-menus.update')->name('update');
        Route::delete('/{masterMenu}', [MasterMenuController::class, 'destroy'])
            ->middleware('can:master-menus.delete')->name('destroy');
    });
    // Companies Routes
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])
            ->middleware('can:companies.view')->name('index');

        Route::get('/volume-breakouts', [CompanyController::class, 'volumeBreakouts'])
            ->middleware('can:companies.view')->name('volume-breakouts');

        Route::post('/', [CompanyController::class, 'store'])
            ->middleware('can:companies.create')->name('store');

        Route::get('/{company}', [CompanyController::class, 'show'])
            ->middleware('can:companies.view')->name('show');

        Route::get('/{company}/edit', [CompanyController::class, 'edit'])
            ->middleware('can:companies.update')->name('edit');

        Route::put('/{company}', [CompanyController::class, 'update'])
            ->middleware('can:companies.update')->name('update');

        Route::delete('/{company}', [CompanyController::class, 'destroy'])
            ->middleware('can:companies.delete')->name('destroy');
    });
    // Companies Routes
    Route::prefix('transaction-hourlies')->name('transaction-hourlies.')->group(function () {
        Route::get('/', [TransactionHourlyController::class, 'index'])
            ->middleware('can:transaction-hourlies.view')->name('index');
    });
    // Companies Routes
    Route::prefix('transaction-dailies')->name('transaction-dailies.')->group(function () {
        Route::get('/', [TransactionDailyController::class, 'index'])
            ->middleware('can:transaction-dailies.view')->name('index');
    });
    // Companies Routes
    Route::prefix('screeners')->name('screeners.')->group(function () {
        Route::get('/', [ScreenerController::class, 'index'])
            ->middleware('can:screeners.view')->name('index');
    });
});

Route::get('/profile/two-factor/confirm-password', fn() => redirect()->route('profile.edit'))
    ->middleware(['auth', 'password.confirm'])
    ->name('profile.two-factor.confirmed');

require __DIR__ . '/auth.php';
