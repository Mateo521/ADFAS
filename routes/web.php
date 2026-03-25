<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImportController;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticiaController;
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::patch('/designaciones/{designacion}/responder', [DashboardController::class, 'responderDesignacion'])->name('designaciones.responder');

    Route::get('/admin/importar-partidos', [ImportController::class, 'index'])->name('admin.importar.index');
    Route::post('/admin/importar-partidos', [ImportController::class, 'upload'])->name('admin.importar.upload');

 
    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
    
  
    Route::get('/noticias/crear', [NoticiaController::class, 'create'])->name('noticias.create');
    
     
    Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
    
 
    Route::get('/noticias/{noticia}', [NoticiaController::class, 'show'])->name('noticias.show');

});

require __DIR__ . '/auth.php';
