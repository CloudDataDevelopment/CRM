<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Documentación';
$this->params['breadcrumbs'][] = $this->title;

// 🔥 CSS específico (reutiliza el mismo aviso.css)
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

// ============================================================
// 🔥 CONFIGURACIÓN DE LA DOCUMENTACIÓN
// ============================================================
// SOLO EDITA ESTE ARRAY PARA AGREGAR/QUITAR/MODIFICAR VIDEOS
//
// Estructura:
// 'titulo' => Título del bloque (grupo de videos)
// 'icono'  => Icono de Bootstrap Icons (https://icons.getbootstrap.com/)
// 'color'  => Color de acento (hexadecimal)
// 'videos' => Array de videos. Cada video tiene:
//     'titulo'   => Título del video
//     'desc'     => Descripción corta (opcional pero recomendado)
//     'url'      => LINK del video (obligatorio)
//     'tipo'     => Cómo se va a mostrar:
//                     'youtube' → Embed automático de YouTube
//                     'vimeo'   → Embed automático de Vimeo
//                     'loom'    → Embed de Loom
//                     'iframe'  → URL de embed directa (Google Drive, etc.)
//                     'link'    → Solo un botón que abre en nueva pestaña
//     'duracion' => Duración del video (opcional, ej: '5:32')
//     'tags'     => Etiquetas (opcional)
// ============================================================

$documentacion = [

    // =======================================================
    // BLOQUE 1: PRIMEROS PASOS
    // =======================================================
    [
        'titulo' => 'Primeros Pasos',
        'icono'  => 'bi-rocket-takeoff',
        'color'  => '#34b7a7',
        'videos' => [

            [
                'titulo'   => 'Cómo iniciar sesión',
                'desc'     => 'Aprende a acceder con tus credenciales y recuperar contraseña.',
                'url'      => 'https://youtu.be/W8ulZ_iWOqM',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:56',
                'tags'     => ['Login', 'Seguridad'],
            ],

        ],
    ],
        // =======================================================
    // BLOQUE 2: dashboard
    // =======================================================
    [
        'titulo' => 'Dashboard',
        'icono'  => 'bi-rocket-takeoff',
        'color'  => '#b73434',
        'videos' => [

            [
                'titulo'   => 'Introducción al dashboard',
                'desc'     => 'Conoce la interfaz del dashboard y las funciones principales.',
                'url'      => 'https://youtu.be/pSfI8WdmacI',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:40',
                'tags'     => ['Introducción', 'Básico'],
            ],

            [
                'titulo'   => 'Ventas',
                'desc'     => 'Conoce las funcionalidades de ventas en el dashboard.',
                'url'      => 'https://youtu.be/mmvs7d9d7NU',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:30',
                'tags'     => ['Ventas', 'Dashboard'],
            ],
            
            [
                'titulo'   => 'Actividades',
                'desc'     => 'Aprende sobre las actividades en el dashboard',
                'url'      => 'https://youtu.be/mmvs7d9d7NU',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:35',
                'tags'     => ['Actividades'],
            ],
            [
                'titulo'   => 'Leads por estado',
                'desc'     => 'Conoce los estados de tus leads',
                'url'      => 'https://youtu.be/tbEJoKbbbJE',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:39',
                'tags'     => ['Actividades'],
            ],
            [
                'titulo'   => 'Metas y utilidad',
                'desc'     => 'Ajusta tus metas y tulidades',
                'url'      => 'https://youtu.be/rPD0xQ7cWYs',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:58',
                'tags'     => ['Actividades'],
            ],
            [
                'titulo'   => 'Resultados',
                'desc'     => 'Conoce los resultados de las ventas',
                'url'      => 'https://youtu.be/rPD0xQ7cWYs',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:37',
                'tags'     => ['Actividades'],
            ],
            


        ],
    ],

    // =======================================================
    // BLOQUE 2: GESTIÓN DE LEADS
    // =======================================================
    [
        'titulo' => 'Gestión de Leads',
        'icono'  => 'bi-people',
        'color'  => '#4e73df',
        'videos' => [

            [
                'titulo'   => 'Crear un nuevo lead',
                'desc'     => 'Registra un lead paso a paso.',
                'url'      => 'https://youtu.be/xzzfKFxCxrA',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '1:08',
                'tags'     => ['Leads', 'Registro'],
            ],

            [
                'titulo'   => 'Asignación de leads',
                'desc'     => 'Asigna múltiples leads a un agente en segundos.',
                'url'      => 'https://youtu.be/2hElz3km7yE',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '0:50',
                'tags'     => ['Asignación', 'Productividad'],
            ],

            [
                'titulo'   => 'Guardar registros',
                'desc'     => 'Guarda los registros de tus leads ',
                'url'      => 'https://youtu.be/E3SzAkViH2k',  // 🔥 Ejemplo Vimeo
                'tipo'     => 'youtube',
                'duracion' => '0:41',
                'tags'     => ['Registros'],
            ],

        ],
    ],

    // =======================================================
    // BLOQUE 3: COTIZACIONES
    // =======================================================
    [
        'titulo' => 'Cotizaciones y Ventas',
        'icono'  => 'bi-cash-coin',
        'color'  => '#1cc88a',
        'videos' => [

            [
                'titulo'   => 'Crear una cotización',
                'desc'     => 'Genera cotizaciones profesionales con montos y pagos.',
                'url'      => '',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '',
                'tags'     => ['Cotización', 'Ventas'],
            ],

            [
                'titulo'   => 'Registrar pagos y saldos',
                'desc'     => 'Aprende a registrar abonos y actualizar el saldo pendiente.',
                'url'      => '',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '',
                'tags'     => ['Pagos', 'Saldos'],
            ],

        ],
    ],

    // =======================================================
    // BLOQUE 4: REPORTES
    // =======================================================
    [
        'titulo' => 'Reportes y Dashboard',
        'icono'  => 'bi-bar-chart',
        'color'  => '#f6c23e',
        'videos' => [

            [
                'titulo'   => 'Interpretar el dashboard',
                'desc'     => 'Cómo leer las métricas, metas y embudo de ventas.',
                'url'      => '',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '',
                'tags'     => ['Dashboard', 'Métricas'],
            ],

            [
                'titulo'   => 'Generar reporte PDF',
                'desc'     => 'Exporta la información de cualquier módulo a PDF.',
                'url'      => '',  // 🔥 AQUÍ TU LINK
                'tipo'     => 'youtube',
                'duracion' => '',
                'tags'     => ['Reportes', 'PDF'],
            ],

        ],
    ],

    // =======================================================
    // 🔥 AGREGA MÁS BLOQUES AQUÍ SI NECESITAS
    // =======================================================
    // [
    //     'titulo' => 'Marketing',
    //     'icono'  => 'bi-megaphone',
    //     'color'  => '#e74a3b',
    //     'videos' => [
    //         [
    //             'titulo'   => 'Crear campaña',
    //             'desc'     => 'Aprende a crear y publicar una campaña.',
    //             'url'      => 'https://www.youtube.com/watch?v=TU_LINK',
    //             'tipo'     => 'youtube',
    //             'duracion' => '6:00',
    //             'tags'     => ['Campañas'],
    //         ],
    //     ],
    // ],

];
?>

