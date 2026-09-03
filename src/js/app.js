let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

let todosPeluqueros = [];
let disponibilidadData = {
    horasDisponibles: [],
    horasOcupadas: [],
    peluquerosPorHora: {},
    duracionTotal: 30
};

const turno = {
    id: '',
    nombre: '',
    telefono: '',
    fecha: '',
    hora: '',
    metodo_pago: 'efectivo',
    servicios: [],
    peluquero: null
};

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

async function iniciarApp() {

    mostrarSeccion(); // Muestra y oculta las secciones
    tabs(); // Cambia la sección cuando se presionan los tabs
    botonesPaginador(); // Agrega o quita los botones del paginador
    paginaSiguiente();
    paginaAnterior();

    idCliente();
    nombreCliente(); // Añade el nombre del cliente al objeto de turno
    telefonoCliente(); // Añade el teléfono del cliente al objeto de turno
    seleccionarFecha(); // Añade la fecha del turno y carga horas
    botonModalPeluqueros(); // Escucha clic en botón para abrir modal de peluqueros
    seleccionarMetodoPago(); // Maneja los radio buttons del método de pago
    botonModalCarrito(); // Maneja el botón flotante del carrito de servicios

    actualizarCarritoUI(); // Inicializa el badge del carrito

    const [servicios, peluqueros] = await Promise.all([
        consultarAPI(),
        consultarPeluqueros()
    ]);

    const inputModificar = document.querySelector('#turno_id_modificar');
    if(inputModificar && inputModificar.value) {
        await precargarDatosModificacion(servicios, peluqueros);
    }

    mostrarResumen(); // Muestra el resumen del turno
}

function mostrarSeccion() {

    // Ocultar la seccion que tenga la clase de mostrar
    const seccionAnterior = document.querySelector('.mostrar');
    if(seccionAnterior) {
        seccionAnterior.classList.remove('mostrar');
    }

    // Seleccionar la sección con el paso...
    const pasoSelector = `#paso-${paso}`;
    const seccion = document.querySelector(pasoSelector);
    seccion.classList.add('mostrar');

    // Quitar la clase de 'actual' al tab anterior
    const tabAnterior = document.querySelector('.actual');
    if(tabAnterior) {
        tabAnterior.classList.remove('actual');
    }

    // Resaltar el tab actual
    const tab = document.querySelector(`[data-paso="${paso}"]`);
    tab.classList.add('actual');
}

function tabs() {
    const botones = document.querySelectorAll('.tabs button');

    botones.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            paso = parseInt(e.target.dataset.paso);

            mostrarSeccion();
            botonesPaginador();
        })
    })
}

function botonesPaginador() {
    const paginaAnterior = document.getElementById('anterior');
    const paginaSiguiente = document.getElementById('siguiente');

    if(paso === 1) {
        paginaAnterior.classList.add('ocultar');
        paginaSiguiente.classList.remove('ocultar');
    } else if (paso === 3) {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.add('ocultar');

        mostrarResumen();
    } else {
        paginaAnterior.classList.remove('ocultar');
        paginaSiguiente.classList.remove('ocultar');

        // Si entramos al paso 2 y ya había fecha seleccionada, refrescar disponibilidad con los servicios actuales
        if(turno.fecha) {
            obtenerHorasDisponiblesDia(turno.fecha);
        }
    }

    mostrarSeccion();
}

function paginaAnterior() {
    const paginaAnterior = document.getElementById('anterior');
    paginaAnterior.addEventListener('click', function() {
        if(paso <= pasoInicial) return;
        paso--;
        botonesPaginador();
    });
}

function paginaSiguiente() {
    const paginaSiguiente = document.getElementById('siguiente');
    paginaSiguiente.addEventListener('click', function() {
        if(paso >= pasoFinal) return;
        paso++;
        botonesPaginador();
    });
}

async function consultarAPI() {

    try {
        const url = '/api/servicios';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);
        return servicios;
    } catch (error) {
        console.log(error);
        return [];
    }

}

function mostrarServicios(servicios) {

    servicios.forEach(servicio => {
        const { id, nombre, precio, duracion } = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.textContent = nombre;
        nombreServicio.classList.add('nombre-servicio');

        const duracionServicio = document.createElement('SPAN');
        duracionServicio.innerHTML = `<i class="fa-regular fa-clock"></i> ${duracion || 30} min`;
        duracionServicio.classList.add('badge-duracion-card');

        const precioServicio = document.createElement('P');
        precioServicio.textContent = `$${precio}`;
        precioServicio.classList.add('precio-servicio');

        const infoDiv = document.createElement('DIV');
        infoDiv.classList.add('info-servicio-meta');
        infoDiv.appendChild(duracionServicio);
        infoDiv.appendChild(precioServicio);

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio');
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function() {
            seleccionarServicio(servicio);
        };

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(infoDiv);

        document.querySelector('.listado-servicios').appendChild(servicioDiv);
    });

    // Comprobar si viene un servicio preseleccionado por URL (?servicio=ID)
    const urlParams = new URLSearchParams(window.location.search);
    const servicioIdParam = urlParams.get('servicio');
    if(servicioIdParam) {
        const servicioPreseleccionado = servicios.find(s => String(s.id) === String(servicioIdParam));
        if(servicioPreseleccionado) {
            seleccionarServicio(servicioPreseleccionado);
        }
    }
}

