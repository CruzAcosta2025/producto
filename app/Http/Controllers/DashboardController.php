<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\Tarea;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index()
    {
        $today = Carbon::today();
        $proyectos = Proyecto::all();
        $totalProyectos = Proyecto::count();
        $totalTareas = Tarea::count();

        // Contar tareas completadas y pendientes
        $tareasCompletadas = Tarea::where('estado', 'completada')->count();
        $tareasPendientes = Tarea::where('estado', 'pendiente')->count();

        // Proyectos que vencen en los próximos 7 días
        $proyectosProximos = Proyecto::where('fecha_fin', '>', $today)
            ->where('fecha_fin', '<=', $today->copy()->addDays(7))
            ->get();

        // Proyectos atrasados
        $proyectosAtrasados = Proyecto::where('fecha_fin', '<', $today)->get();

        return view('dashboard', [
            'totalProyectos' => json_encode($totalProyectos),
            'totalTareas' => json_encode($totalTareas),
            'tareasCompletadas' => json_encode($tareasCompletadas),
            'tareasPendientes' => json_encode($tareasPendientes),
            'proyectos' => json_encode($proyectos),
            'proyectosProximos' => json_encode($proyectosProximos),
            'proyectosAtrasados' => json_encode($proyectosAtrasados),
        ]);
    }
}
