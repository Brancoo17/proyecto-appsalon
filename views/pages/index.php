<?php
/** @var array $servicios */
/** @var array $peluqueros */
/** @var bool $auth */
/** @var string $nombre */
/** @var mixed $admin */
/** @var mixed $peluquero_sesion */
?>

<!-- HEADER / NAVEGACIÓN -->
<header class="home-header">
    <div class="contenedor header-contenido">
        <a href="/" class="logo">
            <img src="/build/img/icons8-peluquero-48.png" alt="Logo BarberShop" class="logo-img">
            <span>Barber<strong>Shop</strong></span>
        </a>

        <!-- Botón menú móvil -->
        <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir Menú">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <nav class="nav-principal" id="navPrincipal">
            <a href="#inicio" class="nav-link">Inicio</a>
            <a href="#servicios" class="nav-link">Servicios</a>
            <a href="#equipo" class="nav-link">Peluqueros</a>
            <a href="#nosotros" class="nav-link">Nosotros</a>
            <a href="#contacto" class="nav-link">Ubicación</a>

            <div class="nav-acciones">
                <?php if($auth): ?>
                    <a href="/usuario" class="boton-nav boton-nav-secundario"><i class="fa-solid fa-user"></i>&nbsp;Mi Cuenta</a>
                    <?php if($admin): ?>
                        <a href="/admin" class="boton-nav"><i class="fa-solid fa-user-tie"></i>&nbsp;Panel Admin</a>
                    <?php elseif($peluquero_sesion): ?>
                        <a href="/peluquero" class="boton-nav"><i class="fa-solid fa-user-tie"></i>&nbsp;Mi Panel</a>
                    <?php else: ?>
                        <a href="/turno" class="boton-nav"><i class="fa-solid fa-calendar-check"></i>&nbsp;Sacar Turno</a>
                    <?php endif; ?>
                    <a href="/logout" class="boton-nav boton-nav-logout"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/login" class="boton-nav boton-nav-secundario"><i class="fa-solid fa-user"></i>&nbsp;Iniciar Sesión</a>
                    <a href="/crear-cuenta" class="boton-nav boton-nav-link"><i class="fa-solid fa-user-plus"></i>&nbsp;Registrarse</a>
                    <a href="/turno" class="boton-nav boton-nav-cta"><i class="fa-solid fa-calendar-check"></i>&nbsp;Reservar Turno</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<!-- HERO / PORTADA -->
<section id="inicio" class="hero">
    <div class="hero-overlay"></div>
    <div class="contenedor hero-contenido">
        <div class="badge-hero">
            <span>✦</span> Estilo & Cuidado Masculino Premium
        </div>
        <h1 class="hero-titulo">Elevá Tu Estilo con los Mejores Profesionales</h1>
        <p class="hero-descripcion">
            Cortes clásicos, degradados modernos, perfilado de barba y tratamientos exclusivos. Reserva tu turno de forma rápida, con o sin registro.
        </p>

        <div class="hero-botones">
            <a href="/turno" class="boton boton-primario"><i class="fa-solid fa-calendar-check"></i>&nbsp;Reservar Turno Ahora</a>
            <a href="#servicios" class="boton boton-secundario"><i class="fa-solid fa-list"></i>&nbsp;Ver Servicios</a>
        </div>

        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-numero">+5</span>
                <span class="stat-texto">Años de Experiencia</span>
            </div>
            <div class="stat-item">
                <span class="stat-numero">100%</span>
                <span class="stat-texto">Satisfacción Garantizada</span>
            </div>
            <div class="stat-item">
                <span class="stat-numero">+1.5k</span>
                <span class="stat-texto">Clientes Felices</span>
            </div>
        </div>
    </div>
</section>

