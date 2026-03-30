<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImportController;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AsignacionController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\DesignacionController;

Route::get('/', function () {
    return redirect()->route('login');
});

 
Route::middleware(['auth', 'aprobado'])->group(function () {
    
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

     
    Route::patch('/designaciones/{designacion}/responder', [DashboardController::class, 'responderDesignacion'])->name('designaciones.responder');

    
    Route::get('/admin/importar-partidos', [ImportController::class, 'index'])->name('admin.importar.index');
    Route::post('/admin/importar-partidos', [ImportController::class, 'upload'])->name('admin.importar.upload');

  
    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
    Route::get('/noticias/crear', [NoticiaController::class, 'create'])->name('noticias.create');
    Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
    Route::get('/noticias/{noticia}', [NoticiaController::class, 'show'])->name('noticias.show');
    Route::get('/noticias/{noticia}/editar', [NoticiaController::class, 'edit'])->name('noticias.edit');
    Route::put('/noticias/{noticia}', [NoticiaController::class, 'update'])->name('noticias.update');
    Route::delete('/noticias/{noticia}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');

   
    Route::get('/admin/asignar-arbitros', [AsignacionController::class, 'index'])->name('admin.asignar.index');
    Route::post('/admin/asignar-arbitros', [AsignacionController::class, 'store'])->name('admin.asignar.store');

    
    Route::patch('/designaciones/{designacion}/responder', [DesignacionController::class, 'responder'])->name('designaciones.responder');  
    Route::get('/admin/historial-asignaciones', [AsignacionController::class, 'historial'])->name('admin.historial.index');
    Route::post('/admin/historial-asignaciones/{partido}/reasignar', [AsignacionController::class, 'updateReasignacion'])->name('admin.historial.reasignar');

   
    Route::get('/admin/arbitros', [App\Http\Controllers\Admin\ArbitroController::class, 'index'])->name('admin.arbitros.index');
    Route::get('/admin/arbitros/{user}', [App\Http\Controllers\Admin\ArbitroController::class, 'show'])->name('admin.arbitros.show');

    Route::post('/admin/arbitros/{user}/aprobar', [App\Http\Controllers\Admin\ArbitroController::class, 'aprobar'])->name('admin.arbitros.aprobar');
    
 
    Route::post('/licencias', [App\Http\Controllers\LicenciaController::class, 'store'])->name('licencias.store');
    
    Route::patch('/admin/licencias/{licencia}/estado', [App\Http\Controllers\LicenciaController::class, 'updateEstado'])->name('admin.licencias.estado');

});

require __DIR__ . '/auth.php';