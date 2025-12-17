<?php

use App\Http\Controllers\Actividades;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CatalogosController;
use App\Http\Controllers\VerEnlace;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Enlaces;
use App\Http\Controllers\EditarTicket;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportToExcel;
use App\Http\Controllers\NuevoEnlace;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EditarEnlace;
use App\Http\Controllers\EndpointController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\ListTicketsTabletController;
use App\Http\Controllers\LoginTabletController;
use App\Http\Controllers\RedController;
use App\Http\Controllers\RedMapas;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\TicketsController;
use App\Livewire\ListTicketsTablet;
use App\Livewire\MemoriasTecnicas;
use App\Livewire\MonitoreoMapa;

//Auth
Route::get('/', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'authenticate'])->name('login.submit');

//Auth para moviles
Route::prefix('app')->group(function () {

    Route::get('/login', [LoginTabletController::class, 'index'])->name('login.tablet');
    Route::post('/login', [LoginTabletController::class, 'autenticar'])->name('login.tablet.submit');

    Route::middleware('auth.movil')->group(function () {
        Route::get('/home', [ListTicketsTabletController::class, 'index'])->name('tablet.home');
        Route::get('/ticket/{id}', [ListTicketsTabletController::class, 'detalles'])->name('tablet.ticket.detalles');
        Route::post('/ticket/comentario', [ListTicketsTabletController::class, 'enviarComentario'])->name('tablet.ticket.comentario');
        Route::post('/ticket/{id}/firma', [ListTicketsTabletController::class, 'guardarFirma'])->name('tablet.ticket.firma');
        Route::post('/logout', [UserController::class, 'logoutMovil'])->name('tablet.logout');
    });
});

//Monitoreo de endpoints
Route::get('/endpoints/listar/', [EndpointController::class, 'listar'])->name('endpoint.list');
Route::get('/endpoint/nuevo', [EndpointController::class, 'create'])->name('endpoint.create');
Route::get('/endpoint/{id?}/', [EndpointController::class, 'edit'])->name('endpoint.edit');

//Auth Web
Route::middleware('auth')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/home', [HomeController::class, 'dashboard'])->name('dashboard');

    //Tickets
    Route::prefix('tickets')->group(function () {

        Route::view('/', 'tickets')->name('tickets.index');
        Route::get('/nuevo', [TicketsController::class, 'newTicket'])->name('tickets.create');
        Route::get('/{id}', [EditarTicket::class, 'index'])->name('tickets.edit');
        Route::get('/{id}/copia', [TicketsController::class, 'copy'])->name('tickets.copy');
        Route::get('/{id}/correo', [TicketsController::class, 'enviarPorMail'])->name('tickets.mail');
        Route::get('/{id}/documento', [PDFController::class, 'viewDocTicket'])->name('tickets.pdf');
    });

    //Catalogos
        Route::middleware('permission:Catalogos')->group(function () {
        Route::get('/admin/catalogos', [AdminController::class, 'indexCat'])->name('catalogos.index');
        Route::get('/catalogos/{tipo}', [CatalogosController::class, 'index'])->name('catalogos.show');
    });

    //Perfil
    Route::get('/perfil', [UserController::class, 'perfil'])->name('perfil');

    Route::middleware('permission:Seccion usuarios')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/{id}/roles', [UserController::class, 'roles'])->name('usuarios.roles');
        Route::get('/user/add', [UserController::class, 'create'])->name('users.create');
        Route::post('/user/save', [UserController::class, 'store'])->name('users.store');
    });

    //Red
    Route::prefix('red')->group(function () {

        Route::get('/ips', [RedController::class, 'indexIps'])->name('red.ips');
        
        //Monitoreo
        Route::get('/monitoreo/mapa', MonitoreoMapa::class)->name('monitoreo.mapa');
        
    });

});
