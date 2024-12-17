@extends('layouts.app')

@section('title', 'Proyectos')

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:px-4 lg:px-6">
    <h2 class="font-semibold text-2xl text-indigo-600 dark:text-indigo-400 leading-tight mt-3 mb-6">
        {{ __('Tus Proyectos') }}
    </h2>

    <!-- Lista de proyectos -->
    <div class="space-y-6">
        @foreach($proyectos as $proyecto)
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4 flex flex-col space-y-4">
            <div class="flex flex-col">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                    <strong>Nombre: </strong>{{ $proyecto->nombre }}
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mt-2">
                    <strong>Descripción: </strong>{{ $proyecto->descripcion }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <strong>Fecha de Inicio: </strong>{{ $proyecto->fecha_inicio }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    <strong>Fecha de Fin: </strong>{{ $proyecto->fecha_fin }}
                </p>
            </div>

            <!-- Usuarios asignados -->
            <div class="mt-4">
                <h4 class="text-lg font-medium text-gray-800 dark:text-gray-100">Usuarios asignados:</h4>
                <div class="mt-2 overflow-x-auto">
                    <table class="min-w-full bg-gray-100 dark:bg-gray-700 text-sm text-left text-gray-800 dark:text-gray-200 rounded-lg shadow-lg">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-800">
                                <th class="py-2 px-4 font-semibold text-gray-800 dark:text-gray-200">Nombre</th>
                                <th class="py-2 px-4 font-semibold text-gray-800 dark:text-gray-200">Correo</th>
                                <th class="py-2 px-4 font-semibold text-gray-800 dark:text-gray-200">Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proyecto->usuarios as $usuario)
                            <tr class="border-b border-gray-300 dark:border-gray-600">
                                <td class="py-2 px-4 text-gray-800 dark:text-gray-200">
                                    {{ $usuario->name }}
                                </td>
                                <td class="py-2 px-4 text-gray-600 dark:text-gray-300">
                                    {{ $usuario->email }}
                                </td>
                                <td class="py-2 px-4 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ ucfirst($usuario->role) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex justify-end space-x-4 mt-4">
                @if(auth()->user()->role == 'administrador' || auth()->user()->role == 'jefe')
                    @if(auth()->user()->role == 'administrador' || $proyecto->usuario_id == auth()->user()->id || $proyecto->usuarios->contains(auth()->user()))
                    <!-- Administrador o el jefe que creó el proyecto puede editar y eliminar -->
                    <button data-id="{{ $proyecto->id }}" onclick="openEditModal(this)" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition duration-300 transform hover:scale-105">
                        Editar
                    </button>

                    <form action="{{ route('proyectos.destroy', $proyecto->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-300 transform hover:scale-105" onclick="return confirm('¿Estás seguro de que deseas eliminar este proyecto?')">
                            Eliminar
                        </button>
                    </form>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-300">No tienes permisos para editar este proyecto.</p>
                    @endif
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal de Edición -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center hidden bg-gray-500 bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg w-full max-w-lg">
        <h3 class="text-2xl text-gray-800 dark:text-gray-100 mb-4">Editar Proyecto</h3>
        <form id="editForm" action="#" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="nombre" class="block text-gray-800 dark:text-gray-200">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="mt-1 block w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white" required>
            </div>
            <div class="mb-4">
                <label for="descripcion" class="block text-gray-800 dark:text-gray-200">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="mt-1 block w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white" required></textarea>
            </div>
            <div class="mb-4">
                <label for="fecha_inicio" class="block text-gray-800 dark:text-gray-200">Fecha de Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="mt-1 block w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white" required>
            </div>
            <div class="mb-4">
                <label for="fecha_fin" class="block text-gray-800 dark:text-gray-200">Fecha de Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="mt-1 block w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white" required>
            </div>
            <div class="mb-4">
                <label for="usuarios" class="block text-gray-800 dark:text-gray-200">Selecciona un usuario</label>
                <select id="usuarios" name="usuarios[]" class="mt-1 block w-full px-4 py-2 border rounded-md dark:bg-gray-700 dark:text-white" multiple required>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" 
                                @if(in_array($usuario->id, $proyecto->usuarios->pluck('id')->toArray())) selected @endif>
                            {{ $usuario->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg">Guardar</button>
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">Cancelar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openEditModal(button) {
        var proyectoId = button.getAttribute('data-id');
        fetch(`public/proyectos/${proyectoId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('nombre').value = data.proyecto.nombre;
                document.getElementById('descripcion').value = data.proyecto.descripcion;
                document.getElementById('fecha_inicio').value = data.proyecto.fecha_inicio;
                document.getElementById('fecha_fin').value = data.proyecto.fecha_fin;

                let selectedUsers = data.proyecto.usuarios.map(usuario => usuario.id);
                let selectElement = document.getElementById('usuarios');
                for (let option of selectElement.options) {
                    option.selected = selectedUsers.includes(parseInt(option.value));
                }

                let form = document.getElementById('editForm');
                form.action = `public/proyectos/${proyectoId}`;
                
                document.getElementById('editModal').classList.remove('hidden');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endsection
