    <?php

    use App\Http\Controllers\DepartmentsController;
    use App\Http\Controllers\ProfileController;
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
        Route::get('/departments', [DepartmentsController::class, 'index'])
            ->middleware('can:departments.view')
            ->name('departments.index');

        Route::post('/departments', [DepartmentsController::class, 'store'])
            ->middleware('can:departments.create')
            ->name('departments.store');

        Route::get('/departments/{department}/edit', [DepartmentsController::class, 'edit'])
            ->middleware('can:departments.update')
            ->name('departments.edit');

        Route::put('/departments/{department}', [DepartmentsController::class, 'update'])
            ->middleware('can:departments.update')
            ->name('departments.update');

        Route::delete('/departments/{department}', [DepartmentsController::class, 'destroy'])
            ->middleware('can:departments.delete')
            ->name('departments.destroy');
    });

    Route::get('/profile/two-factor/confirm-password', fn() => redirect()->route('profile.edit'))
        ->middleware(['auth', 'password.confirm'])
        ->name('profile.two-factor.confirmed');

    require __DIR__ . '/auth.php';