function seleccionarServicio(servicio) {
    const { id } = servicio;
    const { servicios } = turno;

    // Identificar el elemento al que se le da click
    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);

    // Comprobar si un servicio ya fue agregado
    if(servicios.some(agregado => agregado.id === id)) {
        // Eliminarlo
        turno.servicios = servicios.filter(agregado => agregado.id !== id);
        if(divServicio) divServicio.classList.remove('seleccionado');
    } else {
        // Agregarlo
        if(divServicio) divServicio.classList.add('seleccionado');
        turno.servicios = [...servicios, servicio];
    }

    // Resetear hora y peluquero si cambian los servicios seleccionados
    turno.hora = '';
    turno.peluquero = null;
    actualizarUIProfesional();
    actualizarCarritoUI();
}

function actualizarCarritoUI() {
    const badgeContador = document.querySelector('#badge-contador-servicios');
    const badgeTotal = document.querySelector('#badge-total-servicios');
    const btnCarrito = document.querySelector('#btn-carrito');

    const total = turno.servicios.reduce((sum, s) => sum + parseInt(s.precio), 0);
    const cantidad = turno.servicios.length;

    if(badgeContador) badgeContador.textContent = cantidad;
    if(badgeTotal) badgeTotal.textContent = `$${total.toLocaleString('es-AR')}`;

    if(btnCarrito) {
        if(cantidad > 0) {
            btnCarrito.classList.add('con-items');
        } else {
            btnCarrito.classList.remove('con-items');
        }
    }
}

function botonModalCarrito() {
    const btnCarrito = document.querySelector('#btn-carrito');
    if(!btnCarrito) return;

    btnCarrito.addEventListener('click', function() {
        abrirModalCarrito();
    });
}

function abrirModalCarrito() {
    const cantidad = turno.servicios.length;

    if(cantidad === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Tu Carrito está vacío',
            text: 'Selecciona al menos un servicio del catálogo para continuar con tu reserva.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#0da6f3',
            customClass: { popup: 'mi-alerta' }
        });
        return;
    }

    const total = turno.servicios.reduce((sum, s) => sum + parseInt(s.precio), 0);
    const duracionTotal = turno.servicios.reduce((sum, s) => sum + parseInt(s.duracion || 30), 0);

    let htmlServicios = `
        <div class="modal-carrito-contenido">
            <div class="lista-items-carrito">
    `;

    turno.servicios.forEach(s => {
        htmlServicios += `
            <div class="item-carrito-fila" id="item-carrito-${s.id}">
                <div class="item-carrito-info">
                    <strong class="item-carrito-nombre">${s.nombre}</strong>
                    <span class="item-carrito-duracion"><i class="fa-regular fa-clock"></i> ${s.duracion || 30} min</span>
                </div>
                <div class="item-carrito-derecha">
                    <span class="item-carrito-precio">$${parseInt(s.precio).toLocaleString('es-AR')}</span>
                    <button type="button" class="btn-eliminar-item-carrito" data-id="${s.id}" title="Quitar servicio">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        `;
    });

    htmlServicios += `
            </div>
            <div class="resumen-carrito-totales">
                <div class="fila-total-carrito">
                    <span><i class="fa-solid fa-clock"></i> Tiempo Total Estimado:</span>
                    <strong>${formatearDuracion(duracionTotal)}</strong>
                </div>
                <div class="fila-total-carrito total-destacado">
                    <span><i class="fa-solid fa-money-bill-wave"></i> Total a Pagar:</span>
                    <strong>$${total.toLocaleString('es-AR')}</strong>
                </div>
            </div>
        </div>
    `;

    Swal.fire({
        title: '🛒 Servicios Seleccionados',
        html: htmlServicios,
        showCancelButton: true,
        confirmButtonText: 'Continuar a Fecha y Hora <i class="fa-solid fa-arrow-right"></i>',
        cancelButtonText: 'Seguir Agregando',
        confirmButtonColor: '#0da6f3',
        cancelButtonColor: '#475569',
        customClass: {
            popup: 'mi-alerta alerta-carrito-modal'
        },
        didOpen: () => {
            // Escuchar clics en los botones de eliminar dentro del modal
            const botonesEliminar = document.querySelectorAll('.btn-eliminar-item-carrito');
            botonesEliminar.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const servicioId = parseInt(this.dataset.id);
                    const servicioObj = turno.servicios.find(s => parseInt(s.id) === servicioId);
                    if(servicioObj) {
                        seleccionarServicio(servicioObj);
                        // Cerrar y reabrir modal actualizado
                        Swal.close();
                        if(turno.servicios.length > 0) {
                            abrirModalCarrito();
                        }
                    }
                });
            });
        }
    }).then((result) => {
        if(result.isConfirmed) {
            paso = 2;
            mostrarSeccion();
            botonesPaginador();
        }
    });
}

