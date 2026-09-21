<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Leads';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/lead-panel.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/lead-edit-modal.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

// Registrar JS (solo jQuery)
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

// Variables del controlador
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$statusList = isset($statusList) ? $statusList : [];
$leads = isset($leads) ? $leads : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';

// 🔥 ESTADOS PERMITIDOS
$estadosPermitidos = isset($estadosPermitidos) ? $estadosPermitidos : ['Nuevo', 'Contactado', 'Procesando', 'Cancelado'];

// 🔥 Porcentajes
$porcentajes = isset($porcentajes) ? $porcentajes : [
    'nuevo' => 0, 'contactado' => 0, 'procesando' => 0, 'cancelado' => 0,
];

// 🔥 Variables de métricas (basadas en los estados reales)
$contactados = isset($contactados) ? $contactados : 0;

$metricas = [
    [
        'class' => 'primary',
        'icon' => 'users',
        'label' => 'Total Leads',
        'value' => $totalLeads ?? 0,
        'porcentaje' => null,
    ],
    [
        'class' => 'success',
        'icon' => 'plus-circle',
        'label' => 'Nuevo',
        'value' => $nuevosMes ?? 0,
        'porcentaje' => $porcentajes['nuevo'] ?? 0,
    ],
    [
        'class' => 'info',
        'icon' => 'phone',
        'label' => 'Contactado',
        'value' => $contactados,
        'porcentaje' => $porcentajes['contactado'] ?? 0,
    ],
    [
        'class' => 'warning',
        'icon' => 'spinner',
        'label' => 'Procesando',
        'value' => $enProceso ?? 0,
        'porcentaje' => $porcentajes['procesando'] ?? 0,
    ],
    [
        'class' => 'danger',
        'icon' => 'times-circle',
        'label' => 'Cancelado',
        'value' => $perdidos ?? 0,
        'porcentaje' => $porcentajes['cancelado'] ?? 0,
    ],
];

// 🔥 Configuración del embudo
$embudo = isset($embudo) ? $embudo : ['nuevo' => 0, 'contactado' => 0, 'calificado' => 0, 'ganado' => 0, 'total' => 0];

$etapas = [
    [
        'label' => 'Nuevo',
        'count' => $embudo['nuevo'] ?? 0,
        'color' => '#007BFF',
        'icon' => 'fa-plus-circle',
        'width' => 80,
        'descripcion' => 'Leads recién ingresados',
        'border_size' => '14px',
    ],
    [
        'label' => 'Contactado',
        'count' => $embudo['contactado'] ?? 0,
        'color' => '#14aa00',
        'icon' => 'fa-phone',
        'width' => 68,
        'descripcion' => 'Leads con contacto inicial',
        'border_size' => '12px',
    ],
    [
        'label' => 'Procesando',
        'count' => $enProceso ?? 0,
        'color' => '#F97316',
        'icon' => 'fa-spinner',
        'width' => 56,
        'descripcion' => 'Leads en proceso',
        'border_size' => '10px',
    ],
    [
        'label' => 'Cancelado',
        'count' => $perdidos ?? 0,
        'color' => '#dc3545',
        'icon' => 'fa-times-circle',
        'width' => 44,
        'descripcion' => 'Leads cancelados',
        'border_size' => '8px',
    ],
];

// Variables de actividades
$actividadesRecientes = isset($actividadesRecientes) ? $actividadesRecientes : [];
$proximasActividades = isset($proximasActividades) ? $proximasActividades : [];
?>

