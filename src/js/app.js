let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const turno = {
    id: '',
    nombre: '',
    telefono: '',
    fecha: '',
    hora: '',
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

    consultarAPI(); // Consulta la API en el backend de PHP
    consultarPeluqueros(); // Carga los peluqueros en el select

    idCliente();
    nombreCliente(); // Añade el nombre del cliente al objeto de turno
    telefonoCliente(); // Añade el teléfono del cliente al objeto de turno
    seleccionarFecha(); // Añade la fecha del turno en el objeto
    seleccionarPeluquero(); // Escucha cambios en el select
    seleccionarHora(); // Añade la hora del turno en el objeto

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
    const {id} = servicio;
    const {servicios} = turno;

    // Identificar el elemento al que se le da click
    const divServicio = document.querySelector(`[data-id-servicio="${id}"]`);

    // Comprobar si un servicio ya fue agregado
    if(servicios.some(agregado => agregado.id === id)) {
        // Eliminar el servicio
        turno.servicios = servicios.filter(agregado => agregado.id !== id);
        divServicio.classList.remove('seleccionado');
    } else {
        // Agregar el servicio
        divServicio.classList.add('seleccionado');
        turno.servicios = [...servicios, servicio];
    }
}

function idCliente() {
    const inputId = document.querySelector('#id');
    turno.id = inputId ? inputId.value : '';
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
    inputFecha.addEventListener('input', function(e) {

        // Obtener el dia y convertirlo a numero
        const dia = new Date(e.target.value).getUTCDay();

        // Si el dia es domingo, mostrar una alerta
        if(dia === 0) {
            e.target.value = '';
            mostrarAlerta('Día Domingo no disponible', 'error', '.formulario');
        } else {
            turno.fecha = e.target.value;

            // Mostrar el contenedor del peluquero
            const campoPeluquero = document.querySelector('#campo-peluquero');
            campoPeluquero.style.display = 'block';
            // Resetear el peluquero y horas anteriores para forzar nueva selección
            turno.peluquero = null;
            turno.hora = '';
            document.querySelector('#peluquero').value = '';
            document.querySelector('#horas').innerHTML = '';
            document.querySelector('.etiqueta-horas').innerHTML = '';
        }

    });
}

// NUEVAS FUNCIONES PARA CARGAR Y MANEJAR PELUQUEROS
async function consultarPeluqueros() {
    try {
        const url = 'http://localhost:3000/api/peluqueros';
        const resultado = await fetch(url);
        const peluqueros = await resultado.json();
        llenarSelectPeluqueros(peluqueros);
    } catch (error) {
        console.log(error);
    }
}

function llenarSelectPeluqueros(peluqueros) {
    const select = document.querySelector('#peluquero');
    peluqueros.forEach(peluquero => {
        const option = document.createElement('OPTION');
        option.value = peluquero.id;
        option.textContent = `${peluquero.nombre} ${peluquero.apellido}`;
        option.dataset.nombre = peluquero.nombre;
        option.dataset.apellido = peluquero.apellido;
        option.dataset.telefono = peluquero.telefono;
        select.appendChild(option);
    });
}

// 5. NUEVA FUNCIÓN para manejar la selección del peluquero
function seleccionarPeluquero() {
    const selectPeluquero = document.querySelector('#peluquero');
    selectPeluquero.addEventListener('change', function(e) {
        const peluqueroId = e.target.value;
        const opcionSeleccionada = e.target.options[e.target.selectedIndex];
        turno.peluquero = {
            id: peluqueroId,
            nombre: opcionSeleccionada.dataset.nombre,
            apellido: opcionSeleccionada.dataset.apellido,
            telefono: opcionSeleccionada.dataset.telefono
        };
        // Resetear la hora al cambiar de peluquero
        turno.hora = '';
        // Buscar horas disponibles para esta fecha + peluquero
        if (turno.fecha) {
            obtenerHorasDisponibles(turno.fecha, peluqueroId);
        }
    });
}

function seleccionarHora() {
    // Esta función queda simplificada ya que la lógica de selección 
    // se maneja al hacer clic en los botones dinámicos.
}