function idCliente() {
    const idInput = document.querySelector('#id');
    turno.id = idInput ? idInput.value : '';
}

function nombreCliente() {
    const inputNombre = document.querySelector('#nombre');
    if(inputNombre) {
        turno.nombre = inputNombre.value;
        inputNombre.addEventListener('input', function(e) {
            turno.nombre = e.target.value.trim();
        });
    }
}

function telefonoCliente() {
    const inputTelefono = document.querySelector('#telefono');
    if(inputTelefono) {
        turno.telefono = inputTelefono.value;
        inputTelefono.addEventListener('input', function(e) {
            turno.telefono = e.target.value.trim();
        });
    }
}

function seleccionarFecha() {
    const inputFecha = document.querySelector('#fecha');
    if(!inputFecha) return;

    inputFecha.addEventListener('input', function(e) {

        // Obtener el dia y convertirlo a numero
        const dia = new Date(e.target.value).getUTCDay();

        // Si el dia es domingo, mostrar una alerta
        if(dia === 0) {
            e.target.value = '';
            turno.fecha = '';
            turno.hora = '';
            turno.peluquero = null;
            document.querySelector('#horas').innerHTML = '';
            document.querySelector('.etiqueta-horas').innerHTML = '';
            const campoProf = document.querySelector('#campo-profesional');
            if(campoProf) campoProf.style.display = 'none';
            actualizarUIProfesional();
            mostrarAlerta('Día Domingo no disponible', 'error', '.formulario');
        } else {
            turno.fecha = e.target.value;
            turno.hora = '';
            turno.peluquero = null;
            const campoProf = document.querySelector('#campo-profesional');
            if(campoProf) campoProf.style.display = 'none';
            actualizarUIProfesional();

            // Cargar horas disponibles filtrando por servicios y horarios
            obtenerHorasDisponiblesDia(turno.fecha);
        }

    });
}

// Carga la lista completa de peluqueros
async function consultarPeluqueros() {
    try {
        const url = '/api/peluqueros';
        const resultado = await fetch(url);
        todosPeluqueros = await resultado.json();
        return todosPeluqueros;
    } catch (error) {
        console.log(error);
        return [];
    }
}

// Obtener disponibilidad calculada en base a Servicios + Duraciones + Fecha + Horarios
async function obtenerHorasDisponiblesDia(fecha) {
    try {
        const serviciosIds = turno.servicios.map(s => s.id).join(',');
        let url = `/api/disponibilidad?fecha=${fecha}&servicios=${serviciosIds}`;
        const inputModificar = document.querySelector('#turno_id_modificar');
        if(inputModificar && inputModificar.value) {
            url += `&turno_id=${inputModificar.value}`;
        }
        const resultado = await fetch(url);
        disponibilidadData = await resultado.json();

        mostrarHorasGrid(disponibilidadData);
    } catch (error) {
        console.log(error);
    }
}

