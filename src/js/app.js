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

function iniciarApp() {

    mostrarSeccion(); // Muestra y oculta las secciones
    tabs(); // Cambia la sección cuando se presionan los tabs
    botonesPaginador(); // Agrega o quita los botones del paginador
    paginaSiguiente();
    paginaAnterior();

    consultarAPI(); // Consulta la API de servicios
    consultarPeluqueros(); // Carga todos los peluqueros

    idCliente();
    nombreCliente(); // Añade el nombre del cliente al objeto de turno
    telefonoCliente(); // Añade el teléfono del cliente al objeto de turno
    seleccionarFecha(); // Añade la fecha del turno y carga horas
    botonModalPeluqueros(); // Escucha clic en botón para abrir modal de peluqueros
    seleccionarMetodoPago(); // Maneja los radio buttons del método de pago
    botonModalCarrito(); // Maneja el botón flotante del carrito de servicios

    actualizarCarritoUI(); // Inicializa el badge del carrito
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
        const url = 'http://localhost:3000/api/servicios';
        const resultado = await fetch(url);
        const servicios = await resultado.json();
        mostrarServicios(servicios);
        
    } catch (error) {
        console.log(error);
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
        const url = 'http://localhost:3000/api/peluqueros';
        const resultado = await fetch(url);
        todosPeluqueros = await resultado.json();
    } catch (error) {
        console.log(error);
    }
}

// Obtener disponibilidad calculada en base a Servicios + Duraciones + Fecha + Horarios
async function obtenerHorasDisponiblesDia(fecha) {
    try {
        const serviciosIds = turno.servicios.map(s => s.id).join(',');
        const url = `http://localhost:3000/api/disponibilidad?fecha=${fecha}&servicios=${serviciosIds}`;
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

    const horasEstablecimiento = [
        "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", 
        "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", 
        "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", 
        "19:00", "19:30", "20:00", "20:30", "21:00"
    ];

    const horasOcupadas = data.horasOcupadas || [];

    horasEstablecimiento.forEach(hora => {
        const botonHora = document.createElement('BUTTON');
        botonHora.type = 'button';
        botonHora.textContent = hora;
        botonHora.classList.add('hora-boton');

        if (horasOcupadas.includes(hora)) {
            botonHora.disabled = true;
            botonHora.classList.add('ocupada');
        } else {
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

        // Construir opciones para el SweetAlert2
        const inputOptions = {
            'primero_disponible': '✨ Primero disponible (Cualquier profesional)'
        };

        peluquerosDisponibles.forEach(p => {
            inputOptions[p.id] = `👤 ${p.nombre} ${p.apellido}`;
        });

        const valorActual = (turno.peluquero && !turno.peluquero.esPrimero) ? turno.peluquero.id : 'primero_disponible';

        const { value: seleccion } = await Swal.fire({
            title: 'Elige tu Profesional',
            text: `Profesionales disponibles para el ${turno.fecha} a las ${turno.hora}hs:`,
            input: 'select',
            inputOptions: inputOptions,
            inputValue: valorActual,
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0da6f3',
            cancelButtonColor: '#64748b',
            customClass: {
                popup: 'mi-alerta'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes seleccionar una opción';
                }
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

    // Botón para crear un turno
    const botonReservar = document.createElement('button');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Reservar Turno';
    botonReservar.onclick = reservarTurno;

    resumen.appendChild(nombreCliente);
    resumen.appendChild(telefonoCliente);
    resumen.appendChild(peluqueroTurno);
    resumen.appendChild(fechaTurno);
    resumen.appendChild(horaTurno);
    resumen.appendChild(metodoPagoTurno);

    resumen.appendChild(botonReservar);
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
        const url = 'http://localhost:3000/api/turnos';

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