async function obtenerHorasDisponibles(fecha, peluqueroId) {

    if(!turno.peluquero) return;

    try {
        const url = `http://localhost:3000/api/turnos?fecha=${fecha}&peluquero_id=${peluqueroId}`;
        const resultado = await fetch(url);
        const horasOcupadas = await resultado.json();

        mostrarHorasGrid(horasOcupadas);
    } catch (error) {
        console.log(error);
    }
}

function mostrarHorasGrid(horasOcupadas) {

    // Obtener el div de la etiqueta de horas
    const etiquetaHoras = document.querySelector('.etiqueta-horas');
    etiquetaHoras.innerHTML = ''; // Limpiar la etiqueta de horas anterior

    // Crear la etiqueta de horas
    const etiqueta = document.createElement('P');
    etiqueta.textContent = 'Horas disponibles:';
    etiqueta.classList.add('etiqueta-horas');
    etiquetaHoras.appendChild(etiqueta);

    // Agregar las horas disponibles al contenedor
    const contenedorHoras = document.querySelector('#horas');
    contenedorHoras.innerHTML = ''; // Limpiar las horas anteriores

    // Listado de todas las horas posibles
    const horasEstablecimiento = [
        "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", 
        "13:00", "13:30", "14:00", "14:30", "15:00", "15:30",
        "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", 
        "19:00", "19:30", "20:00", "20:30", "21:00"
    ];

    horasEstablecimiento.forEach(hora => {
        const botonHora = document.createElement('BUTTON');
        botonHora.type = 'button';
        botonHora.textContent = hora;
        botonHora.classList.add('hora-boton');

        // Comprobar si la hora está en el arreglo de horas ocupadas
        if (horasOcupadas.includes(hora)) {
            botonHora.disabled = true;
            botonHora.classList.add('ocupada');
        } else {
            // Permitir seleccionar la hora si está disponible
            botonHora.onclick = function() {
                // Remover la clase seleccionado de otros botones
                const botonSeleccionadoPrevio = document.querySelector('.hora-boton.seleccionada');
                if (botonSeleccionadoPrevio) {
                    botonSeleccionadoPrevio.classList.remove('seleccionada');
                }

                // Marcar el botón actual como seleccionado
                botonHora.classList.add('seleccionada');

                // Guardar la hora en el objeto del turno
                turno.hora = hora;
                
                // (Opcional) Asignar el valor al input hidden para compatibilidad
                document.querySelector('#hora').value = hora;
            };
        }

        contenedorHoras.appendChild(botonHora);
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

    const { nombre, telefono, fecha, hora, servicios, peluquero } = turno;

    if(!nombre || !telefono || !fecha || !hora || !peluquero || servicios.length === 0) {
        mostrarAlerta('Faltan datos de Nombre, Teléfono, Servicios, Fecha, Peluquero u Hora', 'error', '.contenido-resumen', false);

        return;
    }

    // Construir el resumen

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
    peluqueroTurno.innerHTML = `<span>Peluquero: </span> ${turno.peluquero.nombre} ${turno.peluquero.apellido}`;

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

    resumen.appendChild(botonReservar);
}

async function reservarTurno() {
    
    const {id, nombre, telefono, fecha, hora, peluquero, servicios} = turno;
    
    // Arreglo con los IDs de los servicios
    const idServicios = servicios.map(servicio => servicio.id);
    
    const datos = new FormData();
    datos.append('usuario_id', id);
    datos.append('nombre', nombre);
    datos.append('telefono', telefono);
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('peluquero_id', peluquero.id);
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

            const mensaje = `Hola! Me gustaría reservar un turno.\nNombre: ${nombre}\nServicios: ${nombresServicios}\nFecha: ${fechaLegible} a las ${hora}hs\nTotal a pagar: $${total}`;

            const telefonoPeluquero = peluquero.telefono.replace(/\D/g, '');
            const urlWhatsApp = `https://wa.me/549${telefonoPeluquero}?text=${encodeURIComponent(mensaje)}`;

            Swal.fire({
                icon: "success",
                title: "Turno Reservado",
                text: "Tu turno ha sido reservado. Serás redirigido a WhatsApp.",
                button: "OK",
                customClass: {
                    popup: 'mi-alerta'
                }
            }).then(() => {
                window.open(urlWhatsApp, '_blank');
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