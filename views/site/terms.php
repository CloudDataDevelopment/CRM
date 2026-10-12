<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Términos y Condiciones';
$this->params['breadcrumbs'][] = $this->title;

// 🔥 CSS específico del aviso/términos (usa tu archivo de estilos)
$this->registerCssFile('@web/css/aviso.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

// 🔥 Bootstrap Icons
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css', [
    'position' => \yii\web\View::POS_HEAD,
]);

// Favicon
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/jpeg', 'href' => Url::to('@web/img/icon4.jpg.jpg')]);

// 🔥 Body class para aplicar estilos de fondo
$this->registerJs("document.body.classList.add('blog-details-page');", \yii\web\View::POS_READY);
?>

<div class="privacy-page">

    <!-- HEADER -->
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="<?= Url::home() ?>" class="logo d-flex align-items-center">
                <h1 class="sitename">Términos y Condiciones.</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <a href="<?= Url::home() ?>" class="btn-getstarted">Volver al Inicio</a>
            </nav>

        </div>
    </header>

    <main class="main">

        <!-- Page Title / Breadcrumbs -->
        <div class="page-title light-background">
            <div class="container">
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="<?= Url::home() ?>">Inicio</a></li>
                        <li class="current">Términos y Condiciones</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-lg-12">

                    <!-- Blog Details Section -->
                    <section id="blog-details" class="blog-details section">
                        <div class="container">

                            <article class="article">

                                <h3 class="title">TÉRMINOS Y CONDICIONES DE USO DEL CRM PLOTYX</h3>

                                <div class="meta-top"></div>

                                <div class="content">

                                    <p>
                                        El presente documento establece los Términos y Condiciones que regulan el acceso, uso y contratación de la plataforma <strong>CRM PLOTYX</strong> (en adelante, el "Servicio"), operada y proporcionada por <strong>PLOTYX</strong>. Al registrarse, acceder o utilizar el CRM, el usuario (en adelante, el "Cliente" o "Usuario") acepta de manera expresa y sin reservas estar vinculado por estos términos. Si no está de acuerdo con ellos, deberá abstenerse de utilizar la plataforma.
                                    </p>

                                    <h5>1. OBJETO DEL SERVICIO</h5>
                                    <p>
                                        PLOTYX proporciona un sistema de Gestión de Relaciones con Clientes (CRM) basado en la nube, diseñado para ayudar a las empresas a administrar sus prospectos, ventas, interacciones comerciales y flujos de trabajo internos de manera automatizada y organizada.
                                    </p>

                                    <h5>2. REGISTRO Y CUENTA DE USUARIO</h5>
                                    <p>
                                        A. Para utilizar el CRM, el Usuario deberá crear una cuenta proporcionando información veraz, completa y actualizada.<br>
                                        B. El Usuario es el único responsable de mantener la confidencialidad de sus credenciales de acceso (nombre de usuario y contraseña) y de todas las actividades que ocurran bajo su cuenta.<br>
                                        C. PLOTYX se reserva el derecho de suspender o cancelar cuentas que presenten información falsa, fraudulenta o que violen los presentes términos.
                                    </p>

                                    <h5>3. USO ACEPTEBLE Y RESTRICCIONES</h5>
                                    <p>
                                        El Cliente se compromete a utilizar el CRM exclusivamente para fines comerciales lícitos. Queda estrictamente prohibido:<br>
                                        • Utilizar la plataforma para el envío masivo de correo no solicitado (Spam) o campañas de marketing deshonestas.<br>
                                        • Almacenar o transmitir contenido ilegal, malicioso, difamatorio o que infrinja derechos de terceros.<br>
                                        • Intentar vulnerar la seguridad, realizar ingeniería inversa, alterar o dañar la infraestructura tecnológica de PLOTYX.
                                    </p>

                                    <h5>4. PROPIEDAD DE LOS DATOS Y PRIVACIDAD</h5>
                                    <p>
                                        A. <strong>Datos del Cliente:</strong> Todos los datos, bases de contactos, leads e información comercial que el Cliente cargue o procese a través del CRM son de su exclusiva propiedad.<br>
                                        B. PLOTYX no utilizará los datos comerciales del Cliente para fines distintos a la prestación y mejora del servicio.<br>
                                        C. El tratamiento de los datos personales recopilados se regirá conforme a nuestro <?= Html::a('Aviso de Privacidad', ['site/privacy']) ?>, el cual cumple con la normatividad aplicable.
                                    </p>

                                    <h5>5. DISPONIBILIDAD Y SOPORTE</h5>
                                    <p>
                                        A. PLOTYX realizará sus mejores esfuerzos técnicos para garantizar la disponibilidad continua de la plataforma (99.9% de uptime estimado), exceptuando ventanas de mantenimiento programado o fallas de fuerza mayor.<br>
                                        B. El soporte técnico se brindará en los horarios y canales establecidos por PLOTYX (Lunes a Viernes de 9:00 a 18:00 horas).
                                    </p>

                                    <h5>6. LIMITACIÓN DE RESPONSABILIDAD</h5>
                                    <p>
                                        PLOTYX no se hace responsable por pérdidas de beneficios comerciales, interrupciones de negocio o daños indirectos derivados del uso o la imposibilidad de uso temporal del CRM, ni por fallas en conexiones de internet atribuibles al proveedor del Cliente. El Cliente es responsable de realizar respaldos periódicos de su información crítica.
                                    </p>

                                    <h5>7. MODIFICACIONES A LOS TÉRMINOS</h5>
                                    <p>
                                        PLOTYX se reserva el derecho de modificar estos Términos y Condiciones en cualquier momento. Las modificaciones entrarán en vigor a partir de su publicación en el sitio web oficial o notificación dentro de la plataforma. El uso continuado del CRM constituirá la aceptación de los nuevos términos.
                                    </p>

                                    <h5>8. CONTACTO</h5>
                                    <p>
                                        Para cualquier duda, aclaración o soporte relacionado con estos Términos y Condiciones, puede ponerse en contacto con nosotros a través de nuestro correo electrónico oficial: <strong>contacto@plotyx.com</strong> o en nuestras oficinas ubicadas en Cancún, Quintana Roo.
                                    </p>

                                </div>

                                <div class="meta-bottom"></div>

                            </article>

                        </div>
                    </section>

                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="footer-dinato">
        <div class="container">
            <div class="row gy-4">

                <!-- Empresa -->
                <div class="col-md-4">
                    <h5>PLOTYX</h5>
                    <p>
                        Impulsamos la transformación digital y el crecimiento tecnológico a través de soluciones de TI y desarrollo de software de alta calidad.
                    </p>
                </div>

                <!-- Contacto -->
                <div class="col-md-4">
                    <h5>Contacto</h5>
                    <ul class="footer-info">
                        <li><i class="bi bi-telephone"></i> +52 998 240 1782</li>
                        <li><i class="bi bi-envelope"></i> contacto@plotyx.com</li>
                        <li><i class="bi bi-clock"></i> Lun – Vie | 9:00 – 18:00</li>
                    </ul>
                </div>

                <!-- Redes -->
                <div class="col-md-4 text-center">
                    <h5>Nuestras Redes Sociales</h5>
                    <ul class="footer-links">
                        <li><a href="https://www.facebook.com/share/1AP82GiC7f/" target="_blank"><i class="bi bi-facebook"></i></a></li>
                        <li><a href="https://www.instagram.com/dinato_inversiones?igsh=d296ZTFuOTQ1OG8z" target="_blank"><i class="bi bi-instagram"></i></a></li>
                        <li><a href="#" target="_blank"><i class="bi bi-linkedin"></i></a></li>
                        <li><a href="https://api.whatsapp.com/send/?phone=9988459281" target="_blank"><i class="bi bi-whatsapp"></i></a></li>
                    </ul>
                </div>

            </div>

            <hr>

            <!-- Créditos -->
            <div class="footer-bottom">
                <p>
                    © Copyright <strong>Clouddatadevelopment</strong> · Todos los derechos reservados
                </p>
                <p>
                    Desarrollado por
                    <a href="https://clouddadevelopment.com/" target="_blank">Clouddatadevelopment</a>
                </p>
                <p>
                    <?= Html::a('Aviso de Privacidad', ['site/privacy']) ?>
                </p>
            </div>
        </div>
    </footer>

</div><!-- /.privacy-page -->