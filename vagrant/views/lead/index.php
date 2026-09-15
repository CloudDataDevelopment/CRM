<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

$this->title = 'Leads';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS del leads
$this->registerCssFile('@web/css/leads.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

// Registrar CSS del panel lateral
$this->registerCssFile('@web/css/lead-panel.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

// Registrar DataTable CSS
$this->registerCssFile('https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css');

// Registrar jQuery
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', [
    'position' => \yii\web\View::POS_HEAD,
]);

$this->registerJsFile('https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

$this->registerJsFile('https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END,
]);

// Variables del controlador
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$statusList = isset($statusList) ? $statusList : [];
$leads = isset($leads) ? $leads : [];
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';

// Variables de métricas
$totalLeads = isset($totalLeads) ? $totalLeads : 0;
$nuevosMes = isset($nuevosMes) ? $nuevosMes : 0;
$enProceso = isset($enProceso) ? $enProceso : 0;
$calificados = isset($calificados) ? $calificados : 0;
$convertidos = isset($convertidos) ? $convertidos : 0;
$perdidos = isset($perdidos) ? $perdidos : 0;

// Variables de actividades
$actividadesRecientes = isset($actividadesRecientes) ? $actividadesRecientes : [];
$proximasActividades = isset($proximasActividades) ? $proximasActividades : [];
?>