function mostrarHorasGrid(data) {

    const etiquetaHoras = document.querySelector('.etiqueta-horas');
    etiquetaHoras.innerHTML = '';

    // Calcular la duración real sumando directamente de turno.servicios para sincronía perfecta
    const duracionReal = turno.servicios.reduce((sum, s) => sum + parseInt(s.duracion || 30), 0) || (data.duracionTotal || 30);
    const duracionTexto = formatearDuracion(duracionReal);

    const etiqueta = document.createElement('P');
    etiqueta.innerHTML = `Horas disponibles <em>(Duración estimada: ${duracionTexto})</em>:`;
    etiqueta.classList.add('etiqueta-horas');
    etiquetaHoras.appendChild(etiqueta);

    const contenedorHoras = document.querySelector('#horas');
    contenedorHoras.innerHTML = '';

    // Obtener horas generadas dinámicamente desde la API (intervalos de 15 minutos)
    const horasEstablecimiento = data.todasLasHoras || Object.keys(data.peluquerosPorHora || {}) || [];

    const horasOcupadas = data.horasOcupadas || [];

    const ahora = new Date();
    const pad = n => String(n).padStart(2, '0');
    const hoyStr = `${ahora.getFullYear()}-${pad(ahora.getMonth() + 1)}-${pad(ahora.getDate())}`;
    const esHoy = (turno.fecha === hoyStr);
    const ahoraMin = (ahora.getHours() * 60) + ahora.getMinutes();

    horasEstablecimiento.forEach(hora => {
        const [h, m] = hora.split(':').map(Number);
        const slotMin = (h * 60) + (m || 0);
        const esPasada = esHoy && (slotMin <= ahoraMin);

        const botonHora = document.createElement('BUTTON');
        botonHora.type = 'button';
        botonHora.textContent = hora;
        botonHora.classList.add('hora-boton');

        const esHoraActualTurno = (turno.hora && turno.hora === hora);

        if ((horasOcupadas.includes(hora) || esPasada) && !esHoraActualTurno) {
            botonHora.disabled = true;
            botonHora.classList.add('ocupada');
        } else {
            if (esHoraActualTurno) {
                botonHora.classList.add('seleccionada');
            }

            botonHora.onclick = function() {
                const botonSeleccionadoPrevio = document.querySelector('.hora-boton.seleccionada');
                if (botonSeleccionadoPrevio) {
                    botonSeleccionadoPrevio.classList.remove('seleccionada');
                }

                botonHora.classList.add('seleccionada');
                turno.hora = hora;
                document.querySelector('#hora').value = hora;

                // Resetear peluquero anterior al cambiar de hora
                turno.peluquero = null;
                actualizarUIProfesional();

                // Mostrar la sección para elegir profesional
                const campoProfesional = document.querySelector('#campo-profesional');
                if(campoProfesional) {
                    campoProfesional.style.display = 'block';
                }
            };
        }

        contenedorHoras.appendChild(botonHora);
    });
}

function botonModalPeluqueros() {
    const btn = document.querySelector('#btn-seleccionar-peluquero');
    if(!btn) return;

    btn.addEventListener('click', async function() {
        if(!turno.fecha || !turno.hora) {
            mostrarAlerta('Por favor, selecciona primero una fecha y hora', 'error', '.formulario');
            return;
        }

        // Obtener peluqueros disponibles para la hora seleccionada desde la respuesta de disponibilidad
        const peluquerosDisponibles = disponibilidadData.peluquerosPorHora[turno.hora] || [];

        if(peluquerosDisponibles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin profesionales disponibles',
                text: 'No hay profesionales disponibles para el tiempo total requerido en este horario. Por favor elige otra hora.',
                customClass: { popup: 'mi-alerta' }
            });
            return;
        }

        const valorActual = (turno.peluquero && !turno.peluquero.esPrimero) ? String(turno.peluquero.id) : 'primero_disponible';

        // Construir opciones con checkboxes seleccionables
        let htmlPeluqueros = `
            <p class="modal-subtitulo-peluqueros">Selecciona quién deseas que te atienda el <strong>${turno.fecha}</strong> a las <strong>${turno.hora} hs</strong>:</p>
            <div class="listado-peluqueros-modal">
                <label class="item-peluquero-check ${valorActual === 'primero_disponible' ? 'activo' : ''}">
                    <input type="radio" name="modal_peluquero" value="primero_disponible" ${valorActual === 'primero_disponible' ? 'checked' : ''}>
                    <div class="check-box-custom"><i class="fa-solid fa-check"></i></div>
                    <div class="info-peluquero-check">
                        <span class="icono-peluquero">✨</span>
                        <div class="datos-peluquero">
                            <strong>Primero disponible</strong>
                            <small>Cualquier barbero del equipo</small>
                        </div>
                    </div>
                </label>
        `;

        peluquerosDisponibles.forEach(p => {
            const estaActivo = (valorActual === String(p.id));
            htmlPeluqueros += `
                <label class="item-peluquero-check ${estaActivo ? 'activo' : ''}">
                    <input type="radio" name="modal_peluquero" value="${p.id}" ${estaActivo ? 'checked' : ''}>
                    <div class="check-box-custom"><i class="fa-solid fa-check"></i></div>
                    <div class="info-peluquero-check">
                        <span class="icono-peluquero">👤</span>
                        <div class="datos-peluquero">
                            <strong>${p.nombre} ${p.apellido}</strong>
                            <small>Barbero profesional</small>
                        </div>
                    </div>
                </label>
            `;
        });

        htmlPeluqueros += `</div>`;

        const { value: seleccion } = await Swal.fire({
            title: 'Elige tu Barbero',
            html: htmlPeluqueros,
            showCancelButton: true,
            confirmButtonText: 'Confirmar Barbero',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0da6f3',
            cancelButtonColor: '#64748b',
            customClass: {
                popup: 'mi-alerta'
            },
            didOpen: () => {
                const items = document.querySelectorAll('.item-peluquero-check');
                items.forEach(item => {
                    item.addEventListener('click', function() {
                        items.forEach(i => i.classList.remove('activo'));
                        this.classList.add('activo');
                        const radio = this.querySelector('input[type="radio"]');
                        if (radio) radio.checked = true;
                    });
                });
            },
            preConfirm: () => {
                const seleccionado = document.querySelector('input[name="modal_peluquero"]:checked');
                if (!seleccionado) {
                    Swal.showValidationMessage('Por favor, selecciona un barbero');
                    return false;
                }
                return seleccionado.value;
            }
        });

        if(seleccion) {
            if(seleccion === 'primero_disponible') {
                const pLibre = peluquerosDisponibles[0];
                turno.peluquero = {
                    id: pLibre.id,
                    nombre: 'Primero Disponible',
                    apellido: `(${pLibre.nombre} ${pLibre.apellido})`,
                    telefono: pLibre.telefono,
                    esPrimero: true
                };
            } else {
                const pElegido = peluquerosDisponibles.find(p => String(p.id) === String(seleccion));
                if(pElegido) {
                    turno.peluquero = {
                        id: pElegido.id,
                        nombre: pElegido.nombre,
                        apellido: pElegido.apellido,
                        telefono: pElegido.telefono,
                        esPrimero: false
                    };
                }
            }
            actualizarUIProfesional();
        }
    });
}

