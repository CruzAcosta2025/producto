@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h2 class="font-semibold text-2xl text-indigo-600 dark:text-indigo-400 leading-tight mt-3 mb-6">
        {{ __('Crear KanbanBoard') }}
    </h2>

    <div class="container mx-auto p-6">
        <!-- Título y Selección de Proyecto -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4 w-full">
                <!-- Selector de proyecto ajustado al tamaño disponible -->
                <select id="select-proyecto" class="border border-gray-300 rounded-lg p-2 w-80">
                    @foreach ($proyectos as $proyecto)
                    <option value="{{ $proyecto->id }}">{{ $proyecto->nombre }}</option>
                    @endforeach
                </select>

                <!-- Botón para agregar nuevo tablero con el mismo tamaño que el select -->
                <button id="btn-nuevo-tablero" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-md w-48">
                    + Nuevo Tablero
                </button>
            </div>
        </div>
    </div>

    <!-- Contenedor de Tableros -->
    <div id="kanban-tableros" class="flex gap-6 overflow-x-auto max-w-full flex-wrap">
        <!-- Los tableros se cargarán dinámicamente aquí -->
    </div>
</div>


<!-- Template para Tablero -->
<template id="template-tablero">
    <div class="bg-white rounded-lg shadow-lg p-4 w-3/4 flex-shrink-0">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-700 tablero-nombre"></h3>
            <div class="flex gap-2">
                <button class="text-yellow-500 hover:text-yellow-600 btn-editar-tablero">
                    ✏️
                </button>
                <button class="text-red-500 hover:text-red-600 btn-eliminar-tablero">
                    🗑️
                </button>
            </div>
        </div>
        <div class="kanban-columnas flex gap-6 overflow-x-auto">
            <!-- Columnas se cargarán dinámicamente aquí -->
        </div>
        <button class="mt-4 bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-md btn-nueva-columna">
            + Agregar Columna
        </button>
    </div>
</template>

<!-- Template para Columna -->
<template id="template-columna">
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4" data-columna-id="">
        <div class="flex justify-between items-center mb-3">
            <h4 class="text-gray-700 font-medium columna-nombre"></h4>
            <button class="text-red-500 hover:text-red-600 btn-eliminar-columna">
                🗑️
            </button>
        </div>
        <div class="kanban-tareas flex flex-col gap-4">
            <!-- Las tareas se cargarán dinámicamente aquí -->
        </div>
        <button class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-md btn-nueva-tarea">
            + Agregar Tarea
        </button>
    </div>
</template>

<!-- Template para Tarea -->
<template id="template-tarea">
    <div class="bg-white border border-gray-200 rounded-lg shadow p-3" data-tarea-id="">

        <p class="text-gray-700 tarea-nombre"></p>

        <!-- Botones para mover la tarea -->
        <div class="flex justify-between">
            <button class="text-blue-500 hover:text-blue-600 btn-mover-izquierda"> <- </button>
                    <button class="text-blue-500 hover:text-blue-600 btn-mover-derecha"> -> </button>
        </div>

        <button class="text-red-500 hover:text-red-600 btn-eliminar-tarea float-right">
            🗑️
        </button>
    </div>
</template>

<!-- Modal para Crear/Editar Tableros -->
<div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden" id="modal-tablero">
    <div class="bg-white rounded-lg shadow-lg w-1/3">
        <form id="form-tablero">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold text-gray-700" id="modal-tablero-title">Crear Tablero</h2>
            </div>
            <div class="p-4">
                <input type="hidden" id="tablero-id">
                <div class="mb-4">
                    <label for="tablero-nombre" class="block text-gray-700 font-medium">Nombre del Tablero</label>
                    <input type="text" id="tablero-nombre" class="w-full border border-gray-300 rounded-lg p-2 mt-1" placeholder="Nombre del tablero">
                </div>
            </div>
            <div class="p-4 border-t flex justify-end gap-2">
                <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md" id="modal-cancelar">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Crear/Editar Columnas -->
<div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden" id="modal-columna">
    <div class="bg-white rounded-lg shadow-lg w-1/3">
        <form id="form-columna">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold text-gray-700" id="modal-columna-title">Crear Columna</h2>
            </div>
            <div class="p-4">
                <input type="hidden" id="columna-id">
                <div class="mb-4">
                    <label for="columna-nombre" class="block text-gray-700 font-medium">Nombre de la Columna</label>
                    <input type="text" id="columna-nombre" class="w-full border border-gray-300 rounded-lg p-2 mt-1" placeholder="Nombre de la columna">
                </div>
            </div>
            <div class="p-4 border-t flex justify-end gap-2">
                <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md" id="modal-cancelar-columna">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Crear/Editar Tareas -->