<!-- BENEFICIOS / CARACTERÍSTICAS -->
<section class="seccion-beneficios">
    <div class="contenedor grid-beneficios">
        <div class="tarjeta-beneficio">
            <div class="icono-beneficio"><i class="fas fa-cut"></i></div>
            <h3>Peluqueros Expertos</h3>
            <p>Profesionales capacitados en las últimas tendencias y técnicas tradicionales de barbería.</p>
        </div>
        <div class="tarjeta-beneficio">
            <div class="icono-beneficio"><i class="fa-solid fa-bolt"></i></div>
            <h3>Reserva en Segundos</h3>
            <p>Elige tu barbero de confianza, servicio, día y horario disponible sin esperas telefónicas.</p>
        </div>
        <div class="tarjeta-beneficio">
            <div class="icono-beneficio"><i class="fas fa-gem"></i></div>
            <h3>Ambiente Exclusivo</h3>
            <p>Música de ambiente, atención de primera y productos cosméticos de la más alta calidad.</p>
        </div>
    </div>
</section>

<!-- SERVICIOS -->
<section id="servicios" class="seccion-servicios">
    <div class="contenedor">
        <div class="encabezado-seccion">
            <span class="subtitulo-seccion">Lo Que Hacemos</span>
            <h2 class="titulo-seccion">Nuestros Servicios</h2>
            <p class="descripcion-seccion">Precios claros y atención personalizada para cada cliente.</p>
        </div>

        <?php if(!empty($servicios)): ?>
            <div class="grid-servicios-home">
                <?php foreach($servicios as $servicio): ?>
                    <div class="tarjeta-servicio-home">
                        <div class="servicio-header">
                            <h3 class="servicio-nombre"><?php echo s($servicio->nombre); ?></h3>
                            <span class="servicio-precio">$<?php echo s($servicio->precio); ?></span>
                        </div>
                        <p class="servicio-detalle">Cuidado completo, acabado profesional y asesoramiento de imagen.</p>
                        <a href="/turno?servicio=<?php echo $servicio->id; ?>" class="boton-servicio">Reservar Este Servicio &rarr;</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No hay servicios disponibles en este momento.</p>
        <?php endif; ?>

        <div class="servicios-cta">
            <p>¿Buscas una combinación de varios servicios?</p>
            <a href="/turno" class="boton boton-primario">Armar Mi Turno Personalizado</a>
        </div>
    </div>
</section>

<!-- NUESTRO EQUIPO / PELUQUEROS -->
<section id="equipo" class="seccion-equipo">
    <div class="contenedor">
        <div class="encabezado-seccion">
            <span class="subtitulo-seccion">Talento y Pasión</span>
            <h2 class="titulo-seccion">Nuestros Peluqueros</h2>
            <p class="descripcion-seccion">Conoce al equipo que se encargará de llevar tu estilo al siguiente nivel.</p>
        </div>

        <?php if(!empty($peluqueros)): ?>
            <div class="grid-equipo">
                <?php foreach($peluqueros as $index => $peluquero): ?>
                    <div class="tarjeta-peluquero">
                        <div class="peluquero-avatar">
                            <span><?php echo strtoupper(substr($peluquero->nombre, 0, 1) . substr($peluquero->apellido, 0, 1)); ?></span>
                        </div>
                        <div class="peluquero-info">
                            <h3 class="peluquero-nombre"><?php echo s($peluquero->nombre . ' ' . $peluquero->apellido); ?></h3>
                            <span class="peluquero-rol">Barbero Especialista</span>
                            <a href="/turno" class="boton-reservar-peluquero">Reservar con <?php echo s($peluquero->nombre); ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Pronto conocerás a nuestro equipo.</p>
        <?php endif; ?>
    </div>
</section>

