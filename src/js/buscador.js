document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    buscarPorFecha();
    cambiarEstadoTurno();
    modalVerServiciosPeluquero();
    modalVerHorariosPeluquero();
}

function buscarPorFecha() {
    const fechaInput = document.querySelector('#fecha');

    if(fechaInput) {
        fechaInput.addEventListener('input', function(e) {
            const fechaSeleccionada = e.target.value;
            window.location = `?fecha=${fechaSeleccionada}`;
        });
    }
}

function cambiarEstadoTurno() {
    const badgesEstado = document.querySelectorAll('.badge-estado');

    badgesEstado.forEach(badge => {
        badge.addEventListener('click', async function() {
            const turnoId = this.dataset.id;
            const estadoActual = this.dataset.estado;

            if(!turnoId) return;

            const { value: nuevoEstado } = await Swal.fire({
                title: 'Cambiar Estado del Turno',
                text: 'Selecciona el nuevo estado para este turno:',
                input: 'select',
                inputOptions: {
                    'reservado': '📅 Reservado',
                    'completado': '✅ Completado',
                    'cancelado': '❌ Cancelado'
                },
                inputValue: estadoActual,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0da6f3',
                cancelButtonColor: '#64748b',
                customClass: {
                    popup: 'mi-alerta'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes seleccionar un estado';
                    }
                }
            });

            if(nuevoEstado && nuevoEstado !== estadoActual) {
                try {
                    const datos = new FormData();
                    datos.append('id', turnoId);
                    datos.append('estado', nuevoEstado);

                    const url = '/api/turnos/estado';
                    const respuesta = await fetch(url, {
                        method: 'POST',
                        body: datos
                    });
                    const resultado = await respuesta.json();

                    if(resultado.resultado) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Estado Actualizado',
                            text: 'El estado del turno ha sido modificado correctamente.',
                            timer: 1200,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'mi-alerta'
                            }
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resultado.mensaje || 'No se pudo actualizar el estado.',
                            customClass: {
                                popup: 'mi-alerta'
                            }
                        });
                    }
                } catch (error) {
                    console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error en la conexión.',
                        customClass: {
                            popup: 'mi-alerta'
                        }
                    });
                }
            }
        });
    });
}

function modalVerServiciosPeluquero() {
    const botones = document.querySelectorAll('.btn-ver-servicios-modal');
    botones.forEach(btn => {
        btn.addEventListener('click', function() {
            const nombre = this.dataset.nombre || 'Peluquero';
            let servicios = [];
            try {
                servicios = JSON.parse(this.dataset.servicios || '[]');
            } catch (e) {
                servicios = [];
            }

            let html = `<div class="modal-info-listado">`;
            if (servicios.length === 0) {
                html += `<p class="alerta-vacio-modal"><i class="fa-solid fa-circle-exclamation"></i> Este profesional no tiene servicios asignados actualmente.</p>`;
            } else {
                html += `<div class="lista-items-modal">`;
                servicios.forEach(s => {
                    html += `
                        <div class="item-modal-fila">
                            <strong><i class="fa-solid fa-scissors"></i> ${s.nombre}</strong>
                            <div class="item-modal-derecha">
                                <span class="badge-duracion"><i class="fa-regular fa-clock"></i> ${s.duracion || 30} min</span>
                                <span class="badge-precio">$${parseInt(s.precio).toLocaleString('es-AR')}</span>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
            }
            html += `</div>`;

            Swal.fire({
                title: `✂️ Servicios de ${nombre}`,
                html: html,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#0da6f3',
                customClass: { popup: 'mi-alerta alerta-info-peluquero-modal' }
            });
        });
    });
}

function modalVerHorariosPeluquero() {
    const diasNombres = {
        1: 'Lunes',
        2: 'Martes',
        3: 'Miércoles',
        4: 'Jueves',
        5: 'Viernes',
        6: 'Sábado'
    };

    const botones = document.querySelectorAll('.btn-ver-horarios-modal');
    botones.forEach(btn => {
        btn.addEventListener('click', function() {
            const nombre = this.dataset.nombre || 'Peluquero';
            let horarios = {};
            try {
                horarios = JSON.parse(this.dataset.horarios || '{}');
            } catch (e) {
                horarios = {};
            }

            let html = `<div class="modal-info-listado"><div class="lista-items-modal">`;
            for (let dia = 1; dia <= 6; dia++) {
                const h = horarios[dia];
                const activo = h ? (parseInt(h.activo) === 1) : false;
                const diaNombre = diasNombres[dia];

                html += `
                    <div class="item-modal-fila ${activo ? 'item-activo' : 'item-inactivo'}">
                        <strong>${diaNombre}</strong>
                        ${activo ? `<span class="badge-horario-activo"><i class="fa-regular fa-clock"></i> ${h.hora_inicio.substring(0,5)} a ${h.hora_fin.substring(0,5)} hs</span>` : `<span class="badge-no-laboral">No laboral</span>`}
                    </div>
                `;
            }
            html += `</div></div>`;

            Swal.fire({
                title: `🕒 Horarios de ${nombre}`,
                html: html,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#0da6f3',
                customClass: { popup: 'mi-alerta alerta-info-peluquero-modal' }
            });
        });
    });
}