<div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden" id="modal-tarea">
    <div class="bg-white rounded-lg shadow-lg w-1/3">
        <form id="form-tarea">
            <div class="p-4 border-b">
                <h2 class="text-xl font-bold text-gray-700" id="modal-tarea-title">Crear Tarea</h2>
            </div>
            <div class="p-4">
                <input type="hidden" id="tarea-id">

                <!-- Nombre de la tarea -->
                <div class="mb-4">
                    <label for="tarea-nombre" class="block text-gray-700 font-medium">Nombre de la Tarea</label>
                    <input type="text" id="tarea-nombre" class="w-full border border-gray-300 rounded-lg p-2 mt-1" placeholder="Nombre de la tarea">
                </div>

                <!-- Estado de la tarea -->
                <div class="mb-4">
                    <label for="tarea-estado" class="block text-gray-700 font-medium">Estado de la Tarea</label>
                    <select id="tarea-estado" class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                        <option value="pendiente">pendiente</option>
                        <option value="en progreso">en progreso</option>
                        <option value="completada">completada</option>
                    </select>
                </div>

                <!-- Prioridad de la tarea -->
                <div class="mb-4">
                    <label for="tarea-prioridad" class="block text-gray-700 font-medium">Prioridad de la Tarea</label>
                    <select id="tarea-prioridad" class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                        <option value="baja">baja</option>
                        <option value="media">media</option>
                        <option value="alta">alta</option>
                    </select>
                </div>

                <!-- Fecha de vencimiento -->
                <div class="mb-4">
                    <label for="tarea-fecha-vencimiento" class="block text-gray-700 font-medium">Fecha de Vencimiento</label>
                    <input type="date" id="tarea-fecha-vencimiento" class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                </div>

                <!-- Selección de usuarios -->
                <div class="mb-4">
                    <label for="tarea-usuarios" class="block text-gray-700 font-medium">Asignar Usuarios</label>
                    <select id="tarea-usuarios" class="w-full border border-gray-300 rounded-lg p-2 mt-1" multiple>
                        <option value="">Seleccione miembros</option>
                        @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            <div class="p-4 border-t flex justify-end gap-2">
                <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md" id="modal-cancelar-tarea">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>


