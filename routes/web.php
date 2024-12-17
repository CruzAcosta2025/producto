<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JitsiController;


Route::get('/', [HomeController::class, 'index'])->name('index');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/tareas', function () {})->name('tareas');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');


// Rutas para la gestión de usuarios (solo accesibles por usuarios autenticados)

//---------------------------------------------------------------
Route::middleware('auth')->group(function () {
    // Muestra la lista de usuarios (carga la vista o responde en JSON para AJAX)
    Route::get('/users', [UsuarioController::class, 'index'])->name('index-usuarios');

    // Muestra los datos de un usuario para ser editado (respuesta en JSON)
    Route::get('public/users/{id}/edit', [UsuarioController::class, 'edit'])->name('index-usuarios.edit');

    // Actualiza el rol de un usuario
    Route::put('public/users/{id}/update', [UsuarioController::class, 'update'])->name('index-usuarios.update');

    // Elimina un usuario
    Route::delete('public/users/{id}', [UsuarioController::class, 'destroy'])->name('index-usuarios.destroy');
});

//---------------------------------------------------------------


// RUTAS PARA LA GESTION DE PROYECTOS 
//---------------------------------------------------------------
// Ruta para mostrar todos los proyectos
Route::get('/proyectos', [ProjectController::class, 'index'])->name('proyectos.index');

// Ruta para crear un nuevo proyecto (Formulario de creación)
Route::get('public/proyectos/create', [ProjectController::class, 'create'])->name('proyectos.create');

// Ruta para almacenar un nuevo proyecto (AJAX)
Route::post('/proyectos', [ProjectController::class, 'store'])->name('proyectos.store');

// Ruta para mostrar un proyecto específico (AJAX)
Route::get('public/proyectos/{id}', [ProjectController::class, 'show'])->name('proyectos.show');

// Ruta para editar un proyecto (Formulario de edición)
Route::get('public/proyectos/{id}/edit', [ProjectController::class, 'edit'])->name('proyectos.edit');

// Ruta para actualizar un proyecto (AJAX)
Route::put('public/proyectos/{id}', [ProjectController::class, 'update'])->name('proyectos.update');

Route::delete('/proyectos/{proyecto}', [ProjectController::class, 'destroy'])->name('proyectos.destroy');
//---------------------------------------------------------------




// RUTAS PARA LA GESTION DE UN KANBAN BOARD
//---------------------------------------------------------------
// Mostrar la vista principal del Kanban y obtener proyectos
Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
//Route::get('/kanban/{proyectoId}', [KanbanController::class, 'index'])->name('kanban.index');

// Crear un nuevo tablero para un proyecto
Route::post('public/kanban/{proyectoId}/crear-tablero', [KanbanController::class, 'crearTablero'])->name('kanban.crearTablero');

// Obtener los tableros de un proyecto específico
Route::get('public/kanban/{proyectoId}/tableros', [KanbanController::class, 'obtenerTableros'])->name('kanban.obtenerTableros');

// Editar un tablero existente
Route::put('public/kanban/{proyectoId}/editar-tablero/{tableroId}', [KanbanController::class, 'editarTablero'])->name('kanban.editarTablero');

//Route::put('/kanban/{proyectoId}/editar-tablero/{tableroId}', [KanbanController::class, 'crearTablero'])->name('kanban.editarTablero');

// Eliminar un tablero de un proyecto
Route::delete('public/kanban/{proyectoId}/eliminar-tablero/{tableroId}', [KanbanController::class, 'eliminarTablero'])->name('kanban.eliminarTablero');


// OBTENER LAS COLUMNAS DE UN TABLERO

// Crear una nueva columna dentro de un tablero
Route::post('public/kanban/{tableroId}/crear-columna', [KanbanController::class, 'crearColumna'])->name('kanban.crearColumna');

// Obtener columnas de un tablero (opcional, si las gestionas por separado)
Route::get('public/kanban/{tableroId}/columnas', [KanbanController::class, 'mostrarColumnas'])->name('kanban.mostrarColumnas');

// Eliminar una columna de un tablero
Route::delete('public/kanban/eliminar-columna/{columnaId}', [KanbanController::class, 'eliminarColumna'])->name('kanban.eliminarColumna');

//Route::delete('/kanban/{tableroId}/eliminar-columna/{columnaId}', [KanbanController::class, 'eliminarColumna'])->name('kanban.eliminarColumna');


// PARA OBTENER LAS TAREAS DE UNA COLUMNA

// Crear una nueva tarea en una columna
Route::post('public/kanban/{columnaId}/crear-tarea', [KanbanController::class, 'crearTarea'])->name('kanban.crearTarea');

Route::get('public/kanban/{columnaId}/tareas', [KanbanController::class, 'mostrarTareas'])->name('kanban.mostrarTarea');


// Mover una tarea a otra columna
Route::put('/kanban/tareas/{tareaId}/mover', [KanbanController::class, 'moveTask'])->name('kanban.moveTask');

// Eliminar una tarea de una columna

Route::delete('public/kanban/eliminar-tarea/{tareaId}', [KanbanController::class, 'eliminarTarea'])->name('kanban.eliminarTarea');


// Mover una tarea a otra columna
Route::put('public/kanban/{columnaId}/tareas/{task_id}/mover', [KanbanController::class, 'moverTarea'])->name('kanban.moverTarea');




//---------------------------------------------------------------

// PARA USAR LA API DE JITSI
// Mostrar la vista para crear salas Jitsi
Route::get('/jitsi', function () {
    return view('jitsi');
});

// Crear una sala Jitsi
Route::post('/jitsi/create-room', [JitsiController::class, 'createRoom']);



require __DIR__ . '/auth.php';
