<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    // Mostrar todos las tareas
    public function index()
    {
        $tareas = Tarea::all();
        return view('tareas.index', compact('tareas'));
    }

    // Mostrar una tarea en particular (con proyecto y usuarios asignados)
    public function show($id)
    {
        $tarea = Tarea::with('proyecto', 'usuarios.usuario')->findOrFail($id);
        return view('tareas.show', compact('tarea'));
    }

    // Mostrar formulario para crear una nueva tarea
    public function create()
    {
        $proyectos = Proyecto::all(); // Obtener todos los proyectos para el select
        return view('tareas.create', compact('proyectos'));
    }

    // Almacenar una nueva tarea
    public function store(Request $request)
    {
        $request->validate([
            'proyecto_id' => 'required|integer|exists:proyectos,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|in:pendiente,en progreso,completada',
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_vencimiento' => 'required|date',
        ]);

        Tarea::create($request->all());

        return redirect()->route('dashboard')->with('success', 'Tarea creada exitosamente.');
    }

    // Mostrar un formulario para editar una tarea existente
    public function edit($id)
    {
        $tarea = Tarea::findOrFail($id);
        $proyectos = Proyecto::all(); // Obtener todos los proyectos para el select
        return view('tareas.edit', compact('tarea', 'proyectos'));
    }

    // Actualizar una tarea existente
    // Actualizar tarea
    public function update(Request $request, $id)
    {
        $tarea = Tarea::findOrFail($id);

        $request->validate([
            'proyecto_id' => 'nullable|integer|exists:proyectos,id',
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,en progreso,completada',
            'prioridad' => 'nullable|in:baja,media,alta',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $tarea->update($request->all());

        return redirect()->route('dashboard')->with('success', 'Tarea actualizada exitosamente.');
    }


    // Eliminar una tarea
    public function destroy($id)
    {
        $tarea = Tarea::findOrFail($id);
        $tarea->delete();

        return redirect()->route('dashboard')->with('success', 'Tarea eliminada exitosamente.');
    }
}
