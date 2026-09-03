document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    comprobarAlertaResultado();
    buscarPorFecha();
    cambiarEstadoTurno();
    modalVerServiciosPeluquero();
    modalVerHorariosPeluquero();
    buscadorClientes();
    modalVerTurnosCliente();
    iniciarBloqueoHorarios();
    iniciarDesbloqueoHorarios();
}

function comprobarAlertaResultado() {
    const urlParams = new URLSearchParams(window.location.search);
    const resultado = urlParams.get('resultado');

    if(resultado === '3') {
        Swal.fire({
            icon: 'success',
            title: '¡Turno Actualizado!',
            text: 'El turno ha sido modificado y actualizado exitosamente.',
            confirmButtonColor: '#0da6f3',
            customClass: { popup: 'mi-alerta' }
        });
        urlParams.delete('resultado');
        const nuevaQuery = urlParams.toString();
        const nuevaUrl = window.location.pathname + (nuevaQuery ? '?' + nuevaQuery : '');
        window.history.replaceState({}, '', nuevaUrl);
    }
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

function buscadorClientes() {
    const inputBuscador = document.querySelector('#buscador-clientes');
    const btnLimpiar = document.querySelector('#btn-limpiar-busqueda');
    const contadorVisibles = document.querySelector('#contador-visibles');
    const alertaSinResultados = document.querySelector('#sin-resultados-clientes');
    const filasClientes = document.querySelectorAll('.fila-cliente');

    if(!inputBuscador || filasClientes.length === 0) return;

    inputBuscador.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        let visibleCount = 0;

        if(query.length > 0) {
            if(btnLimpiar) btnLimpiar.style.display = 'inline-flex';
        } else {
            if(btnLimpiar) btnLimpiar.style.display = 'none';
        }

        filasClientes.forEach(fila => {
            const nombre = fila.dataset.nombre || '';
            const email = fila.dataset.email || '';
            const telefono = fila.dataset.telefono || '';

            if(nombre.includes(query) || email.includes(query) || telefono.includes(query)) {
                fila.style.display = '';
                visibleCount++;
            } else {
                fila.style.display = 'none';
            }
        });

        if(contadorVisibles) {
            contadorVisibles.textContent = visibleCount;
        }

        if(alertaSinResultados) {
            alertaSinResultados.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    });

    if(btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            inputBuscador.value = '';
            inputBuscador.dispatchEvent(new Event('input'));
            inputBuscador.focus();
        });
    }
}

