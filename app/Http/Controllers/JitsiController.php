<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JitsiController extends Controller
{
    public function createRoom(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string|max:255',
        ]);

        // Obtener el nombre de la sala
        $roomName = $request->input('room_name');

        // Renderizar la vista de Jitsi con el nombre de la sala
        return view('jitsi', ['roomName' => $roomName]);
    }
}