<!-- SOBRE NOSOTROS -->
<section id="nosotros" class="seccion-nosotros">
    <div class="contenedor grid-nosotros">
        <div class="nosotros-imagen">
            <img src="/build/img/2.jpg" alt="Interior BarberShop" loading="lazy">
        </div>
        <div class="nosotros-texto">
            <span class="subtitulo-seccion">Sobre Nosotros</span>
            <h2 class="titulo-seccion">Más Que un Corte, una Experiencia Única</h2>
            <p>
                En <strong>BarberShop</strong> creemos que el cuidado masculino es un arte. Diseñamos cada espacio para que desconectes de la rutina, disfrutes de un buen momento y salgas con la confianza de lucir impecable.
            </p>
            <ul class="lista-puntos">
                <li><span>✔</span> Productos de marcas internacionales de primer nivel.</li>
                <li><span>✔</span> Higiene, desinfección y toallas calientes en cada servicio.</li>
                <li><span>✔</span> Puntualidad absoluta respetando el horario de tu turno.</li>
            </ul>
            <a href="/turno" class="boton boton-primario">Agendar una Cita</a>
        </div>
    </div>
</section>

<!-- HORARIOS Y CONTACTO -->
<section id="contacto" class="seccion-contacto">
    <div class="contenedor">
        <div class="encabezado-seccion">
            <span class="subtitulo-seccion">Visítanos</span>
            <h2 class="titulo-seccion">Horarios & Ubicación</h2>
            <p class="descripcion-seccion">Estamos esperándote en el corazón de la ciudad.</p>
        </div>

        <div class="grid-contacto">
            <div class="tarjeta-contacto">
                <div class="icono-contacto"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Ubicación</h3>
                <p>Av. Principal 1234, Centro</p>
                <p class="texto-secundario">Buenos Aires, Argentina</p>
            </div>

            <div class="tarjeta-contacto">
                <div class="icono-contacto"><i class="fas fa-clock"></i></div>
                <h3>Horarios de Atención</h3>
                <p>Lunes a Sábados: <strong>09:00 - 20:00 hs</strong></p>
                <p class="texto-secundario">Domingos: Cerrado</p>
            </div>

            <div class="tarjeta-contacto">
                <div class="icono-contacto"><i class="fas fa-phone"></i></div>
                <h3>Contacto Directo</h3>
                <p>Teléfono: <strong>(011) 4567-8900</strong></p>
                <p class="texto-secundario">Consultas vía WhatsApp o llamada</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="banner-cta">
    <div class="contenedor banner-contenido">
        <h2>¿Listo Para Renovar Tu Imagen?</h2>
        <p>Selecciona tu peluquero favorito y agenda tu turno en segundos. Sin trámites innecesarios.</p>
        <a href="/turno" class="boton boton-primario boton-lg">Reservar Mi Turno Ahora</a>
    </div>
</section>

<!-- FOOTER -->
<footer class="home-footer">
    <div class="contenedor footer-contenido">
        <div class="footer-col">
            <div class="logo logo-footer">
                <span>Barber<strong>Shop</strong></span>
            </div>
            <p>La barbería elegida por quienes buscan calidad, precisión y estilo.</p>
        </div>

        <div class="footer-col">
            <h4>Enlaces Rápidos</h4>
            <ul class="footer-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="#equipo">Peluqueros</a></li>
                <li><a href="#contacto">Ubicación</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Acceso</h4>
            <ul class="footer-links">
                <li><a href="/turno">Sacar Turno</a></li>
                <li><a href="/login">Iniciar Sesión</a></li>
                <li><a href="/crear-cuenta">Crear Cuenta</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="contenedor">
            <p>&copy; <?php echo date('Y'); ?> BarberShop. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<script>
    // Toggle para menú móvil
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const navPrincipal = document.getElementById('navPrincipal');

    if(mobileBtn && navPrincipal) {
        mobileBtn.addEventListener('click', () => {
            mobileBtn.classList.toggle('activo');
            navPrincipal.classList.toggle('activo');
        });

        // Cerrar menú al hacer clic en un enlace
        navPrincipal.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileBtn.classList.remove('activo');
                navPrincipal.classList.remove('activo');
            });
        });
    }
</script>