@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const apiUrl = '{{ route("kanban.index") }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Selección de elementos del DOM
        const selectProyecto = document.getElementById('select-proyecto');
        const kanbanTableros = document.getElementById('kanban-tableros');
        const btnNuevoTablero = document.getElementById('btn-nuevo-tablero');
        const modalTablero = document.getElementById('modal-tablero');
        const formTablero = document.getElementById('form-tablero');
        const modalCancelar = document.getElementById('modal-cancelar');
        const modalTarea = document.getElementById('modal-tarea');
        const formTarea = document.getElementById('form-tarea');
        const tareaNombreInput = document.getElementById('tarea-nombre');
        const tareaIdInput = document.getElementById('tarea-id');
        const modalCancelarTarea = document.getElementById('modal-cancelar-tarea');
        const tableroNombreInput = document.getElementById('tablero-nombre');
        const tableroIdInput = document.getElementById('tablero-id');
        const modalTitleTarea = document.getElementById('modal-tarea-title');
        const modalTitle = document.getElementById('modal-tablero-title');


        // Mostrar modal de tablero
        function mostrarModal() {
            modalTablero.classList.remove('hidden');
        }

        // Ocultar modal de tablero
        function ocultarModal() {
            modalTablero.classList.add('hidden');
            tableroIdInput.value = '';
            tableroNombreInput.value = '';
            modalTitle.textContent = 'Crear Tablero';
        }

        function ocultarModalTarea() {
            modalTarea.classList.add('hidden');
            tareaIdInput.value = '';
            tareaNombreInput.value = '';
            modalTitle.textContent = 'Crear Tarea';
        }

        

        // Mostrar modal al hacer clic en "Nuevo Tablero"
        btnNuevoTablero.addEventListener('click', function() {
            mostrarModal();
        });

        // Crear o editar tablero
        formTablero.addEventListener('submit', function(e) {
            e.preventDefault();
            const tableroId = tableroIdInput.value;
            const nombre = tableroNombreInput.value;
            const proyectoId = selectProyecto.value;

            const url = tableroId ?
                `public/kanban/${proyectoId}/editar-tablero/${tableroId}` :
                `public/kanban/${proyectoId}/crear-tablero`;
            const method = tableroId ? 'PUT' : 'POST';

            fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        nombre
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarTableros(proyectoId);
                        ocultarModal();
                    } else {
                        console.error('Error al guardar el tablero:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Eliminar tablero
        function eliminarTablero(tableroId) {
            const proyectoId = selectProyecto.value;
            if (!confirm('¿Seguro que deseas eliminar este tablero?')) return;

            fetch(`public/kanban/${proyectoId}/eliminar-tablero/${tableroId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarTableros(proyectoId);
                    } else {
                        console.error('Error al eliminar el tablero:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Cargar tableros de un proyecto
        function cargarTableros(proyectoId) {
            fetch(`public/kanban/${proyectoId}/tableros`)
                .then(response => response.json())
                .then(data => {
                    console.log('Datos recibidos:', data); // Para inspeccionar la respuesta

                    if (!data.tableros || !Array.isArray(data.tableros)) {
                        throw new Error('La respuesta no contiene un arreglo "tableros".');
                    }

                    kanbanTableros.innerHTML = '';

                    data.tableros.forEach(tablero => {
                        console.log('Tablero:', tablero); // Inspeccionar cada tablero
                        agregarTableroDOM(tablero);

                        //tablero.columns.forEach(columna => {
                        //  console.log('Columna:', columna); // Inspeccionar cada columna
                        //agregarColumnaDOM(columna, kanbanTableros, tablero);

                        //columna.tasks.forEach(tarea => {
                        //  console.log('Tarea:', tarea); // Inspeccionar cada tarea
                        //agregarTareaDOM(tarea, kanbanTableros);
                        //});
                        //});
                    });
                })
                .catch(error => console.error('Error al cargar tableros:', error));
        }




        function editarTablero(tablero) {
            const modalTablero = document.getElementById('modal-tablero');
            const tableroIdInput = document.getElementById('tablero-id');
            const tableroNombreInput = document.getElementById('tablero-nombre');
            const modalTitle = document.getElementById('modal-tablero-title');

            // Configurar los valores del modal con los datos del tablero
            tableroIdInput.value = tablero.id;
            tableroNombreInput.value = tablero.nombre;
            modalTitle.textContent = 'Editar Tablero';

            // Mostrar el modal
            modalTablero.classList.remove('hidden');
        }





        // Agregar tablero al DOM
        function agregarTableroDOM(tablero) {
            const template = document.getElementById('template-tablero').content.cloneNode(true);
            template.querySelector('.tablero-nombre').textContent = tablero.nombre || 'Sin nombre';

            const btnEditar = template.querySelector('.btn-editar-tablero');
            const btnEliminar = template.querySelector('.btn-eliminar-tablero');
            const btnNuevaColumna = template.querySelector('.btn-nueva-columna');
            const columnasContainer = template.querySelector('.kanban-columnas');

            // Asegúrate de pasar el tablero aquí
            if (Array.isArray(tablero.columnas)) {
                tablero.columnas.forEach(columna => agregarColumnaDOM(columna, columnasContainer, tablero));
            }

            btnEditar.addEventListener('click', () => editarTablero(tablero));
            btnEliminar.addEventListener('click', () => eliminarTablero(tablero.id));
            btnNuevaColumna.addEventListener('click', () => crearColumna(tablero.id, columnasContainer));

            kanbanTableros.appendChild(template);
        }


        // PARA LA GESTION DE COLUMNAS
        // Crear columna
        function crearColumna(tableroId, columnasContainer) {
            const nombre = prompt('Nombre de la nueva columna:');
            if (!nombre) return;

            fetch(`public/kanban/${tableroId}/crear-columna`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        nombre
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta al crear columna:', data); // Aquí puedes inspeccionar los datos

                    if (data.success) {
                        agregarColumnaDOM(data.columna, columnasContainer);
                    } else {
                        console.error('Error al crear columna:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function agregarColumnaDOM(columna, columnasContainer, tablero) {
            if (!columna.id) {
                console.error('La columna no tiene un id válido:', columna);
                return;
            }

            const template = document.getElementById('template-columna').content.cloneNode(true);
            const columnaElement = template.querySelector('[data-columna-id]');

            columnaElement.setAttribute('data-columna-id', columna.id); // Asigna el ID de la columna al contenedor
            template.querySelector('.columna-nombre').textContent = columna.nombre;

            const btnEliminar = template.querySelector('.btn-eliminar-columna');
            const btnNuevaTarea = template.querySelector('.btn-nueva-tarea');
            const tareasContainer = template.querySelector('.kanban-tareas');

            // Asegúrate de que columna tiene un id y pasamos tablero completo aquí
            btnEliminar.addEventListener('click', () => eliminarColumna(columna.id, columnasContainer));

            btnNuevaTarea.addEventListener('click', () => crearTarea(columna.id, tareasContainer));

            columnasContainer.appendChild(template);
        }



        function eliminarColumna(columnaId, columnasContainer) {
            if (!confirm('¿Seguro que deseas eliminar esta columna?')) return;

            // Realizar solicitud de eliminación de columna
            fetch(`public/kanban/eliminar-columna/${columnaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Eliminar la columna del DOM solo si la eliminación fue exitosa
                        const columnaElement = columnasContainer.querySelector(`[data-columna-id="${columnaId}"]`);
                        if (columnaElement) {
                            columnasContainer.removeChild(columnaElement);
                        }
                    } else {
                        alert('Error: ' + data.error); // Mejor manejo de error
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        //PARA LA GESTION DE TAREAS
        // Crear tarea

        function crearTarea(columnaId, tareasContainer) {
            // Mostrar el modal de tarea
            const modalTarea = document.getElementById('modal-tarea');
            const tareaNombreInput = document.getElementById('tarea-nombre');
            const tareaEstadoInput = document.getElementById('tarea-estado');
            const tareaPrioridadInput = document.getElementById('tarea-prioridad');
            const tareaFechaVencimientoInput = document.getElementById('tarea-fecha-vencimiento'); // Nuevo campo
            const tareaUsuariosSelect = document.getElementById('tarea-usuarios'); // Nuevo campo para usuarios

            // Limpiar los campos
            tareaNombreInput.value = '';
            tareaEstadoInput.value = 'Pendiente'; // Valor por defecto
            tareaPrioridadInput.value = 'Baja'; // Valor por defecto
            tareaFechaVencimientoInput.value = ''; // Limpiar la fecha
            tareaUsuariosSelect.selectedIndex = -1; // Limpiar la selección de usuarios

            // Mostrar el modal
            modalTarea.classList.remove('hidden');

            // Manejar el envío del formulario
            const formTarea = document.getElementById('form-tarea');
            formTarea.onsubmit = function(e) {
                e.preventDefault();
                const nombre = tareaNombreInput.value;
                const estado = tareaEstadoInput.value;
                const prioridad = tareaPrioridadInput.value;
                const fechaVencimiento = tareaFechaVencimientoInput.value; // Obtener la fecha de vencimiento

                // Obtener los usuarios seleccionados
                const tareaUsuariosSeleccionados = Array.from(tareaUsuariosSelect.selectedOptions).map(option => option.value);

                fetch(`public/kanban/${columnaId}/crear-tarea`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            nombre,
                            estado,
                            prioridad,
                            fecha_vencimiento: fechaVencimiento, // Incluir la fecha en el cuerpo de la solicitud
                            usuarios: tareaUsuariosSeleccionados // Incluir los usuarios seleccionados
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            agregarTareaDOM(data.tarea, tareasContainer);
                            ocultarModalTarea();
                            
                        } else {
                            console.error('Error al crear tarea:', data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            };
        }


        // Agregar tarea al DOM
        function agregarTareaDOM(tarea, tareasContainer) {
            const template = document.getElementById('template-tarea').content.cloneNode(true);
            const tareaElement = template.querySelector('[data-tarea-id]');
            tareaElement.setAttribute('data-tarea-id', tarea.id);

            // Obtener nombres de los usuarios asignados
            const usuariosNombres = (tarea.usuarios && tarea.usuarios.length > 0) ?
                tarea.usuarios.map(usuario => usuario.name).join(', ') : 'Ningún usuario asignado';

            // Rellenar la información de la tarea
            template.querySelector('.tarea-nombre').innerHTML = `
        <strong>${tarea.nombre}</strong>
        <p class="text-sm text-gray-500">Estado: ${tarea.estado}</p>
        <p class="text-sm text-gray-500">Prioridad: ${tarea.prioridad}</p>
        <p class="text-sm text-gray-500">Vence: ${tarea.fecha_vencimiento || 'Sin fecha'}</p>
        <p class="text-sm text-gray-500">Asignados: ${usuariosNombres}</p>
    `;

            // Añadir los eventos para los botones de mover
            
            const btnMoverIzquierda = template.querySelector('.btn-mover-izquierda');
            const btnMoverDerecha = template.querySelector('.btn-mover-derecha');

            btnMoverIzquierda.addEventListener('click', () => moverTarea(tarea.id, tareasContainer.closest('[data-columna-id]').getAttribute('data-columna-id'), 'left'));
            btnMoverDerecha.addEventListener('click', () => moverTarea(tarea.id, tareasContainer.closest('[data-columna-id]').getAttribute('data-columna-id'), 'right'));

            // Seleccionar el botón de eliminar dentro del template
            const btnEliminarTarea = template.querySelector('.btn-eliminar-tarea');

            // Añadir el evento de click para eliminar la tarea
            btnEliminarTarea.addEventListener('click', () => eliminarTarea(tarea.id, tareasContainer));

            // Añadir la tarea al contenedor de tareas
            tareasContainer.appendChild(template);
        }

        // Función para mover tarea
        function moverTarea(tareaId, columnaId, direccion) {
            // Seleccionar todas las columnas dentro de su contenedor
            const columnas = Array.from(document.querySelectorAll('[data-columna-id]'));
            const currentColumna = document.querySelector(`[data-columna-id="${columnaId}"]`);

            if (!currentColumna) {
                console.error("No se encontró la columna actual en el DOM.");
                return;
            }

            const indexColumna = columnas.indexOf(currentColumna);

            if (indexColumna === -1) {
                console.error("No se encontró la columna actual en el array.");
                return;
            }

            let nuevaColumnaId;
            if (direccion === 'left' && indexColumna > 0) {
                nuevaColumnaId = columnas[indexColumna - 1]?.getAttribute('data-columna-id');
            } else if (direccion === 'right' && indexColumna < columnas.length - 1) {
                nuevaColumnaId = columnas[indexColumna + 1]?.getAttribute('data-columna-id');
            }

            if (!nuevaColumnaId) {
                console.warn("No hay columna válida en la dirección especificada.");
                return;
            }

            // Solicitud para mover la tarea
            fetch(`public/kanban/${columnaId}/tareas/${tareaId}/mover`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        kanban_column_id: nuevaColumnaId
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mover la tarea en el DOM
                        const tareaElement = document.querySelector(`[data-tarea-id="${tareaId}"]`);
                        const nuevaColumnaContainer = document.querySelector(`[data-columna-id="${nuevaColumnaId}"] .kanban-tareas`);
                        nuevaColumnaContainer.appendChild(tareaElement);
                        console.log('Tarea movida correctamente.');
                    } else {
                        console.error('Error al mover la tarea:', data.error);
                    }
                })
                .catch(error => console.error('Error al mover la tarea:', error));
        }



        // Asignar eventos a los botones de mover tarea
        document.querySelectorAll('.btn-mover-izquierda').forEach(btn => {
            btn.addEventListener('click', () => {
                const tareaId = btn.closest('[data-tarea-id]').getAttribute('data-tarea-id');
                const columnaId = btn.closest('[data-columna-id]').getAttribute('data-columna-id');
                moverTarea(tareaId, columnaId, 'left');
            });
        });

        document.querySelectorAll('.btn-mover-derecha').forEach(btn => {
            btn.addEventListener('click', () => {
                const tareaId = btn.closest('[data-tarea-id]').getAttribute('data-tarea-id');
                const columnaId = btn.closest('[data-columna-id]').getAttribute('data-columna-id');
                moverTarea(tareaId, columnaId, 'right');
            });
        });



        // Eliminar tarea
        function eliminarTarea(tareaId, tareasContainer) {
            if (!confirm('¿Seguro que deseas eliminar esta tarea?')) return;

            const columnaId = tareasContainer.closest('[data-columna-id]').getAttribute('data-columna-id');

            fetch(`public/kanban/eliminar-tarea/${tareaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Eliminar la tarea del DOM solo si la eliminación fue exitosa
                        const tareaElement = tareasContainer.querySelector(`[data-tarea-id="${tareaId}"]`);
                        if (tareaElement) {
                            tareasContainer.removeChild(tareaElement);
                        }
                    } else {
                        alert('Error: ' + data.message); // Mejor manejo de error
                    }
                })
                .catch(error => console.error('Error:', error));
        }



        // Inicializar
        selectProyecto.addEventListener('change', function() {
            cargarTableros(this.value);
        });

        if (selectProyecto.value) {
            cargarTableros(selectProyecto.value);
        }

        modalCancelar.addEventListener('click', ocultarModal);
        modalCancelarTarea.addEventListener('click', ocultarModalTarea);

    });
</script>
@endsection