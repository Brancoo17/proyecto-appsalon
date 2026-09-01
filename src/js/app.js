let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

let todosPeluqueros = [];
let turnosDelDia = [];

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
        const { id, nombre, precio } = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.textContent = nombre;
        nombreServicio.classList.add('nombre-servicio');

        const precioServicio = document.createElement('P');
        precioServicio.textContent = `$${precio}`;
        precioServicio.classList.add('precio-servicio');

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('servicio');
        servicioDiv.dataset.idServicio = id;
        servicioDiv.onclick = function() {
            seleccionarServicio(servicio);
        };

        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

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
        divServicio.classList.remove('seleccionado');
    } else {
        // Agregarlo
        divServicio.classList.add('seleccionado');
        turno.servicios = [...servicios, servicio];
    }
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

            // Cargar horas disponibles para el día seleccionado
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

// Obtener turnos ocupados de la fecha y pintar las horas disponibles
async function obtenerHorasDisponiblesDia(fecha) {
    try {
        const url = `http://localhost:3000/api/turnos?fecha=${fecha}`;
        const resultado = await fetch(url);
        turnosDelDia = await resultado.json();

        mostrarHorasGrid(turnosDelDia);
    } catch (error) {
        console.log(error);
    }
}

function mostrarHorasGrid(turnosOcupados) {

    // Obtener el div de la etiqueta de horas
    const etiquetaHoras = document.querySelector('.etiqueta-horas');
    etiquetaHoras.innerHTML = '';

    // Crear la etiqueta de horas
    const etiqueta = document.createElement('P');
    etiqueta.textContent = 'Horas disponibles:';
    etiqueta.classList.add('etiqueta-horas');
    etiquetaHoras.appendChild(etiqueta);

    // Agregar las horas disponibles al contenedor
    const contenedorHoras = document.querySelector('#horas');
    contenedorHoras.innerHTML = '';

    // Listado de todas las horas posibles
    const horasEstablecimiento = [
        "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", 
        "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", 
        "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", 
        "19:00", "19:30", "20:00", "20:30", "21:00"
    ];

    const totalPeluqueros = todosPeluqueros.length || 1;

    horasEstablecimiento.forEach(hora => {
        const botonHora = document.createElement('BUTTON');
        botonHora.type = 'button';
        botonHora.textContent = hora;
        botonHora.classList.add('hora-boton');

        // Turnos ocupados en esta hora específica
        const ocupadosEnHora = turnosOcupados.filter(t => t.hora === hora);

        // Si todos los peluqueros están ocupados a esta hora, deshabilitar
        if (ocupadosEnHora.length >= totalPeluqueros && totalPeluqueros > 0) {
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

                // Resetear peluquero anterior para obligar a seleccionar uno válido para esta nueva hora
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

        // Obtener IDs de peluqueros ocupados a esa fecha y hora
        const bookedPeluqueroIds = turnosDelDia
            .filter(t => t.hora === turno.hora)
            .map(t => String(t.peluquero_id));

        // Filtrar peluqueros disponibles
        const peluquerosDisponibles = todosPeluqueros.filter(p => !bookedPeluqueroIds.includes(String(p.id)));

        if(peluquerosDisponibles.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin disponibilidad',
                text: 'No hay profesionales disponibles para este horario. Por favor elige otra hora.',
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
                // Asignar el primer peluquero libre
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

    // Heading para Servicios en Resumen
    const headingServicios = document.createElement('H3');
    headingServicios.textContent = 'Resumen de Servicios';
    resumen.appendChild(headingServicios);

    // Iterando y mostrando los servicios seleccionados
    servicios.forEach(servicio => {
        const {nombre, precio} = servicio;

        const nombreServicio = document.createElement('P');
        nombreServicio.textContent = nombre;

        const precioServicio = document.createElement('P');
        precioServicio.innerHTML = `<span>Precio: </span> $${precio}`;

        const servicioDiv = document.createElement('DIV');
        servicioDiv.classList.add('contenedor-servicio');
        servicioDiv.appendChild(nombreServicio);
        servicioDiv.appendChild(precioServicio);

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

    // Mostrar peluquero en el resumen
    const peluqueroTurno = document.createElement('P');
    const nombreProf = peluquero.esPrimero ? `Primero disponible ${peluquero.apellido}` : `${peluquero.nombre} ${peluquero.apellido}`;
    peluqueroTurno.innerHTML = `<span>Profesional: </span> ${nombreProf}`;

    // Formatear la fecha en español
    const [year, month, day] = fecha.split('-');

    const fechaObjt = new Date(
        Number(year),
        Number(month) - 1,
        Number(day)
    );

    const opciones = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    const fechaFormateada = fechaObjt.toLocaleDateString('es-AR', opciones);

    const fechaTurno = document.createElement('P');
    fechaTurno.innerHTML = `<span>Fecha: </span> ${fechaFormateada}`;

    const horaTurno = document.createElement('P');
    horaTurno.innerHTML = `<span>Hora: </span> ${hora}hs`;

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
            // Construir mensaje de WhatsApp
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
            const mensaje = `Hola! Me gustaría reservar un turno.\nNombre: ${nombre}\nProfesional: ${nombreProf}\nServicios: ${nombresServicios}\nFecha: ${fechaLegible} a las ${hora}hs\nMétodo de Pago: ${metodoFormateado}\nTotal a pagar: $${total}`;

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