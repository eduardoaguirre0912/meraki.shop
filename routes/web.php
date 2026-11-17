<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntradaInventarioController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\GastoCategoriaController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PuntoEncuentroController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RetiroController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/finanzas', [FinanzasController::class, 'index'])->name('finanzas.index');
    Route::resource('retiros', RetiroController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/gastos', [ReporteController::class, 'gastos'])->name('reportes.gastos');
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('/reportes/movimientos', [ReporteController::class, 'movimientos'])->name('reportes.movimientos');
    Route::get('/reportes/cuentas', [ReporteController::class, 'cuentasPorCobrar'])->name('reportes.cuentas');
    Route::get('/reportes/entregas', [ReporteController::class, 'entregas'])->name('reportes.entregas');
    Route::get('/reportes/utilidades', [ReporteController::class, 'utilidades'])->name('reportes.utilidades');

    Route::resource('clientas', ClientaController::class);
    Route::get('/clientas-buscar', [ClientaController::class, 'buscar'])->name('clientas.buscar');

    Route::resource('productos', ProductoController::class);
    Route::get('/productos-buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');

    Route::resource('ventas', VentaController::class);
    Route::post('/ventas/{venta}/abono', [VentaController::class, 'abono'])->name('ventas.abono');

    Route::resource('entradas', EntradaInventarioController::class)->only(['index', 'create', 'store']);
    Route::resource('gastos', GastoController::class);
    Route::resource('entregas', EntregaController::class);
    Route::resource('puntos', PuntoEncuentroController::class);

    Route::get('/gasto-categorias', [GastoCategoriaController::class, 'index'])->name('gasto-categorias.index');
    Route::post('/gasto-categorias', [GastoCategoriaController::class, 'store'])->name('gasto-categorias.store');
    Route::put('/gasto-categorias/{categoria}', [GastoCategoriaController::class, 'update'])->name('gasto-categorias.update');
    Route::post('/gasto-categorias/{categoria}/toggle', [GastoCategoriaController::class, 'toggle'])->name('gasto-categorias.toggle');
});