<div class="privacy-page">

    <!-- HEADER -->
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="<?= Url::home() ?>" class="logo d-flex align-items-center">
                <h1 class="sitename">Documentación.</h1>
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
                        <li class="current">Documentación</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-lg-12">

                    <section id="blog-details" class="blog-details section">
                        <div class="container">

                            <article class="article">

                                <h3 class="title">CENTRO DE DOCUMENTACIÓN</h3>

                                <div class="meta-top"></div>

                                <div class="content">

                                    <p style="text-align: center; color: #6c757d;">
                                        <i class="bi bi-collection-play"></i>
                                        Biblioteca de videos tutoriales para aprender a usar el sistema paso a paso.
                                    </p>

                                    <!-- 🔥 BLOQUES DINÁMICOS -->
                                    <?php foreach ($documentacion as $bloque): ?>

                                        <?php if (empty($bloque['videos'])) continue; ?>

                                        <div class="docs-block" style="border-left: 4px solid <?= Html::encode($bloque['color']) ?>;">

                                            <h5 class="docs-block-title" style="color: <?= Html::encode($bloque['color']) ?>;">
                                                <i class="<?= Html::encode($bloque['icono']) ?>"></i>
                                                <?= Html::encode($bloque['titulo']) ?>
                                                <span class="docs-count"><?= count($bloque['videos']) ?> videos</span>
                                            </h5>

                                            <div class="docs-videos-grid">

                                                <?php foreach ($bloque['videos'] as $video): ?>

                                                    <?php
                                                    // 🔥 Generar URL de embed según el tipo
                                                    $embedUrl = '#';
                                                    $tipo = $video['tipo'] ?? 'youtube';
                                                    $videoId = '';

                                                    if ($tipo === 'youtube') {
                                                        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]+)/', $video['url'], $m)) {
                                                            $videoId = $m[1];
                                                        }
                                                        $embedUrl = $videoId ? 'https://www.youtube.com/embed/' . $videoId : $video['url'];

                                                    } elseif ($tipo === 'vimeo') {
                                                        if (preg_match('/vimeo\.com\/(\d+)/', $video['url'], $m)) {
                                                            $videoId = $m[1];
                                                        }
                                                        $embedUrl = $videoId ? 'https://player.vimeo.com/video/' . $videoId : $video['url'];

                                                    } elseif ($tipo === 'loom') {
                                                        $embedUrl = $video['url'];

                                                    } elseif ($tipo === 'iframe') {
                                                        $embedUrl = $video['url'];

                                                    } else {
                                                        $embedUrl = $video['url'];
                                                    }

                                                    $puedeEmbed = in_array($tipo, ['youtube', 'vimeo', 'loom', 'iframe'])
                                                                  && !empty($embedUrl)
                                                                  && $embedUrl !== '#';
                                                    ?>

                                                    <div class="docs-video-card">

                                                        <div class="docs-video-thumb">
                                                            <?php if ($puedeEmbed): ?>
                                                                <iframe
                                                                    src="<?= Html::encode($embedUrl) ?>"
                                                                    title="<?= Html::encode($video['titulo']) ?>"
                                                                    frameborder="0"
                                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                    allowfullscreen
                                                                    loading="lazy">
                                                                </iframe>
                                                            <?php else: ?>
                                                                <a href="<?= Html::encode($video['url']) ?>" target="_blank" rel="noopener" class="docs-video-link">
                                                                    <i class="bi bi-play-circle-fill"></i>
                                                                    <span>Ver video</span>
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if (!empty($video['duracion'])): ?>
                                                                <span class="docs-video-duration">
                                                                    <i class="bi bi-clock"></i> <?= Html::encode($video['duracion']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="docs-video-info">
                                                            <h6 class="docs-video-title">
                                                                <?= Html::encode($video['titulo']) ?>
                                                            </h6>

                                                            <?php if (!empty($video['desc'])): ?>
                                                                <p class="docs-video-desc">
                                                                    <?= Html::encode($video['desc']) ?>
                                                                </p>
                                                            <?php endif; ?>

                                                            <?php if (!empty($video['tags'])): ?>
                                                                <div class="docs-video-tags">
                                                                    <?php foreach ($video['tags'] as $tag): ?>
                                                                        <span class="docs-tag"><?= Html::encode($tag) ?></span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                    </div>

                                                <?php endforeach; ?>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                    <?php if (empty($documentacion)): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-camera-video-off" style="font-size: 3rem;"></i>
                                            <p class="mt-3">Aún no hay videos disponibles.</p>
                                        </div>
                                    <?php endif; ?>

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


                <div class="col-md-4">
                    <h5>Contacto</h5>
                    <ul class="footer-info">
                        <li><i class="bi bi-telephone"></i> +52 998 240 1782</li>
                        <li><i class="bi bi-envelope"></i> contacto@soporte@clouddatadevelopment.com.com</li>
                        <li><i class="bi bi-clock"></i> Lun – Vie | 9:00 – 18:00</li>
                    </ul>
                </div>


            </div>

            <hr>

            <div class="footer-bottom">
                <p>
                    © Copyright <strong>Clouddatapage</strong> · Todos los derechos reservados
                </p>
                <p>
                    Desarrollado por
                    <a href="https://clouddatapage.com/" target="_blank">Clouddatapage</a>
                </p>
                <p>
                    <?= Html::a('Aviso de Privacidad', ['site/privacy']) ?>
                    &nbsp;|&nbsp;
                    <?= Html::a('Términos y Condiciones', ['site/terms']) ?>
                    &nbsp;|&nbsp;
                    <?= Html::a('Documentación', ['site/docs']) ?>
                </p>
            </div>
        </div>
    </footer>

</div><!-- /.privacy-page -->

<!-- ============================================ -->
<!-- 🔥 ESTILOS ESPECÍFICOS DE DOCUMENTACIÓN      -->
<!-- ============================================ -->
<style>
.docs-block {
    background: #f8f9fa;
    padding: 20px 25px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.docs-block-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.docs-block-title i {
    font-size: 1.4rem;
}

.docs-count {
    background: rgba(0,0,0,0.08);
    color: #495057;
    font-size: 0.75rem;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 500;
    margin-left: auto;
}

.docs-videos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.docs-video-card {
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.docs-video-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.docs-video-thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
    overflow: hidden;
}

.docs-video-thumb iframe {
    width: 100%;
    height: 100%;
    border: 0;
}

.docs-video-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #ffffff;
    text-decoration: none;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    gap: 10px;
}

.docs-video-link i {
    font-size: 3rem;
    color: #34b7a7;
}

.docs-video-link span {
    font-size: 0.9rem;
    font-weight: 500;
}

.docs-video-duration {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.75);
    color: #fff;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.docs-video-info {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.docs-video-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #37423b;
    margin-bottom: 6px;
    line-height: 1.3;
}

.docs-video-desc {
    font-size: 0.82rem;
    color: #6c757d;
    line-height: 1.4;
    margin-bottom: 10px;
    flex-grow: 1;
}

.docs-video-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.docs-tag {
    background: #e9ecef;
    color: #495057;
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .docs-videos-grid {
        grid-template-columns: 1fr;
    }

    .docs-block {
        padding: 15px;
    }
}
</style>