<?php

namespace App\Http\Controllers;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\Tarea;
use App\Models\Proyecto;
use App\Models\TareaUsuario;
use App\Models\User;
use Illuminate\Support\Facades\Log;


use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los proyectos
        $proyectos = Proyecto::with(['kanbanBoards.columns.tasks'])->get();
        $usuarios = User::all();

        // Verificar si es una solicitud AJAX
        if ($request->ajax()) {
            return response()->json($proyectos);
        }

        $proyectosConTableros = Proyecto::with(['kanbanBoards.columns.tasks'])->get();


        // Si no es AJAX, retornar la vista y pasar los proyectos
        return view('kanban', compact('proyectos', 'usuarios'));
    }



    public function crearTablero(Request $request, $proyectoId)
    {
        Log::info('Intentando crear un nuevo tablero Kanban');

        try {
            // Validar solo el nombre del tablero
            $validated = $request->validate([
                'nombre' => 'required|string|max:255', // Solo validamos el nombre
            ]);

            Log::info('Datos validados correctamente', $validated);

            // Buscar el proyecto al cual se le asociará el tablero
            $proyecto = Proyecto::find($proyectoId); // Usamos el proyectoId directamente
            if (!$proyecto) {
                Log::error('Proyecto no encontrado', ['proyecto_id' => $proyectoId]);
                return response()->json(['success' => false, 'error' => 'Proyecto no encontrado'], 404);
            }

            // Crear el tablero Kanban
            $tablero = new KanbanBoard();
            $tablero->nombre = $validated['nombre'];
            $tablero->proyecto_id = $proyecto->id;
            $tablero->save();

            Log::info('Tablero Kanban creado con éxito', ['tablero_id' => $tablero->id]);

            return response()->json([
                'success' => true,
                'message' => 'Tablero Kanban creado con éxito',
                'tablero' => $tablero,
            ], 201);
        } catch (\Exception $e) {
            // Log error en caso de excepción
            Log::error('Error al crear tablero Kanban', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al crear el tablero',
            ], 500);
        }
    }

    //public function obtenerTableros($proyectoId)
    //{
      //  $tableros = KanbanBoard::where('proyecto_id', $proyectoId)->get();
        //return response()->json(['tableros' => $tableros]);
   // }

   


    public function obtenerTableros($proyectoId)
    {
        $tableros = KanbanBoard::with('columns.tasks')->where('proyecto_id', $proyectoId)->get();
        return response()->json(['tableros' => $tableros]);
    }


    public function editarTablero(Request $request, $proyectoId, $tableroId)
    {
        try {
            // Validar los datos de entrada (por ejemplo, solo el nombre)
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
            ]);

            // Buscar el tablero asociado al proyecto
            $tablero = KanbanBoard::where('proyecto_id', $proyectoId)->findOrFail($tableroId);

            // Actualizar el nombre del tablero
            $tablero->nombre = $validated['nombre'];
            $tablero->save();

            return response()->json([
                'success' => true,
                'message' => 'Tablero actualizado con éxito',
                'tablero' => $tablero,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al editar el tablero', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Hubo un problema al actualizar el tablero',
            ], 500);
        }
    }


    public function eliminarTablero($proyectoId, $tableroId)
    {
        $tablero = KanbanBoard::where('proyecto_id', $proyectoId)->findOrFail($tableroId);
        $tablero->delete();

        return response()->json(['success' => true]);
    }



    public function crearColumna(Request $request, $tableroId)
    {
        try {
            // Validar los datos
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
            ]);

            // Buscar el tablero Kanban
            $tablero = KanbanBoard::find($tableroId);
            if (!$tablero) {
                return response()->json(['success' => false, 'error' => 'Tablero no encontrado'], 404);
            }

            // Crear la columna
            $columna = new KanbanColumn();
            $columna->nombre = $validated['nombre'];
            $columna->kanban_board_id = $tablero->id;
            $columna->save();

            return response()->json([
                'success' => true,
                'columna' => $columna,
            ], 201);
        } catch (\Exception $e) {
            // Enviar el mensaje de error real para depuración
            return response()->json(['success' => false, 'error' => 'Ocurrió un error al crear la columna: ' . $e->getMessage()], 500);
        }
    }



    public function mostrarColumnas($tableroId)
    {
        $tablero = KanbanBoard::find($tableroId);
        if (!$tablero) {
            return response()->json(['error' => 'Tablero no encontrado'], 404);
        }

        $columnas = $tablero->columnas; // Relación con las columnas

        return response()->json([
            'tablero' => $tablero,
            'columnas' => $columnas,
        ]);
    }



    public function eliminarColumna($columnaId)
    {
        try {
            // Buscar la columna por su ID
            $columna = KanbanColumn::findOrFail($columnaId);

            // Verificar si la columna tiene tareas
            if ($columna->tasks()->count() > 0) {
                return response()->json(['success' => false, 'error' => 'No se puede eliminar la columna, contiene tareas.'], 400);
            }

            // Eliminar la columna
            $columna->delete();

            return response()->json([
                'success' => true,
                'message' => 'Columna eliminada con éxito',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Hubo un problema al eliminar la columna: ' . $e->getMessage(),
            ], 500);
        }
    }





    public function crearTarea(Request $request, $columnaId)
    {
        try {
            $usuarios = User::all();

            // Validar los datos de entrada según la tabla 'tareas'
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'estado' => 'required|in:pendiente,en progreso,completada', // Valores posibles
                'prioridad' => 'required|in:baja,media,alta', // Valores posibles
                'fecha_vencimiento' => 'required|date', // Obligatorio según tu tabla
                'usuarios' => 'required|array|min:1', // Asegurarse de que se seleccionen usuarios
                'usuarios.*' => 'exists:users,id', // Validar que los usuarios existan en la base de datos
            ]);

            // Buscar la columna por ID
            $columna = KanbanColumn::find($columnaId);

            if (!$columna) {
                return response()->json([
                    'success' => false,
                    'error' => 'La columna no existe.',
                ], 400);
            }

            // Obtener el kanban_board asociado a la columna
            $kanbanBoard = KanbanBoard::find($columna->kanban_board_id);

            if (!$kanbanBoard || !$kanbanBoard->proyecto_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'La columna no está asociada a un proyecto válido.',
                ], 400);
            }

            // Obtener el proyecto_id desde el kanban_board
            $proyectoId = $kanbanBoard->proyecto_id;

            // Crear la tarea en la tabla 'tareas'
            $tarea = Tarea::create([
                'proyecto_id' => $proyectoId,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? '',
                'estado' => $validated['estado'],
                'prioridad' => $validated['prioridad'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
            ]);

            // Crear la relación en 'kanban_tasks' (conectar la tarea con la columna)
            $kanbanTask = KanbanTask::create([
                'kanban_column_id' => $columnaId,
                'tarea_id' => $tarea->id,
            ]);

            // Asignar los usuarios a la tarea (usando la tabla pivot tarea_usuario)
            foreach ($validated['usuarios'] as $usuarioId) {
                TareaUsuario::create([
                    'tarea_id' => $tarea->id,
                    'usuario_id' => $usuarioId,
                    'asignado_en' => now(), // Fecha de asignación
                ]);
            }

            // Devolver respuesta exitosa
            return response()->json([
                'success' => true,
                'tarea' => $tarea->load('usuarios'), // Cargar la relación 'usuarios' de la tarea
                'kanban_task' => $kanbanTask,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear la tarea en la columna', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }



    public function eliminarTarea($tareaId)
    {


        try {
            // Encuentra la tarea relacionada con la columna
            $kanbanTask = KanbanTask::findOrFail($tareaId);

            // Eliminar la tarea de la tabla 'kanban_tasks'
            $kanbanTask->delete();

            // Eliminar la tarea de la tabla 'tareas'
            $tarea = Tarea::findOrFail($kanbanTask->tarea_id);
            $tarea->delete();



            // Retorna una respuesta exitosa
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            // Si algo sale mal, revertir los cambios


            // Loguear el error para detalles
            \Log::error("Error al eliminar tarea: " . $e->getMessage());

            // Retornar un mensaje de error
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function mostrarTareas($columnaId)
    {
        $columna = KanbanColumn::find($columnaId);
        if (!$columna) {
            return response()->json(['error' => 'Columna no encontrada'], 404);
        }

        // Recupera las tareas asociadas a la columna, incluyendo los usuarios asignados
        $tareas = $columna->tasks()->with('usuarios')->get();

        return response()->json([
            'columna' => $columna,
            'tareas' => $tareas,
        ]);
    }



    // Mover una tarea a otra columna (AJAX)

    public function moverTarea(Request $request, $columnaId, $task_id)
    {
        // Validar que el ID de la columna sea válido
        $validated = $request->validate([
            'kanban_column_id' => 'required|integer|exists:kanban_columns,id'
        ]);

        try {
            // Encontrar la tarea y actualizar la columna
            $kanbanTask = KanbanTask::findOrFail($task_id);

            // Verificar si la columna existe
            $columna = KanbanColumn::findOrFail($columnaId);

            // Mover la tarea a la nueva columna
            $kanbanTask->update([
                'kanban_column_id' => $validated['kanban_column_id']
            ]);

            return response()->json([
                'success' => true,
                'kanbanTask' => $kanbanTask
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Hubo un problema al mover la tarea: ' . $e->getMessage()
            ], 500);
        }
    }
}
