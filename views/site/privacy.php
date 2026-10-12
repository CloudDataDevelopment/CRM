<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Aviso de Privacidad';
$this->params['breadcrumbs'][] = $this->title;

// 🔥 CSS específico del aviso (usa tu archivo aviso.css)
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

// 🔥 Body class para que apliquen los estilos de fondo gris
$this->registerJs("document.body.classList.add('blog-details-page');", \yii\web\View::POS_READY);
?>

<div class="privacy-page">

    <!-- HEADER -->
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="<?= Url::home() ?>" class="logo d-flex align-items-center">
                <h1 class="sitename">Aviso de privacidad.</h1>
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
                        <li class="current">Aviso de privacidad</li>
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

                                <h3 class="title">AVISO DE PRIVACIDAD INTEGRAL</h3>

                                <div class="meta-top"></div>

                                <div class="content">

                                    <p>
                                        El presente Aviso de Privacidad son conforme a los principios de licitud, consentimiento, información, calidad, finalidad, lealtad, proporcionalidad y
                                        responsabilidad. Por lo que PLOTYX, es el responsable del tratamiento de los Datos Personales que se recaban, los cuales serán protegidos de
                                        conformidad a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares; y demás normatividad que resulte aplicable.
                                    </p>

                                    <h5>LOS DATOS PERSONALES QUE SE RECABAN Y PARA SU FINALIDAD</h5>

                                    <p>
                                        Los datos personales que se recaban pueden comprender nombre completo, domicilio, número de celular y correo; así como INE, RFC, acta constitutiva, CURP.
                                        Los cuales serán utilizados con la finalidad de realizar el registro para brindar el servicio de Tecnologías de Información y Comunicación (IT).
                                        La información que se reúna permitirá proporcionar el servicio al cliente, para efectuar o gestionar el servicio que este adquiera.
                                    </p>

                                    <h5>DE LOS MEDIOS PARA EJERCER LOS DERECHOS DE ACCESO, RECTIFICACIÓN, CANCELACIÓN U OPOSICIÓN</h5>

                                    <p>
                                        El titular de los datos personales podrá ejercer sus derechos de Acceso, Rectificación, Cancelación u Oposición (ARCO), mediante solicitud correspondiente
                                        de manera presencial en las oficinas de PLOTYX. Dicha solicitud deberá ser de acuerdo a lo estipulado en la Ley Federal de Protección de Datos
                                        Personales en Posesión de los Particulares.
                                    </p>

                                    <h5>DE LAS TRANSFERENCIAS DE DATOS QUE SE EFECTÚEN</h5>

                                    <p>
                                        Se le informa que no se realizarán transferencias de datos personales ni tratamientos que requieran el consentimiento del titular de los datos personales.
                                    </p>

                                    <h5>DE LOS DERECHOS DE LOS TITULARES DE DATOS PERSONALES</h5>

                                    <p>
                                        A. El titular de los datos personales podrá ejercer los derechos de acceso, rectificación, cancelación y oposición.
                                        El ejercicio de cualquiera de ellos no es requisito previo ni impide el ejercicio de otro. Los datos personales recabados por PLOTYX son resguardados de tal manera que permite el ejercicio sin dilación de estos derechos. <br>

                                        B. Además, el titular de los datos personales titulares tiene derecho a acceder a sus datos personales que obren en poder del responsable,
                                        así como conocer el presente Aviso de Privacidad al que está sujeto el tratamiento.<br>

                                        C. El titular de los datos tendrá derecho a rectificarlos cuando sean inexactos o incompletos.

                                        D. El titular tendrá en todo momento el derecho a cancelar sus datos personales.

                                        E. La cancelación de datos personales dará lugar a un periodo de bloqueo tras el cual se procederá a la supresión del dato.
                                        PLOTYX, podrá conservarlos exclusivamente para efectos de las responsabilidades nacidas del tratamiento.<br>

                                        F. El periodo de bloqueo será equivalente al plazo de prescripción de las acciones derivadas de la relación jurídica que funda el tratamiento.
                                        Una vez cancelado el dato se dará aviso al titular.
                                    </p>

                                    <h5>DEL PROCEDIMIENTO Y MEDIO POR EL CUAL EL RESPONSABLE COMUNICARÁ A LOS TITULARES DE CAMBIOS AL AVISO DE PRIVACIDAD</h5>

                                    <p>
                                        En caso que exista cambios al presente Aviso de Privacidad, se hará de su conocimiento mediante el sitio de internet oficial de PLOTYX, en la sección de Aviso de Privacidad en el cual podrá consultarlo, o bien, en las oficinas de PLOTYX<br>

                                        El aviso de privacidad se pone a disposición de los titulares de los datos personales de manera presencial en las oficinas de PLOTYX, S.A.
                                        de así como en el sitio de internet con dirección. www.plotyx.com, plotyx.com. En la sección de Aviso de Privacidad.
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
                    <a href="https://clouddatadevelopment.com/" target="_blank">Clouddatadevelopment</a>
                </p>
                <p>
                    <?= Html::a('Aviso de Privacidad', ['site/privacy']) ?>
                </p>
            </div>
        </div>
    </footer>

</div><!-- /.privacy-page -->