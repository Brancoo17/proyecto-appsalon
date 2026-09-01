document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    buscarPorFecha();
    cambiarEstadoTurno();
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