<div class="lead-index">
    <!-- HEADER CON BREADCRUMBS -->
    <div class="leads-header">
        <div>
            <!-- Ruta de navegación -->
            <div class="breadcrumb-custom">
                <span>CRM</span>
                <span class="separator">›</span>
                <span>Contactos</span>
                <span class="separator">›</span>
                <span class="current">Leads</span>
            </div>
            <!-- Título principal -->
            <h1 class="page-title">
                <?= Html::encode($this->title) ?>
                <small><?= date('d/m/Y H:i') ?></small>
            </h1>
        </div>
        <div class="header-actions">
            <?php if ($isAdmin): ?>
                <?= Html::a('<i class="fas fa-trash"></i> Papelera', ['trash'], [
                    'class' => 'btn btn-warning btn-sm'
                ]) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-plus"></i> Nuevo Lead', ['create'], [
                'class' => 'btn btn-success btn-sm'
            ]) ?>
        </div>
    </div>

    <!-- TARJETAS DE MÉTRICAS -->
    <div class="row g-2 mb-3">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card stat-card-primary dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $totalLeads ?></div>
                        <div class="stat-label">Total Leads</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card stat-card-success dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $nuevosMes ?></div>
                        <div class="stat-label">Nuevos este mes</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card stat-card-warning dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $enProceso ?></div>
                        <div class="stat-label">En Proceso</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card stat-card-info dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $calificados ?></div>
                        <div class="stat-label">Calificados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card stat-card-purple dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $convertidos ?></div>
                        <div class="stat-label">Convertidos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card stat-card-danger dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $perdidos ?></div>
                        <div class="stat-label">Perdidos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MENSAJE PARA AGENTES SIN LEADS -->
    <?php if ($isAgent && empty($leads)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>No tienes leads asignados.</strong>
            Tu administrador te asignará leads para que puedas gestionarlos.
        </div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="card filtros-card mb-3">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['lead/index']) ?>" id="form-filtros">
                <div class="row align-items-end">
                    <div class="col-md-1">
                        <label class="form-label fw-bold mb-0">Filtrar</label>
                    </div>

                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Buscar por nombre, apellido, teléfono o comentarios..." 
                               value="<?= Html::encode($search) ?>" id="search-input">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" name="status" id="status-select">
                            <option value="">Todos los estados</option>
                            <?php foreach ($statusList as $id => $nombre): ?>
                                <option value="<?= $nombre ?>" <?= $status == $nombre ? 'selected' : '' ?>>
                                    <?= ucfirst($nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="<?= Html::encode($fecha_inicio) ?>" id="fecha-inicio">
                    </div>

                    <div class="col-md-2">
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="<?= Html::encode($fecha_fin) ?>" id="fecha-fin">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100" id="btn-filtrar">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="col-md-1">
                        <a href="<?= Url::to(['lead/index']) ?>" class="btn btn-secondary w-100" title="Limpiar filtros">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL: TABLA + PANEL LATERAL -->
    <div class="row g-0 main-container">
        <!-- Tabla de Leads -->
        <div class="col-12" id="tableContainer">
            <div class="card table-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <!-- 🔥 QUITADA LA CLASE table-striped -->
                        <table class="table table-hover mb-0" id="leads-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Observaciones</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leads)): ?>
                                    <?php foreach ($leads as $lead): ?>
                                        <tr class="lead-row" data-id="<?= $lead->id_lead ?>">
                                            <td>
                                                <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span><?= $lead->phone ?></span>
                                                    <?php if ($lead->phone): ?>
                                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead->phone) ?>" 
                                                           target="_blank" 
                                                           class="text-success" 
                                                           title="WhatsApp"
                                                           style="font-size: 0.9rem;">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = $lead->getStatusBadgeClass();
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= $lead->getStatusName() ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($lead->created_at)) ?></td>
                                            <td>
                                                <?php 
                                                $comments = Html::encode($lead->comments);
                                                echo StringHelper::truncate($comments, 50, '...');
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button class="btn btn-info btn-sm btn-action view-lead-btn" 
                                                            data-id="<?= $lead->id_lead ?>"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($isAdmin): ?>
                                                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $lead->id_lead, 'return' => 'index'], [
                                                            'class' => 'btn btn-primary btn-sm btn-action',
                                                            'title' => 'Editar'
                                                        ]) ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($isAgent && $lead->id_user == Yii::$app->user->id): ?>
                                                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $lead->id_lead, 'return' => 'index'], [
                                                            'class' => 'btn btn-primary btn-sm btn-action',
                                                            'title' => 'Editar'
                                                        ]) ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($isAdmin && !$lead->isDeleted()): ?>
                                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $lead->id_lead], [
                                                            'class' => 'btn btn-danger btn-sm btn-action',
                                                            'title' => 'Mover a papelera',
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
                                            <?php if ($isAgent): ?>
                                                No tienes leads asignados
                                            <?php else: ?>
                                                No hay leads registrados
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral de Detalle -->
        <div class="col-12 col-lg-4" id="slidePanel" style="display: none; padding-left: 15px;">
            <div class="card slide-panel-card">
                <div class="card-body p-0" id="slidePanelContent">
                    <!-- Contenido vacío por defecto -->
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIVIDADES RECIENTES Y PRÓXIMAS -->
    <div class="row g-2 mt-3">
        <div class="col-md-6">
            <div class="card activities-card">
                <div class="card-header">
                    <i class="fas fa-history text-primary me-2"></i> 
                    Actividades Recientes
                    <span class="badge bg-primary ms-2"><?= count($actividadesRecientes) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="activities-list">
                        <?php if (!empty($actividadesRecientes)): ?>
                            <?php foreach ($actividadesRecientes as $actividad): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-<?= $actividad['icon'] ?? 'circle' ?> text-<?= $actividad['color'] ?? 'secondary' ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <strong><?= Html::encode($actividad['lead_name'] ?? 'Lead') ?></strong>
                                            <span class="activity-status">
                                                <span class="badge bg-<?= $actividad['badge_color'] ?? 'secondary' ?>">
                                                    <?= $actividad['status'] ?? 'Sin estado' ?>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="activity-description">
                                            <?= Html::encode($actividad['description'] ?? 'Actividad registrada') ?>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="activity-date">
                                                <i class="far fa-calendar-alt"></i> 
                                                <?= isset($actividad['date']) ? date('d/m/Y H:i', strtotime($actividad['date'])) : '' ?>
                                            </span>
                                            <?php if (isset($actividad['user_name']) && $actividad['user_name']): ?>
                                                <span class="activity-user">
                                                    <i class="fas fa-user"></i> 
                                                    <?= Html::encode($actividad['user_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-empty">
                                <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                <p class="text-muted">No hay actividades recientes</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card activities-card">
                <div class="card-header">
                    <i class="fas fa-calendar-check text-success me-2"></i> 
                    Próximas Actividades
                    <span class="badge bg-success ms-2"><?= count($proximasActividades) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="activities-list">
                        <?php if (!empty($proximasActividades)): ?>
                            <?php foreach ($proximasActividades as $actividad): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-<?= $actividad['icon'] ?? 'calendar-plus' ?> text-<?= $actividad['color'] ?? 'success' ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <strong><?= Html::encode($actividad['lead_name'] ?? 'Lead') ?></strong>
                                            <span class="activity-status">
                                                <span class="badge bg-<?= $actividad['badge_color'] ?? 'warning' ?>">
                                                    <?= $actividad['status'] ?? 'Pendiente' ?>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="activity-description">
                                            <?= Html::encode($actividad['description'] ?? 'Actividad programada') ?>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="activity-date text-warning">
                                                <i class="far fa-clock"></i> 
                                                <?= isset($actividad['date']) ? date('d/m/Y H:i', strtotime($actividad['date'])) : '' ?>
                                            </span>
                                            <?php if (isset($actividad['user_name']) && $actividad['user_name']): ?>
                                                <span class="activity-user">
                                                    <i class="fas fa-user"></i> 
                                                    <?= Html::encode($actividad['user_name']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-empty">
                                <i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i>
                                <p class="text-muted">No hay próximas actividades</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// ============================================
// JAVASCRIPT PARA FILTROS, DATATABLE Y PANEL LATERAL
// ============================================
$viewModalUrl = Url::to(['lead/view-modal']);
?>

<script>
// ============================================
// ESPERAR A QUE JQUERY ESTÉ CARGADO
// ============================================
(function() {
    // Verificar si jQuery está cargado
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
    // INICIALIZAR DATATABLE
    // ============================================
    $(document).ready(function() {
        var table = $('#leads-table');
        if (table.find('tbody tr').length > 0 && table.find('tbody tr td[colspan]').length === 0) {
            try {
                var dataTable = table.DataTable({
                    "pageLength": 10,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": 5 },
                        { "className": "text-center", "targets": 5 }
                    ],
                    "order": [[3, 'desc']]
                });
                console.log('✅ DataTable inicializado correctamente');
            } catch(e) {
                console.error('❌ Error al inicializar DataTable:', e);
            }
        }
    });

    // ============================================
    // DELEGACIÓN DE EVENTOS
    // ============================================
    $(document).on('click', '.view-lead-btn', function(e) {
        e.stopPropagation();
        var leadId = $(this).data('id');
        console.log('🔍 Click en ver lead:', leadId);
        if (leadId) {
            loadLeadDetail(leadId);
        }
    });

    $(document).on('click', '.lead-row', function(e) {
        if ($(e.target).closest('a, button').length > 0) {
            return;
        }
        var leadId = $(this).data('id');
        console.log('🔍 Click en fila lead:', leadId);
        if (leadId) {
            loadLeadDetail(leadId);
        }
    });

    // ============================================
    // FILTROS AUTOMÁTICOS
    // ============================================
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('form-filtros').submit();
        }
    });

    document.getElementById('status-select').addEventListener('change', function() {
        document.getElementById('form-filtros').submit();
    });

    document.getElementById('fecha-inicio').addEventListener('change', function() {
        document.getElementById('form-filtros').submit();
    });

    document.getElementById('fecha-fin').addEventListener('change', function() {
        document.getElementById('form-filtros').submit();
    });

    document.getElementById('btn-filtrar').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('form-filtros').submit();
    });

    // ============================================
    // PANEL LATERAL - VER LEAD
    // ============================================
    var currentLeadId = null;
    var viewModalUrl = '<?= $viewModalUrl ?>';

    function loadLeadDetail(leadId) {
        if (leadId === currentLeadId) {
            closeSlidePanel();
            return;
        }
        
        currentLeadId = leadId;
        
        var panel = document.getElementById('slidePanel');
        var contenedor = document.getElementById('tableContainer');
        var contenido = document.getElementById('slidePanelContent');
        
        if (!panel || !contenedor || !contenido) {
            console.error('❌ Elementos del panel no encontrados');
            return;
        }
        
        console.log('📦 Abriendo panel para lead:', leadId);
        
        panel.style.display = 'block';
        panel.style.flexDirection = 'column';
        
        contenedor.className = 'col-12 col-lg-7';
        contenedor.style.display = 'flex';
        contenedor.style.flexDirection = 'column';
        contenedor.style.transition = 'all 0.3s ease';
        
        contenido.innerHTML = '<div class="text-center text-muted py-5" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-3">Cargando información del lead...</p></div>';
        contenido.style.display = 'flex';
        contenido.style.flexDirection = 'column';
        contenido.style.height = '100%';
        
        var url = viewModalUrl + '?id=' + leadId;
        console.log('🌐 Cargando URL:', url);
        
        fetch(url)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.text();
            })
            .then(function(data) {
                console.log('✅ Contenido cargado correctamente');
                contenido.innerHTML = data;
                contenido.style.display = 'flex';
                contenido.style.flexDirection = 'column';
                contenido.style.height = '100%';
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                contenido.innerHTML = '<div class="text-center text-danger py-5" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;"><i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i><p>Error al cargar la información del lead</p><small>' + error.message + '</small><button class="btn btn-secondary btn-sm mt-3" onclick="closeSlidePanel()">Cerrar</button></div>';
            });
    }

    window.loadLeadDetail = loadLeadDetail;

    function closeSlidePanel() {
        console.log('🔒 Cerrando panel lateral');
        currentLeadId = null;
        
        var panel = document.getElementById('slidePanel');
        var contenedor = document.getElementById('tableContainer');
        var contenido = document.getElementById('slidePanelContent');
        
        if (panel) {
            panel.style.display = 'none';
        }
        
        if (contenedor) {
            contenedor.className = 'col-12';
            contenedor.style.display = 'flex';
            contenedor.style.flexDirection = 'column';
            contenedor.style.transition = 'all 0.3s ease';
        }
        
        if (contenido) {
            contenido.innerHTML = '';
            contenido.style.display = 'flex';
            contenido.style.flexDirection = 'column';
            contenido.style.height = '100%';
        }
    }

    window.closeSlidePanel = closeSlidePanel;

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSlidePanel();
        }
    });

    console.log('✅ Panel lateral inicializado correctamente');
}
</script>