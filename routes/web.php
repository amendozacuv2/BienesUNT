<?php

use App\Livewire\Areas\Create as AreasCreate;
use App\Livewire\Areas\Edit as AreasEdit;
use App\Livewire\Areas\Index as AreasIndex;
use App\Livewire\Profile\Index as ProfileIndex;
use App\Livewire\Roles\Create as RolesCreate;
use App\Livewire\Roles\Edit as RolesEdit;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Locations\Create as LocationsCreate;
use App\Livewire\Locations\Edit as LocationsEdit;
use App\Livewire\Locations\Index as LocationsIndex;
use App\Livewire\Employees\Create as EmployeesCreate;
use App\Livewire\Employees\Edit as EmployeesEdit;
use App\Livewire\Employees\Index as EmployeesIndex;
use App\Livewire\Users\Create as UsersCreate;
use App\Livewire\Users\Edit as UsersEdit;
use App\Livewire\Users\Index as UsersIndex;

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {
    Route::view('home', 'dashboard')
        ->name('home');

    Route::redirect('dashboard', 'home');

    Route::get('profile', ProfileIndex::class)
        ->middleware('permission:view.profile')
        ->name('profile');

    Route::prefix('roles')
        ->name('roles.')
        ->group(function () {
            Route::get('/', RolesIndex::class)
                ->middleware('permission:view.role')
                ->name('index');

            Route::get('/crear', RolesCreate::class)
                ->middleware('permission:create.role')
                ->name('create');

            Route::get('/{role}/editar', RolesEdit::class)
                ->middleware('permission:edit.role')
                ->name('edit');
        });

    Route::prefix('areas')
        ->name('areas.')
        ->group(function () {
            Route::get('/', AreasIndex::class)
                ->middleware('permission:view.area')
                ->name('index');

            Route::get('/crear', AreasCreate::class)
                ->middleware('permission:create.area')
                ->name('create');

            Route::get('/{area:uuid}/editar', AreasEdit::class)
                ->middleware('permission:edit.area')
                ->name('edit');
        });

    Route::prefix('locations')
        ->name('locations.')
        ->group(function () {
            Route::get('/', LocationsIndex::class)
                ->middleware('permission:view.location')
                ->name('index');

            Route::get('/crear', LocationsCreate::class)
                ->middleware('permission:create.location')
                ->name('create');

            Route::get('/{location:uuid}/editar', LocationsEdit::class)
                ->middleware('permission:edit.location')
                ->name('edit');
        });

    Route::prefix('employees')
    ->name('employees.')
    ->group(function () {
        Route::get('/', EmployeesIndex::class)
            ->middleware('permission:view.employee')
            ->name('index');

        Route::get('/crear', EmployeesCreate::class)
            ->middleware('permission:create.employee')
            ->name('create');

        Route::get('/{employee:uuid}/editar', EmployeesEdit::class)
            ->middleware('permission:edit.employee')
            ->name('edit');
    });

    Route::prefix('users')
    ->name('users.')
    ->group(function () {
        Route::get('/', UsersIndex::class)
            ->middleware('permission:view.user')
            ->name('index');

        Route::get('/crear', UsersCreate::class)
            ->middleware('permission:create.user')
            ->name('create');

        Route::get('/{user:uuid}/editar', UsersEdit::class)
            ->middleware('permission:edit.user')
            ->name('edit');
    });
    
});

require __DIR__.'/auth.php';