function actualizarUIProfesional() {
    const btn = document.querySelector('#btn-seleccionar-peluquero');
    const badgeInfo = document.querySelector('#peluquero-seleccionado-info');

    if(!badgeInfo || !btn) return;

    if(turno.peluquero) {
        badgeInfo.style.display = 'block';
        if(turno.peluquero.esPrimero) {
            badgeInfo.innerHTML = `
                <div class="peluquero-badge-card">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <div>
                        <strong>Asignación Automática:</strong>
                        <span>${turno.peluquero.apellido}</span>
                    </div>
                </div>
            `;
        } else {
            badgeInfo.innerHTML = `
                <div class="peluquero-badge-card">
                    <i class="fa-solid fa-user-check"></i>
                    <div>
                        <strong>Profesional:</strong>
                        <span>${turno.peluquero.nombre} ${turno.peluquero.apellido}</span>
                    </div>
                </div>
            `;
        }
        btn.innerHTML = `<i class="fa-solid fa-arrows-rotate"></i> Cambiar Profesional`;
    } else {
        badgeInfo.style.display = 'none';
        badgeInfo.innerHTML = '';
        btn.innerHTML = `<i class="fa-solid fa-scissors"></i> Ver Profesionales Disponibles`;
    }
}

function seleccionarMetodoPago() {
    const radios = document.querySelectorAll('input[name="metodo_pago"]');
    
    // Obtener valor inicial seleccionado
    const seleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    if(seleccionado) {
        turno.metodo_pago = seleccionado.value;
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function(e) {
            turno.metodo_pago = e.target.value;
        });
    });
}