<div class="lead-index">
        
    <!-- HEADER -->
    <div class="leads-header">
        <div>
            <div class="breadcrumb-custom">
                <span>CRM</span><span class="separator">›</span>
                <span>Contactos</span><span class="separator">›</span>
                <span class="current">Leads</span>
            </div>
            <h1 class="page-title"><?= Html::encode($this->title) ?> <small><?= date('d/m/Y H:i') ?></small></h1>
        </div>
        <div class="header-actions">
            <?php if ($isAdmin): ?>
                <?= Html::a('<i class="fas fa-trash"></i> Papelera', ['trash'], ['class' => 'btn btn-outline-danger btn-sm btn-header-action']) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-plus"></i> Nuevo Lead', ['create'], ['class' => 'btn btn-primary btn-sm btn-header-action']) ?>
        </div>
    </div>

    <!-- MÉTRICAS -->
    <div class="row g-2 mb-2">
        <?php foreach ($metricas as $metrica): ?>
            <div class="col-xl col-md-4 col-6">
                <div class="card stat-card stat-card-<?= $metrica['class'] ?> dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-<?= $metrica['icon'] ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $metrica['value'] ?></div>
                            <div class="stat-label"><?= $metrica['label'] ?></div>
                            <?php if (isset($metrica['porcentaje']) && $metrica['porcentaje'] !== null): ?>
                                <div class="stat-percent" style="font-size: 0.7rem; color: #6c757d; margin-top: 2px;">
                                    <i class="fas fa-chart-line" style="font-size: 0.6rem;"></i>
                                    <?= number_format($metrica['porcentaje'], 1) ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MENSAJE PARA AGENTES -->
    <?php if ($isAgent && empty($leads)): ?>
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> <strong>No tienes leads asignados.</strong> Tu administrador te asignará leads para que puedas gestionarlos.</div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="card filtros-card mb-3">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['lead/index']) ?>" id="form-filtros">
                <div class="row align-items-end">
                    <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?= Html::encode($search) ?>" id="search-input">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status" id="status-select">
                            <option value="">Todos los estados</option>
                            <?php 
                            foreach ($statusList as $id => $nombre): 
                                if (!in_array($nombre, $estadosPermitidos)) continue;
                            ?>
                                <option value="<?= $nombre ?>" <?= $status == $nombre ? 'selected' : '' ?>><?= ucfirst($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="fecha_inicio" value="<?= Html::encode($fecha_inicio) ?>" id="fecha-inicio">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="fecha_fin" value="<?= Html::encode($fecha_fin) ?>" id="fecha-fin">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100" id="btn-filtrar"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="<?= Url::to(['lead/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL: TABLA + PANEL -->
    <div class="panel-container">
        <!-- Tabla -->
        <div class="table-wrapper" id="tableWrapper">
            <div class="card table-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-list"></i>
                        <span>Listado de Leads</span>
                        <span class="badge bg-primary ms-2"><?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?></span>
                    </div>
                    <div class="header-right">
                        <span class="badge bg-secondary">
                            Página <?= $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1 ?> de <?= $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1 ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="leads-table">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $dataProvider ? $dataProvider->getSort()->link('name') : 'Nombre' ?></th>
                                    <th>Teléfono</th>
                                    <th><?= $dataProvider ? $dataProvider->getSort()->link('id_status') : 'Estado' ?></th>
                                    <th><?= $dataProvider ? $dataProvider->getSort()->link('created_at') : 'Fecha Registro' ?></th>
                                    <th>Observaciones</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leads)): ?>
                                    <?php foreach ($leads as $lead): ?>
                                        <tr class="lead-row" data-id="<?= $lead->id_lead ?>">
                                            <td><strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span><?= $lead->phone ?></span>
                                                    <?php if ($lead->phone): ?>
                                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead->phone) ?>" target="_blank" class="text-success" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-<?= $lead->getStatusBadgeClass() ?>"><?= $lead->getStatusName() ?></span></td>
                                            <td><?= date('d/m/Y', strtotime($lead->created_at)) ?></td>
                                            <td><?= StringHelper::truncate(Html::encode($lead->comments), 50, '...') ?></td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button class="btn btn-info btn-sm btn-action view-lead-btn" data-id="<?= $lead->id_lead ?>" title="Ver"><i class="fas fa-eye"></i></button>
                                                    <?php if ($isAdmin || ($isAgent && $lead->id_user == Yii::$app->user->id)): ?>
                                                        <button class="btn btn-primary btn-sm btn-action edit-lead-btn" 
                                                                data-id="<?= $lead->id_lead ?>" 
                                                                title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($isAdmin && !$lead->isDeleted()): ?>
                                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $lead->id_lead], [
                                                            'class' => 'btn btn-danger btn-sm btn-action',
                                                            'title' => 'Eliminar',
                                                            'data' => [
                                                                'confirm' => '¿Mover este lead a la papelera?',
                                                                'method' => 'post',
                                                            ],
                                                        ]) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                            <?= $isAgent ? 'No tienes leads asignados' : 'No hay leads registrados' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Mostrando <?= $dataProvider ? $dataProvider->getCount() : 0 ?> de <?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?> leads
                            </small>
                        </div>
                        <div class="col-md-6">
                            <?php if ($dataProvider): ?>
                                <?= LinkPager::widget([
                                    'pagination' => $dataProvider->getPagination(),
                                    'options' => ['class' => 'pagination justify-content-end'],
                                    'linkOptions' => ['class' => 'page-link'],
                                    'prevPageLabel' => 'Anterior',
                                    'nextPageLabel' => 'Siguiente',
                                    'maxButtonCount' => 5,
                                    'hideOnSinglePage' => true,
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ACTIVIDADES + EMBUDO debajo de la tabla -->
            <div class="row g-2 mt-3 bottom-cards">
                <div class="col-md-4">
                    <div class="card activities-card">
                        <div class="card-header">
                            <div class="header-left">
                                <i class="fas fa-history text-primary"></i>
                                <span>Actividades Recientes</span>
                            </div>
                            <span class="badge bg-primary"><?= count($actividadesRecientes) ?></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="activities-list">
                                <?php if (!empty($actividadesRecientes)): ?>
                                    <?php foreach ($actividadesRecientes as $actividad): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon"><i class="fas fa-<?= $actividad['icon'] ?? 'circle' ?> text-<?= $actividad['color'] ?? 'secondary' ?>"></i></div>
                                            <div class="activity-content">
                                                <div class="activity-title"><strong><?= Html::encode($actividad['lead_name'] ?? 'Lead') ?></strong> <span class="badge bg-<?= $actividad['badge_color'] ?? 'secondary' ?>"><?= $actividad['status'] ?? 'Sin estado' ?></span></div>
                                                <div class="activity-description"><?= Html::encode($actividad['description'] ?? 'Actividad registrada') ?></div>
                                                <div class="activity-meta"><span class="activity-date"><i class="far fa-calendar-alt"></i> <?= isset($actividad['date']) ? date('d/m/Y H:i', strtotime($actividad['date'])) : '' ?></span></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="activity-empty"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i><p class="text-muted">No hay actividades recientes</p></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card activities-card">
                        <div class="card-header">
                            <div class="header-left">
                                <i class="fas fa-calendar-check text-success"></i>
                                <span>Próximas Actividades</span>
                            </div>
                            <span class="badge bg-success"><?= count($proximasActividades) ?></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="activities-list">
                                <?php if (!empty($proximasActividades)): ?>
                                    <?php foreach ($proximasActividades as $actividad): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon"><i class="fas fa-<?= $actividad['icon'] ?? 'calendar-plus' ?> text-<?= $actividad['color'] ?? 'success' ?>"></i></div>
                                            <div class="activity-content">
                                                <div class="activity-title"><strong><?= Html::encode($actividad['lead_name'] ?? 'Lead') ?></strong> <span class="badge bg-<?= $actividad['badge_color'] ?? 'warning' ?>"><?= $actividad['status'] ?? 'Pendiente' ?></span></div>
                                                <div class="activity-description"><?= Html::encode($actividad['description'] ?? 'Actividad programada') ?></div>
                                                <div class="activity-meta"><span class="activity-date text-warning"><i class="far fa-clock"></i> <?= isset($actividad['date']) ? date('d/m/Y H:i', strtotime($actividad['date'])) : '' ?></span></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="activity-empty"><i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i><p class="text-muted">No hay próximas actividades</p></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card funnel-card dashboard-card">
                        <div class="card-header">
                            <div class="header-left">
                                <i class="fas fa-filter text-primary"></i>
                                <span>Leads por Etapas</span>
                            </div>
                            <span class="badge bg-info text-white"><?= $embudo['total'] ?? 0 ?></span>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div class="funnel-container-trapezoid w-100">
                                <?php foreach ($etapas as $index => $etapa): ?>
                                    <div class="funnel-step-trapezoid">
                                        <div class="funnel-block-trapezoid" style="width: <?= $etapa['width'] ?>%; background: <?= $etapa['color'] ?>; border-left: <?= $etapa['border_size'] ?> solid transparent; border-right: <?= $etapa['border_size'] ?> solid transparent; border-top: 0px solid transparent;">
                                            <div class="funnel-block-content-trapezoid">
                                                <div class="funnel-label-trapezoid">
                                                    <i class="fas <?= $etapa['icon'] ?>"></i>
                                                    <span class="funnel-label-text"><?= $etapa['label'] ?></span>
                                                </div>
                                                <div class="funnel-stats-trapezoid">
                                                    <span class="funnel-count-trapezoid"><?= $etapa['count'] ?></span>
                                                </div>
                                            </div>
                                            <div class="funnel-tooltip-trapezoid">
                                                <i class="fas <?= $etapa['icon'] ?>"></i> <?= $etapa['label'] ?>: <?= $etapa['count'] ?> leads<br>
                                                <small><?= $etapa['descripcion'] ?></small>
                                            </div>
                                        </div>
                                        <?php if ($index < count($etapas) - 1): ?>
                                            <div class="funnel-connector-trapezoid"><span>▾</span></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel Lateral -->
        <div class="panel-wrapper" id="panelWrapper">
            <div class="card slide-panel-card">
                <div class="card-body p-0" id="slidePanelContent"></div>
            </div>
        </div>
    </div>
</div>

<?php
$viewModalUrl = Url::to(['lead/view-modal']);
?>

<script>
// ============================================
// ESPERAR A QUE JQUERY ESTÉ CARGADO
// ============================================
(function() {
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery no está cargado. Intentando cargar...');
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = function() {
            console.log('✅ jQuery cargado manualmente');
            inicializar();
        };
        document.head.appendChild(script);
    } else {
        console.log('✅ jQuery ya está cargado');
        inicializar();
    }
})();

function inicializar() {
    var $ = jQuery;
    
    console.log('🚀 Inicializando panel lateral...');
    
    // ============================================
    // DELEGACIÓN DE EVENTOS
    // ============================================
    $(document).on('click', '.view-lead-btn', function(e) {
        e.stopPropagation();
        var leadId = $(this).data('id');
        if (leadId) openPanel(leadId);
    });

    $(document).on('click', '.lead-row', function(e) {
        if ($(e.target).closest('a, button').length > 0) return;
        var leadId = $(this).data('id');
        if (leadId) openPanel(leadId);
    });

    $(document).on('click', '.edit-lead-btn', function(e) {
        e.stopPropagation();
        var leadId = $(this).data('id');
        if (leadId) {
            if (isOpen) {
                closePanel(function() {
                    setTimeout(function() {
                        openEditPanel(leadId);
                    }, 300);
                });
            } else {
                openEditPanel(leadId);
            }
        }
    });

    // ============================================
    // FILTROS AUTOMÁTICOS
    // ============================================
    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') document.getElementById('form-filtros').submit();
        });
    }
    
    var statusSelect = document.getElementById('status-select');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            document.getElementById('form-filtros').submit();
        });
    }
    
    var fechaInicio = document.getElementById('fecha-inicio');
    if (fechaInicio) {
        fechaInicio.addEventListener('change', function() {
            document.getElementById('form-filtros').submit();
        });
    }
    
    var fechaFin = document.getElementById('fecha-fin');
    if (fechaFin) {
        fechaFin.addEventListener('change', function() {
            document.getElementById('form-filtros').submit();
        });
    }
    
    var btnFiltrar = document.getElementById('btn-filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('form-filtros').submit();
        });
    }

    // ============================================
    // PANEL LATERAL
    // ============================================
    var currentLeadId = null;
    var viewModalUrl = '<?= $viewModalUrl ?>';
    var isOpen = false;

    function openPanel(leadId) {
        if (leadId === currentLeadId && isOpen) {
            closePanel();
            return;
        }
        
        if (isOpen) {
            closePanel(function() {
                setTimeout(function() {
                    openPanel(leadId);
                }, 300);
            });
            return;
        }
        
        currentLeadId = leadId;
        isOpen = true;
        
        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var filaSeleccionada = document.querySelector('.lead-row[data-id="' + leadId + '"]');
        
        if (!panel || !tableWrapper || !panelContent) {
            console.error('❌ Elementos no encontrados');
            return;
        }
        
        document.querySelectorAll('.lead-row').forEach(function(row) {
            row.classList.remove('lead-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('lead-row-selected');
        }
        
        tableWrapper.classList.add('with-panel');
        
        panel.style.display = 'block';
        panel.classList.add('visible');
        
        panelContent.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Cargando...</p></div>';
        
        fetch(viewModalUrl + '?id=' + leadId)
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(data) {
                panelContent.innerHTML = data;
                console.log('✅ Contenido cargado');
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button></div>';
            });
    }

    function openEditPanel(leadId) {
        currentLeadId = leadId;
        isOpen = true;
        
        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var filaSeleccionada = document.querySelector('.lead-row[data-id="' + leadId + '"]');
        
        if (!panel || !tableWrapper || !panelContent) {
            console.error('❌ Elementos no encontrados');
            return;
        }
        
        document.querySelectorAll('.lead-row').forEach(function(row) {
            row.classList.remove('lead-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('lead-row-selected');
        }
        
        tableWrapper.classList.add('with-panel');
        
        panel.style.display = 'block';
        panel.classList.add('visible');
        
        panelContent.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Cargando formulario de edición...</p></div>';
        
        fetch('<?= Url::to(['lead/update']) ?>?id=' + leadId + '&modal=1')
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(data) {
                panelContent.innerHTML = data;
                console.log('✅ Formulario de edición cargado');
                
                var scripts = panelContent.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    var script = document.createElement('script');
                    script.text = scripts[i].text;
                    document.body.appendChild(script);
                }
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar el formulario</p><button class="btn btn-secondary btn-sm mt-3" onclick="closePanel()">Cerrar</button></div>';
            });
    }

    function closePanel(callback) {
        var panel = document.getElementById('panelWrapper');
        var tableWrapper = document.getElementById('tableWrapper');
        
        if (!panel || !tableWrapper) {
            if (callback) callback();
            return;
        }
        
        isOpen = false;
        
        document.querySelectorAll('.lead-row').forEach(function(row) {
            row.classList.remove('lead-row-selected');
        });
        
        panel.classList.remove('visible');
        panel.classList.add('closing');
        tableWrapper.classList.remove('with-panel');
        
        setTimeout(function() {
            panel.style.display = 'none';
            panel.classList.remove('closing');
            var contenido = document.getElementById('slidePanelContent');
            if (contenido) {
                contenido.innerHTML = '';
            }
            currentLeadId = null;
            if (callback) callback();
        }, 300);
    }

    window.openPanel = openPanel;
    window.openEditPanel = openEditPanel;
    window.closePanel = closePanel;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closePanel();
        }
    });

    console.log('✅ Panel lateral inicializado correctamente');
}
</script>