function modalVerTurnosCliente() {
    const botones = document.querySelectorAll('.btn-ver-turnos-cliente');
    botones.forEach(btn => {
        btn.addEventListener('click', function() {
            const nombre = this.dataset.nombre || 'Cliente';
            let turnos = [];
            try {
                turnos = JSON.parse(this.dataset.turnos || '[]');
            } catch (e) {
                turnos = [];
            }

            let html = `<div class="modal-info-listado modal-turnos-cliente">`;
            if (turnos.length === 0) {
                html += `<p class="alerta-vacio-modal"><i class="fa-solid fa-calendar-xmark"></i> Este cliente no tiene turnos registrados.</p>`;
            } else {
                html += `<div class="lista-turnos-modal">`;
                turnos.forEach(t => {
                    const estado = (t.estado || 'reservado').toLowerCase();

                    // Formato de fecha dd/mm/yy (año de 2 dígitos)
                    let fechaFormateada = '';
                    if (t.fecha) {
                        const partes = t.fecha.split('-');
                        if (partes.length === 3) {
                            const anioCorto = partes[0].length === 4 ? partes[0].slice(2) : partes[0];
                            fechaFormateada = `${partes[2]}/${partes[1]}/${anioCorto}`;
                        } else {
                            fechaFormateada = t.fecha;
                        }
                    }

                    let estadoBadge = `<span class="badge-estado badge-estado--${estado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;

                    // Generar chips individuales para cada servicio
                    let serviciosChipsHtml = '';
                    if (t.servicios && t.servicios.length > 0) {
                        serviciosChipsHtml = t.servicios.map(s => `
                            <span class="chip-servicio-modal">
                                <i class="fa-solid fa-scissors"></i> ${s}
                            </span>
                        `).join('');
                    } else {
                        serviciosChipsHtml = `<span class="chip-servicio-modal"><i class="fa-solid fa-scissors"></i> Servicio general</span>`;
                    }

                    html += `
                        <div class="item-turno-modal">
                            <div class="header-turno-modal">
                                <div class="fecha-hora-modal">
                                    <span class="badge-fecha-item">
                                        <i class="fa-regular fa-calendar"></i> ${fechaFormateada}
                                    </span>
                                    <span class="badge-hora-item">
                                        <i class="fa-regular fa-clock"></i> ${t.hora} hs
                                    </span>
                                </div>
                                ${estadoBadge}
                            </div>

                            <div class="seccion-servicios-modal">
                                <span class="label-seccion-servicios">Servicios:</span>
                                <div class="tags-servicios-modal">
                                    ${serviciosChipsHtml}
                                </div>
                            </div>

                            <div class="footer-turno-modal">
                                <div class="col-barbero-modal">
                                    <span class="label-mini"><i class="fa-solid fa-user-tie"></i> Barbero:</span>
                                    <strong class="nombre-barbero">${t.peluquero || 'No asignado'}</strong>
                                </div>
                                <div class="col-total-modal">
                                    <span class="label-mini">Total:</span>
                                    <strong class="monto-total">$${parseFloat(t.total || 0).toLocaleString('es-AR')}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
            }
            html += `</div>`;

            Swal.fire({
                title: `📋 Turnos de ${nombre}`,
                html: html,
                width: '640px',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#0da6f3',
                customClass: { popup: 'mi-alerta alerta-info-turnos-modal' }
            });
        });
    });
}

function iniciarBloqueoHorarios() {
    const btnBloquear = document.querySelector('#btn-bloquear-horario');
    const datosBloqueo = document.querySelector('#datos-bloqueo');
    if(!btnBloquear || !datosBloqueo) return;

    btnBloquear.addEventListener('click', async function() {
        const esAdmin = datosBloqueo.dataset.esAdmin === '1';
        const fechaActual = datosBloqueo.dataset.fecha || '';
        let peluqueros = [];
        try {
            peluqueros = JSON.parse(datosBloqueo.dataset.peluqueros || '[]');
        } catch(e) {
            peluqueros = [];
        }

        // Generar opciones de horas de 15 en 15 minutos entre 10:00 y 21:00
        const horasInicioOptions = [];
        const horasFinOptions = [];
        for(let m = 10 * 60; m <= 21 * 60; m += 15) {
            const h = String(Math.floor(m / 60)).padStart(2, '0');
            const min = String(m % 60).padStart(2, '0');
            const horaStr = `${h}:${min}`;
            if(m < 21 * 60) horasInicioOptions.push(horaStr);
            if(m > 10 * 60) horasFinOptions.push(horaStr);
        }

        let selectPeluqueroHtml = '';
        if(esAdmin) {
            selectPeluqueroHtml = `
                <div class="swal-campo-bloqueo">
                    <label for="swal-peluquero">Profesional a Bloquear:</label>
                    <select id="swal-peluquero" class="swal2-select select-bloqueo-custom">
                        <option value="">✨ Todos los Profesionales</option>
                        ${peluqueros.map(p => `<option value="${p.id}">👤 ${p.nombre}</option>`).join('')}
                    </select>
                </div>
            `;
        }

        const modalHtml = `
            <div class="modal-bloqueo-contenido">
                <p class="modal-bloqueo-subtitulo">Selecciona la franja horaria que no estará disponible para reservas:</p>
                ${selectPeluqueroHtml}
                <div class="swal-campo-bloqueo">
                    <label for="swal-fecha">Fecha:</label>
                    <input type="date" id="swal-fecha" class="swal2-input input-bloqueo-custom" value="${fechaActual}" min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="swal-fila-horas">
                    <div class="swal-campo-bloqueo">
                        <label for="swal-hora-inicio">Hora Inicio:</label>
                        <select id="swal-hora-inicio" class="swal2-select select-bloqueo-custom">
                            ${horasInicioOptions.map(h => `<option value="${h}" ${h === '13:00' ? 'selected' : ''}>${h} hs</option>`).join('')}
                        </select>
                    </div>
                    <div class="swal-campo-bloqueo">
                        <label for="swal-hora-fin">Hora Fin:</label>
                        <select id="swal-hora-fin" class="swal2-select select-bloqueo-custom">
                            ${horasFinOptions.map(h => `<option value="${h}" ${h === '14:00' ? 'selected' : ''}>${h} hs</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="swal-campo-bloqueo">
                    <label for="swal-motivo">Motivo (Opcional):</label>
                    <input type="text" id="swal-motivo" class="swal2-input input-bloqueo-custom" placeholder="Ej: Almuerzo, Trámite, Médico">
                    <div class="chips-sugerencias-motivo">
                        <span class="chip-motivo-sug" role="button">Almuerzo</span>
                        <span class="chip-motivo-sug" role="button">Descanso</span>
                        <span class="chip-motivo-sug" role="button">Médico</span>
                        <span class="chip-motivo-sug" role="button">Trámite personal</span>
                        <span class="chip-motivo-sug" role="button">Capacitación</span>
                    </div>
                </div>
            </div>
        `;

        const { value: formValues } = await Swal.fire({
            title: '🔒 Bloquear Horario',
            html: modalHtml,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-lock"></i> Confirmar Bloqueo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            customClass: { popup: 'mi-alerta modal-bloqueo-swal' },
            didOpen: () => {
                const chips = document.querySelectorAll('.chip-motivo-sug');
                const inputMotivo = document.querySelector('#swal-motivo');
                chips.forEach(chip => {
                    chip.addEventListener('click', function() {
                        if(inputMotivo) {
                            inputMotivo.value = this.textContent.trim();
                            inputMotivo.focus();
                        }
                    });
                });
            },
            preConfirm: () => {
                const fecha = document.querySelector('#swal-fecha')?.value;
                const horaInicio = document.querySelector('#swal-hora-inicio')?.value;
                const horaFin = document.querySelector('#swal-hora-fin')?.value;
                const peluqueroId = document.querySelector('#swal-peluquero')?.value || '';
                const motivo = document.querySelector('#swal-motivo')?.value.trim() || '';

                if(!fecha || !horaInicio || !horaFin) {
                    Swal.showValidationMessage('Por favor completa la fecha y los horarios');
                    return false;
                }

                if(horaInicio >= horaFin) {
                    Swal.showValidationMessage('La hora de fin debe ser posterior a la de inicio');
                    return false;
                }

                return { fecha, horaInicio, horaFin, peluqueroId, motivo };
            }
        });

        if(formValues) {
            const formData = new FormData();
            formData.append('fecha', formValues.fecha);
            formData.append('hora_inicio', formValues.horaInicio);
            formData.append('hora_fin', formValues.horaFin);
            formData.append('peluquero_id', formValues.peluqueroId);
            formData.append('motivo', formValues.motivo);

            try {
                const res = await fetch('/api/bloqueos', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if(data.resultado) {
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Horario Bloqueado!',
                        text: data.mensaje || 'La franja horaria ha sido bloqueada correctamente.',
                        confirmButtonColor: '#0da6f3',
                        customClass: { popup: 'mi-alerta' }
                    });
                    window.location = `?fecha=${formValues.fecha}`;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se pudo bloquear',
                        text: data.mensaje || 'Ocurrió un error al intentar bloquear el horario.',
                        confirmButtonColor: '#0da6f3',
                        customClass: { popup: 'mi-alerta' }
                    });
                }
            } catch(e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'No se pudo comunicar con el servidor.',
                    confirmButtonColor: '#0da6f3',
                    customClass: { popup: 'mi-alerta' }
                });
            }
        }
    });
}

function iniciarDesbloqueoHorarios() {
    const btnsDesbloquear = document.querySelectorAll('.btn-eliminar-bloqueo');
    btnsDesbloquear.forEach(btn => {
        btn.addEventListener('click', async function() {
            const bloqueoId = this.dataset.id;
            if(!bloqueoId) return;

            const confirmacion = await Swal.fire({
                title: '¿Liberar este horario?',
                text: 'El bloqueo se eliminará y el horario volverá a estar disponible para reservas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-lock-open"></i> Sí, Desbloquear',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0da6f3',
                cancelButtonColor: '#64748b',
                customClass: { popup: 'mi-alerta' }
            });

            if(confirmacion.isConfirmed) {
                const formData = new FormData();
                formData.append('id', bloqueoId);

                try {
                    const res = await fetch('/api/bloqueos/eliminar', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if(data.resultado) {
                        await Swal.fire({
                            icon: 'success',
                            title: '¡Horario Desbloqueado!',
                            text: 'El horario ha sido liberado exitosamente.',
                            confirmButtonColor: '#0da6f3',
                            customClass: { popup: 'mi-alerta' }
                        });
                        window.location.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.mensaje || 'No se pudo eliminar el bloqueo.',
                            confirmButtonColor: '#0da6f3',
                            customClass: { popup: 'mi-alerta' }
                        });
                    }
                } catch(e) {
                    console.error(e);
                }
            }
        });
    });
}