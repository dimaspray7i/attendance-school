<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

// Redirect dashboard utama berdasarkan role user
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } 
    
    if ($user->isSiswa()) {
        return redirect()->route('siswa.dashboard');
    } 
    
    if ($user->isOrangTua()) {
        return redirect()->route('orangtua.dashboard');
    }
    
    abort(403, 'Role tidak dikenali.');
})->middleware(['auth', 'verified'])->name('dashboard');

// ROUTE GROUP: ADMIN
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

// ROUTE GROUP: SISWA
Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('siswa.dashboard');
    })->name('dashboard');
});

// ROUTE GROUP: ORANG TUA
Route::middleware(['auth', 'verified', 'role:orang_tua'])->prefix('orangtua')->name('orangtua.')->group(function () {
    Route::get('/dashboard', function () {
        return view('orangtua.dashboard');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =========================================================================
// ROUTE GROUP: ADMIN
// =========================================================================
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    // Master Data Routes
    Route::resource('majors', \App\Http\Controllers\Admin\MajorController::class);
    Route::resource('classes', \App\Http\Controllers\Admin\SchoolClassController::class);
    Route::resource('academic-years', \App\Http\Controllers\Admin\AcademicYearController::class);
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
    Route::resource('parents', \App\Http\Controllers\Admin\ParentController::class);
});

require __DIR__.'/auth.php';