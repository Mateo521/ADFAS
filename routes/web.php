<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AsignacionController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\DesignacionController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsEspectador;
use App\Http\Controllers\Admin\EspectadorController;
use App\Http\Middleware\VerificarCuotaAlDia;
use App\Http\Controllers\Admin\TarifaController;
use App\Models\Ajuste;
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/cuota-requerida', [App\Http\Controllers\Admin\PagoController::class, 'pantallaBloqueo'])->name('pagos.requerido');
});
Route::middleware(['auth', 'aprobado', VerificarCuotaAlDia::class])->group(function () {



    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');


    Route::patch('/designaciones/{designacion}/responder', [DesignacionController::class, 'responder'])->name('designaciones.responder');
    Route::post('/licencias', [App\Http\Controllers\LicenciaController::class, 'store'])->name('licencias.store');



    Route::middleware([IsEspectador::class])->group(function () {
        Route::get('/pizarra-en-vivo', [AsignacionController::class, 'pizarraEnVivo'])->name('pizarra.envivo');
    });


    Route::middleware([IsAdmin::class])->group(function () {

        // Importar y Asignar
        Route::get('/admin/importar-partidos', [ImportController::class, 'index'])->name('admin.importar.index');
        Route::post('/admin/importar-partidos', [ImportController::class, 'upload'])->name('admin.importar.upload');

        Route::get('/admin/asignar-arbitros', [AsignacionController::class, 'index'])->name('admin.asignar.index');
        Route::post('/admin/asignar-arbitros', [AsignacionController::class, 'store'])->name('admin.asignar.store');

        Route::get('/admin/historial-asignaciones', [AsignacionController::class, 'historial'])->name('admin.historial.index');
        Route::post('/admin/historial-asignaciones/{partido}/reasignar', [AsignacionController::class, 'updateReasignacion'])->name('admin.historial.reasignar');

        Route::get('/admin/arbitros', [App\Http\Controllers\Admin\ArbitroController::class, 'index'])->name('admin.arbitros.index');
        Route::get('/admin/arbitros/{user}', [App\Http\Controllers\Admin\ArbitroController::class, 'show'])->name('admin.arbitros.show');
        Route::post('/admin/arbitros/{user}/aprobar', [App\Http\Controllers\Admin\ArbitroController::class, 'aprobar'])->name('admin.arbitros.aprobar');
        Route::patch('/admin/licencias/{licencia}/estado', [App\Http\Controllers\LicenciaController::class, 'updateEstado'])->name('admin.licencias.estado');

        Route::get('/noticias/crear', [NoticiaController::class, 'create'])->name('noticias.create');
        Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
        Route::get('/noticias/{noticia}/editar', [NoticiaController::class, 'edit'])->name('noticias.edit');
        Route::put('/noticias/{noticia}', [NoticiaController::class, 'update'])->name('noticias.update');
        Route::delete('/noticias/{noticia}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');


        Route::get('/admin/espectadores', [EspectadorController::class, 'index'])->name('admin.espectadores.index');
        Route::post('/admin/espectadores', [EspectadorController::class, 'store'])->name('admin.espectadores.store');
        Route::delete('/admin/espectadores/{user}', [EspectadorController::class, 'destroy'])->name('admin.espectadores.destroy');


        // Gestión de Aranceles/Tarifas
        Route::get('/admin/tarifas', [TarifaController::class, 'index'])->name('admin.tarifas.index');
        Route::post('/admin/tarifas', [TarifaController::class, 'store'])->name('admin.tarifas.store');
        Route::put('/admin/tarifas/{tarifa}', [TarifaController::class, 'update'])->name('admin.tarifas.update');
        Route::delete('/admin/tarifas/{tarifa}', [TarifaController::class, 'destroy'])->name('admin.tarifas.destroy');


        Route::get('/admin/pagos', [PagoController::class, 'index'])->name('admin.pagos.index');
        Route::post('/admin/pagos/registrar', [PagoController::class, 'registrarManual'])->name('admin.pagos.registrar');
        Route::post('/admin/pagos/ajustes', [PagoController::class, 'guardarAjustes'])->name('admin.pagos.ajustes');

    });


    Route::get('/noticias/{noticia}', [NoticiaController::class, 'show'])->name('noticias.show');

});

require __DIR__ . '/auth.php';