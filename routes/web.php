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

    // Master Data
    Route::resource('majors', \App\Http\Controllers\Admin\MajorController::class);
    Route::resource('classes', \App\Http\Controllers\Admin\SchoolClassController::class);
    Route::resource('academic-years', \App\Http\Controllers\Admin\AcademicYearController::class);
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
    Route::resource('parents', \App\Http\Controllers\Admin\ParentController::class);

    // Face Management
    Route::get('face-profiles', [\App\Http\Controllers\Admin\FaceProfileController::class, 'index'])
        ->name('face-profiles.index');
    Route::get('face-profiles/{faceProfile}', [\App\Http\Controllers\Admin\FaceProfileController::class, 'show'])
        ->name('face-profiles.show');
    Route::post('face-profiles/{faceProfile}/review', [\App\Http\Controllers\Admin\FaceProfileController::class, 'review'])
        ->name('face-profiles.review');
    Route::get('face-embeddings/{embedding}/image', [\App\Http\Controllers\Admin\FaceProfileController::class, 'image'])
        ->name('face-profiles.image');
});

// =========================================================================
// ROUTE GROUP: SISWA
// =========================================================================
Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('siswa.dashboard');
    })->name('dashboard');

    // Face Enrollment
    Route::get('face-enrollment', [\App\Http\Controllers\Student\FaceEnrollmentController::class, 'index'])
        ->name('face-enrollment.index');
    Route::get('face-enrollment/capture', [\App\Http\Controllers\Student\FaceEnrollmentController::class, 'create'])
        ->name('face-enrollment.capture');
    Route::post('face-enrollment', [\App\Http\Controllers\Student\FaceEnrollmentController::class, 'store'])
        ->name('face-enrollment.store');
    Route::get('face-enrollment/{id}', [\App\Http\Controllers\Student\FaceEnrollmentController::class, 'show'])
        ->name('face-enrollment.show');
});

require __DIR__.'/auth.php';