function mostrarAlerta(mensaje, tipo, elemento, desaparece = true) {

    // Previene que se generen más de una alerta a la vez
    const alertaPrevia = document.querySelector('.alerta');

    if(alertaPrevia) {
        alertaPrevia.remove();
    }

    // Crear la alerta
    const alerta = document.createElement('div');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta');
    alerta.classList.add(tipo);

    const referencia = document.querySelector(elemento);
    referencia.appendChild(alerta);

    // Eliminar la alerta despues de 3 segundos
    if(desaparece) {
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
}

function calcularHoraFin(horaInicio, duracionMinutos) {
    const [h, m] = horaInicio.split(':').map(Number);
    const totalMin = (h * 60) + m + duracionMinutos;
    const finH = String(Math.floor(totalMin / 60)).padStart(2, '0');
    const finM = String(totalMin % 60).padStart(2, '0');
    return `${finH}:${finM}`;
}

function formatearDuracion(minutos) {
    if (minutos < 60) return `${minutos} min`;
    const horas = Math.floor(minutos / 60);
    const minRestantes = minutos % 60;
    return minRestantes > 0 ? `${horas}h ${minRestantes}m` : `${horas}h`;
}

function mostrarResumen() {
    const resumen = document.querySelector('.contenido-resumen');

    // Limpiar el contenido de Resumen
    while(resumen.firstChild) {
        resumen.removeChild(resumen.firstChild);
    }

    const { nombre, telefono, fecha, hora, servicios, peluquero, metodo_pago } = turno;

    if(!nombre || !telefono || !fecha || !hora || !peluquero || !metodo_pago || servicios.length === 0) {
        mostrarAlerta('Faltan datos de Nombre, Teléfono, Servicios, Fecha, Hora, Profesional o Método de Pago', 'error', '.contenido-resumen', false);
        return;
    }

    // Calcular duración total y hora de finalización estimada
    const duracionTotalMin = servicios.reduce((sum, s) => sum + parseInt(s.duracion || 30), 0);
    const horaFin = calcularHoraFin(hora, duracionTotalMin);

    // Heading para Servicios en Resumen
    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    // Iterando y mostrando los servicios seleccionados
    servicios.forEach(servicio => {
        const {nombre, precio, duracion} = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.textContent = nombre;

        const metaServicio = document.createElement('DIV');
        metaServicio.classList.add('meta-servicio-resumen');
        metaServicio.innerHTML = `
            <span class="duracion-tag"><i class="fa-regular fa-clock"></i> ${duracion || 30} min</span>
            <span class="precio-tag">$${precio}</span>
        `;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('contenedor-servicio');
        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(metaServicio);

        resumen.appendChild(servicioDiv);
    });

    // Heading para Datos del Turno
    const headingDatos = document.createElement('H3');
    headingDatos.textContent = 'Datos del Turno';
    resumen.appendChild(headingDatos);

    const nombreCliente = document.createElement('P');
    nombreCliente.innerHTML = `<span>Nombre: </span> ${nombre}`;

    const telefonoCliente = document.createElement('P');
    telefonoCliente.innerHTML = `<span>Teléfono: </span> ${telefono}`;

    const peluqueroTurno = document.createElement('P');
    const nombreProf = peluquero.esPrimero ? `Primero disponible ${peluquero.apellido}` : `${peluquero.nombre} ${peluquero.apellido}`;
    peluqueroTurno.innerHTML = `<span>Profesional: </span> ${nombreProf}`;

    // Formatear la fecha en español
    const [year, month, day] = fecha.split('-');
    const fechaObjt = new Date(Number(year), Number(month) - 1, Number(day));
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const fechaFormateada = fechaObjt.toLocaleDateString('es-AR', opciones);

    const fechaTurno = document.createElement('P');
    fechaTurno.innerHTML = `<span>Fecha: </span> ${fechaFormateada}`;

    const horaTurno = document.createElement('P');
    horaTurno.innerHTML = `<span>Horario: </span> ${hora} a ${horaFin} hs (${formatearDuracion(duracionTotalMin)})`;

    const metodoPagoTurno = document.createElement('P');
    const metodoFormateado = metodo_pago.charAt(0).toUpperCase() + metodo_pago.slice(1);
    metodoPagoTurno.innerHTML = `<span>Método de Pago: </span> ${metodoFormateado}`;

    // Botón para crear un turno o guardar cambios
    const botonAccion = document.createElement('button');
    botonAccion.classList.add('boton');

    const inputModificar = document.querySelector('#turno_id_modificar');
    if(inputModificar && inputModificar.value) {
        botonAccion.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Cambios';
        botonAccion.onclick = guardarCambiosTurno;
    } else {
        botonAccion.textContent = 'Reservar Turno';
        botonAccion.onclick = reservarTurno;
    }

    resumen.appendChild(nombreCliente);
    resumen.appendChild(telefonoCliente);
    resumen.appendChild(peluqueroTurno);
    resumen.appendChild(fechaTurno);
    resumen.appendChild(horaTurno);
    resumen.appendChild(metodoPagoTurno);

    resumen.appendChild(botonAccion);
}

async function reservarTurno() {
    
    const {id, nombre, telefono, fecha, hora, peluquero, servicios, metodo_pago} = turno;
    
    // Arreglo con los IDs de los servicios
    const idServicios = servicios.map(servicio => servicio.id);
    
    const datos = new FormData();
    datos.append('usuario_id', id);
    datos.append('nombre', nombre);
    datos.append('telefono', telefono);
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('peluquero_id', peluquero.id);
    datos.append('metodo_pago', metodo_pago);
    datos.append('servicios', idServicios);

    try {
        // Petición a la API
        const url = '/api/turnos';

        const respuesta = await fetch(url, {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        if(resultado.resultado) {
            // Calcular duración total y hora fin estimada
            const duracionTotalMin = servicios.reduce((sum, s) => sum + parseInt(s.duracion || 30), 0);
            const horaFin = calcularHoraFin(hora, duracionTotalMin);
            const nombresServicios = servicios.map(s => s.nombre).join(', ');
            const total = servicios.reduce((sum, s) => sum + parseInt(s.precio), 0);

            // Formatear fecha legible
            const [anio, mes, dia] = fecha.split('-');
            const fechaObj = new Date(Number(anio), Number(mes) - 1, Number(dia));
            const fechaLegible = fechaObj.toLocaleDateString('es-AR', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            const metodoFormateado = metodo_pago.charAt(0).toUpperCase() + metodo_pago.slice(1);
            const nombreProf = peluquero.esPrimero ? `Primero disponible ${peluquero.apellido}` : `${peluquero.nombre} ${peluquero.apellido}`;
            const mensaje = `Hola! Me gustaría reservar un turno.\nNombre: ${nombre}\nProfesional: ${nombreProf}\nServicios: ${nombresServicios}\nFecha: ${fechaLegible}\nHorario: ${hora} a ${horaFin}hs (${formatearDuracion(duracionTotalMin)})\nMétodo de Pago: ${metodoFormateado}\nTotal a pagar: $${total}`;

            const telefonoPeluquero = peluquero.telefono ? peluquero.telefono.replace(/\D/g, '') : '';
            const urlWhatsApp = `https://wa.me/549${telefonoPeluquero}?text=${encodeURIComponent(mensaje)}`;

            Swal.fire({
                icon: "warning",
                title: "Confirmación de Turno",
                text: "Presiona OK para confirmar el turno en WhatsApp.",
                button: "OK",
                customClass: {
                    popup: 'mi-alerta'
                }
            }).then(() => {
                if(telefonoPeluquero) {
                    window.open(urlWhatsApp, '_blank');
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });
        }
    } catch (error) {
        console.log(error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Hubo un error al reservar el turno.",
            button: "OK",
            customClass: {
                popup: 'mi-alerta'
            }
        });
    }
}

async function precargarDatosModificacion(servicios, peluqueros) {
    const inputServicios = document.querySelector('#turno_modificar_servicios');
    const idsServicios = inputServicios ? JSON.parse(inputServicios.value || '[]') : [];

    if(Array.isArray(idsServicios) && idsServicios.length > 0) {
        servicios.forEach(servicio => {
            if(idsServicios.includes(parseInt(servicio.id))) {
                turno.servicios.push(servicio);
                const divServicio = document.querySelector(`[data-id-servicio="${servicio.id}"]`);
                if(divServicio) divServicio.classList.add('seleccionado');
            }
        });
        actualizarCarritoUI();
    }

    const inputFecha = document.querySelector('#fecha');
    const inputHora = document.querySelector('#hora');
    const fechaPrev = document.querySelector('#turno_modificar_fecha')?.value || '';
    const horaPrev = document.querySelector('#turno_modificar_hora')?.value || '';
    const peluqueroIdPrev = document.querySelector('#turno_modificar_peluquero_id')?.value || '';
    const metodoPagoPrev = document.querySelector('#turno_modificar_metodo_pago')?.value || 'efectivo';

    if(metodoPagoPrev) {
        turno.metodo_pago = metodoPagoPrev;
        const radioPago = document.querySelector(`input[name="metodo_pago"][value="${metodoPagoPrev}"]`);
        if(radioPago) radioPago.checked = true;
    }

    if(fechaPrev) {
        turno.fecha = fechaPrev;
        if(inputFecha) inputFecha.value = fechaPrev;

        if(horaPrev) {
            turno.hora = horaPrev;
            if(inputHora) inputHora.value = horaPrev;
        }

        await obtenerHorasDisponiblesDia(fechaPrev);

        if(horaPrev) {
            const botones = document.querySelectorAll('.hora-boton');
            botones.forEach(b => {
                if(b.textContent.trim() === horaPrev) {
                    b.classList.add('seleccionada');
                    b.disabled = false;
                    b.classList.remove('ocupada');
                }
            });

            if(peluqueroIdPrev && peluqueros && peluqueros.length > 0) {
                const peluqueroEncontrado = peluqueros.find(p => String(p.id) === String(peluqueroIdPrev));
                if(peluqueroEncontrado) {
                    turno.peluquero = {
                        id: peluqueroEncontrado.id,
                        nombre: peluqueroEncontrado.nombre,
                        apellido: peluqueroEncontrado.apellido,
                        telefono: peluqueroEncontrado.telefono,
                        esPrimero: false
                    };
                    const campoProf = document.querySelector('#campo-profesional');
                    if(campoProf) campoProf.style.display = 'block';
                    actualizarUIProfesional();
                }
            }
        }
    }
}

async function guardarCambiosTurno() {
    const inputModificar = document.querySelector('#turno_id_modificar');
    const turnoId = inputModificar ? inputModificar.value : null;
    if(!turnoId) return;

    const { id, nombre, telefono, fecha, hora, peluquero, servicios, metodo_pago } = turno;

    if(!nombre || !telefono || !fecha || !hora || !peluquero || !metodo_pago || servicios.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Datos Incompletos',
            text: 'Por favor, completa todos los campos (servicios, fecha, horario y barbero) antes de guardar.',
            confirmButtonColor: '#0da6f3',
            customClass: { popup: 'mi-alerta' }
        });
        return;
    }

    const confirmacion = await Swal.fire({
        title: '¿Guardar Cambios?',
        text: `Se actualizará el turno #${turnoId} para el ${fecha} a las ${hora} hs.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, Guardar Cambios',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0da6f3',
        cancelButtonColor: '#64748b',
        customClass: { popup: 'mi-alerta' }
    });

    if(!confirmacion.isConfirmed) return;

    const idServicios = servicios.map(servicio => servicio.id);

    const datos = new FormData();
    datos.append('id', turnoId);
    datos.append('usuario_id', id);
    datos.append('nombre', nombre);
    datos.append('telefono', telefono);
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('peluquero_id', peluquero.id);
    datos.append('metodo_pago', metodo_pago);
    datos.append('servicios', idServicios.join(','));

    try {
        const respuesta = await fetch('/api/turnos/actualizar', {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();

        if(resultado.resultado) {
            const esCliente = document.querySelector('#es_cliente')?.value === '1';
            const redirectTarget = resultado.redirect || document.querySelector('#redirect_url')?.value || '/';

            // Calcular duración total y hora fin estimada
            const duracionTotalMin = servicios.reduce((sum, s) => sum + parseInt(s.duracion || 30), 0);
            const horaFin = calcularHoraFin(hora, duracionTotalMin);
            const nombresServicios = servicios.map(s => s.nombre).join(', ');
            const total = servicios.reduce((sum, s) => sum + parseInt(s.precio), 0);

            // Formatear fecha legible
            const [anio, mes, dia] = fecha.split('-');
            const fechaObj = new Date(Number(anio), Number(mes) - 1, Number(dia));
            const fechaLegible = fechaObj.toLocaleDateString('es-AR', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            const metodoFormateado = metodo_pago.charAt(0).toUpperCase() + metodo_pago.slice(1);
            const nombreProf = peluquero.esPrimero ? `Primero disponible ${peluquero.apellido}` : `${peluquero.nombre} ${peluquero.apellido}`;
            const mensaje = `Hola! Modifiqué mi turno.\n*Modificación de Turno #${turnoId}*\nNombre: ${nombre}\nProfesional: ${nombreProf}\nServicios: ${nombresServicios}\nNueva Fecha: ${fechaLegible}\nNuevo Horario: ${hora} a ${horaFin}hs (${formatearDuracion(duracionTotalMin)})\nMétodo de Pago: ${metodoFormateado}\nTotal a pagar: $${total}`;

            const rawTel = peluquero.telefono ? peluquero.telefono.replace(/\D/g, '') : '';
            let telefonoPeluquero = rawTel;
            if(rawTel) {
                if(rawTel.startsWith('549')) {
                    telefonoPeluquero = rawTel;
                } else if(rawTel.startsWith('54')) {
                    telefonoPeluquero = '549' + rawTel.slice(2);
                } else {
                    telefonoPeluquero = '549' + rawTel;
                }
            }

            const urlWhatsApp = telefonoPeluquero ? `https://wa.me/${telefonoPeluquero}?text=${encodeURIComponent(mensaje)}` : '';

            if(esCliente && urlWhatsApp) {
                Swal.fire({
                    icon: "warning",
                    title: "¡Turno Modificado!",
                    text: "Presiona OK para notificar la modificación del turno al barbero por WhatsApp.",
                    confirmButtonText: '<i class="fa-brands fa-whatsapp"></i> Enviar a WhatsApp',
                    confirmButtonColor: '#25D366',
                    customClass: {
                        popup: 'mi-alerta'
                    }
                }).then(() => {
                    window.open(urlWhatsApp, '_blank');
                    setTimeout(() => {
                        window.location.href = redirectTarget;
                    }, 1000);
                });
            } else {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Turno Actualizado!',
                    text: resultado.mensaje || 'Los cambios se han guardado exitosamente.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#0da6f3',
                    customClass: { popup: 'mi-alerta' }
                });
                window.location.href = redirectTarget;
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo actualizar',
                text: resultado.mensaje || 'Ocurrió un error al intentar modificar el turno.',
                confirmButtonColor: '#0da6f3',
                customClass: { popup: 'mi-alerta' }
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error de Red',
            text: 'No se pudo conectar con el servidor para guardar las modificaciones.',
            confirmButtonColor: '#0da6f3',
            customClass: { popup: 'mi-alerta' }
        });
    }
}