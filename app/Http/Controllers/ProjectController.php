<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Filtrar proyectos según el rol
        if ($user->role == 'administrador') {
            // Si el usuario es administrador, se muestran todos los proyectos
            $proyectos = Proyecto::with('usuarios')->get();
        } elseif ($user->role == 'jefe') {
            // Si el usuario es jefe, se muestran los proyectos que él creó o en los que está asignado
            $proyectos = Proyecto::with('usuarios') // Cargar usuarios asociados
                ->where('usuario_id', $user->id)  // Proyectos que el jefe ha creado
                ->orWhereHas('usuarios', function ($query) use ($user) {
                    $query->where('proyecto_usuario.usuario_id', $user->id);  // Proyectos en los que el jefe está asignado
                })
                ->get();
        } else {
            // Si el usuario es miembro, solo los proyectos en los que está asignado
            $proyectos = Proyecto::with('usuarios') // Cargar usuarios asociados
                ->whereHas('usuarios', function ($query) use ($user) {
                    $query->where('proyecto_usuario.usuario_id', $user->id);
                })
                ->get();
        }

        // Obtener todos los usuarios para el formulario de edición
        $usuarios = User::with('proyectos')->get();

        return view('index', compact('proyectos', 'usuarios'));
    }



    // Mostrar un proyecto específico (AJAX)
    public function show($id)
    {
        $proyecto = Proyecto::with('usuarios')->find($id);
        if (!$proyecto) {
            return response()->json(['error' => 'Proyecto no encontrado'], 404); // Respuesta JSON con error
        }
        return response()->json(['proyecto' => $proyecto]); // Devolver proyecto en formato JSON
    }

    public function create()
    {
        $user = auth()->user();

        // Filtrar usuarios según el rol
        if ($user->role == 'administrador') {
            // Si el usuario es administrador, cargar solo jefes
            $usuarios = User::where('role', 'jefe')->get();
        } elseif ($user->role == 'jefe') {
            // Si el usuario es jefe, cargar solo miembros
            $usuarios = User::where('role', 'miembro')->get();
        } else {
            // Si es miembro, no se pueden agregar otros miembros
            $usuarios = collect(); // Devolver una colección vacía
        }

        // Mostrar la vista con los usuarios filtrados
        return view('create', compact('usuarios'));
    }




    public function store(Request $request)
    {
        try {
            // Validación
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'usuarios' => 'required|array|min:1',
                'usuarios.*' => 'exists:users,id',
                'usuario_id' => 'exists:users,id', // Validar que el usuario exista
            ]);

            // Crear el proyecto
            $proyecto = Proyecto::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'usuario_id' => auth()->id(), // Asegúrate de que el usuario esté autenticado
            ]);

            // Asociar los usuarios seleccionados (solo miembros, si es jefe)
            if (auth()->user()->role == 'jefe') {
                // Filtrar solo miembros
                $usuarios = array_filter($validated['usuarios'], function ($usuarioId) {
                    $usuario = User::find($usuarioId);
                    return $usuario->role == 'miembro';
                });
                $proyecto->usuarios()->attach($usuarios);
            } else {
                // Si es administrador, asignar todos los usuarios seleccionados
                $proyecto->usuarios()->attach($validated['usuarios']);
            }

            return response()->json(['success' => true, 'proyecto' => $proyecto], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear el proyecto:', ['exception' => $e]);
            return response()->json(['success' => false, 'error' => 'Error del servidor. Inténtalo nuevamente.'], 500);
        }
    }



    public function edit($id)
    {
        $proyecto = Proyecto::findOrFail($id);

        // Verificar si el usuario tiene permisos para editar el proyecto
        if (auth()->user()->role == 'jefe' && $proyecto->usuario_id != auth()->id()) {
            return redirect()->route('proyectos.index')->with('error', 'No tienes permisos para editar este proyecto.');
        }

        $usuarios = User::all(); // Suponiendo que tienes un modelo User
        return view('proyectos.edit', compact('proyecto', 'usuarios'));
    }


    public function update(Request $request, $id)
    {
        // Encontrar el proyecto por ID
        $proyecto = Proyecto::findOrFail($id);

        // Validar los datos del formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'usuarios' => 'required|array|min:1', // Asegurarse de que se seleccionen usuarios
            'usuarios.*' => 'exists:users,id', // Validar que los usuarios existan en la base de datos
        ]);

        // Actualizar los datos básicos del proyecto
        $proyecto->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
        ]);

        // Asociar los usuarios seleccionados al proyecto (relación muchos a muchos)
        $proyecto->usuarios()->sync($validated['usuarios']); // Usamos sync para actualizar la relación

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('proyectos.index')->with('success', 'Proyecto actualizado exitosamente.');
    }




    // Eliminar un proyecto (AJAX)
    public function destroy($id)
    {
        // Buscar el proyecto
        $proyecto = Proyecto::find($id);

        // Verificar si el proyecto existe
        if (!$proyecto) {
            return response()->json(['error' => 'Proyecto no encontrado'], 404);
        }

        try {
            // Eliminar el proyecto
            $proyecto->delete();
            return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado exitosamente');


            // Responder con éxito
            return response()->json([
                'success' => true,
                'message' => 'Proyecto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el proyecto: ' . $e->getMessage()
            ], 500);
        }
    }
}
