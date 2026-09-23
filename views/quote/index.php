<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Cotizaciones';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/quote-panel.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

// 🔥 CSS del modal de edición (necesario para el panel lateral AJAX)
$editModalCssPath = Yii::getAlias('@webroot/css/quote-edit-modal.css');
$editModalCssVersion = file_exists($editModalCssPath) ? filemtime($editModalCssPath) : time();
$this->registerCssFile('@web/css/quote-edit-modal.css?v=' . $editModalCssVersion, [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);

// Registrar JS
$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

// Variables del controlador
$quotes = isset($quotes) ? $quotes : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$totalQuotes = isset($totalQuotes) ? $totalQuotes : 0;
$totalPendientes = isset($totalPendientes) ? $totalPendientes : 0;
$totalCompletados = isset($totalCompletados) ? $totalCompletados : 0;
$totalCancelados = isset($totalCancelados) ? $totalCancelados : 0;
$montoCompletados = isset($montoCompletados) ? $montoCompletados : 0;
$montoCancelados = isset($montoCancelados) ? $montoCancelados : 0;
$totalMonto = isset($totalMonto) ? $totalMonto : 0;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$statusOptions = isset($statusOptions) ? $statusOptions : [];
$statusCounts = isset($statusCounts) ? $statusCounts : [];
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';

$hasData = !empty($quotes);
$totalCount = $dataProvider ? $dataProvider->getTotalCount() : 0;
$currentCount = $dataProvider ? $dataProvider->getCount() : 0;
$pageCount = $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1;
$currentPage = $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1;
?>

<div class="quote-container">
    <div class="quote-index">
        
        <!-- HEADER -->
        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span class="current">Cotizaciones</span>
                </div>
                <h1 class="page-title"><?= Html::encode($this->title) ?> <small><?= date('d/m/Y H:i') ?></small></h1>
            </div>
            <div class="header-actions">
                <?= $this->render('/layouts/_report_button') ?>
                
                <?php if ($isAdmin || $isSuperAdmin): ?>
                    <?= Html::a(
                        '<i class="fas fa-trash"></i> Papelera',
                        ['trash'],
                        ['class' => 'btn btn-outline-danger btn-sm']
                    ) ?>
                <?php endif; ?>
                
                <?= Html::a('<i class="fas fa-plus"></i> Nueva Cotización', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <!-- 🔥 MÉTRICAS -->
        <div class="row g-2 mb-2">
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-file-invoice"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalQuotes ?></div>
                            <div class="stat-label">Total Cotizaciones</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-clock"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalPendientes ?></div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-check-circle"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalCompletados ?></div>
                            <div class="stat-label">Completados</div>
                            <div class="stat-label text-success" style="font-size: 0.45rem;">$<?= number_format($montoCompletados, 0, '.', ',') ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-times-circle"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalCancelados ?></div>
                            <div class="stat-label">Cancelados</div>
                            <div class="stat-label text-danger" style="font-size: 0.45rem;">$<?= number_format($montoCancelados, 0, '.', ',') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['quote/index']) ?>" id="form-filtros">
                    <div class="row align-items-end">
                        <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                        <div class="col-md-3"><input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?= Html::encode($search) ?>" id="search-input"></div>
                        <div class="col-md-2">
                            <select class="form-select" name="status" id="status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($statusOptions as $id => $nombre): ?>
                                    <option value="<?= $nombre ?>" <?= $status == $nombre ? 'selected' : '' ?>><?= ucfirst($nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="date" class="form-control" name="fecha_inicio" value="<?= Html::encode($fecha_inicio) ?>" id="fecha-inicio"></div>
                        <div class="col-md-2"><input type="date" class="form-control" name="fecha_fin" value="<?= Html::encode($fecha_fin) ?>" id="fecha-fin"></div>
                        <div class="col-md-1"><button type="submit" class="btn btn-primary w-100" id="btn-filtrar"><i class="fas fa-search"></i></button></div>
                        <div class="col-md-1"><a href="<?= Url::to(['quote/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a></div>
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
                            <span>Listado de Cotizaciones</span>
                            <span class="badge bg-primary ms-2"><?= $totalQuotes ?></span>
                            <span class="badge bg-warning ms-1">Pendientes: <?= $totalPendientes ?></span>
                            <span class="badge bg-success ms-1">Completados: <?= $totalCompletados ?></span>
                            <?php if ($totalCancelados > 0): ?>
                                <span class="badge bg-danger ms-1">Cancelados: <?= $totalCancelados ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="header-right">
                            <span class="badge bg-secondary">
                                Página <?= $currentPage ?> de <?= $pageCount ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="quotes-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($hasData): ?>
                                        <?php 
                                        $counter = 0;
                                        foreach ($quotes as $quote): 
                                            $counter++;
                                            $statusName = $quote->getStatusName();
                                            $badgeClass = $quote->getStatusBadgeClass();
                                        ?>
                                            <tr class="quote-row" data-id="<?= $quote->id_quote ?>">
                                                <td><?= $counter ?></td>
                                                <td>
                                                    <strong><?= $quote->lead ? Html::encode($quote->lead->name . ' ' . $quote->lead->lastname) : 'Lead no disponible' ?></strong>
                                                    <?php if ($quote->lead): ?>
                                                        <br><small class="text-muted"><i class="fas fa-phone"></i> <?= $quote->lead->phone ?? 'Sin teléfono' ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($quote->date_quote)) ?></td>
                                                <td><strong>$<?= number_format($quote->total_amount ?? 0, 0, '.', ',') ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?= $badgeClass ?>"><?= $statusName ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button class="btn btn-info btn-sm btn-action view-quote-btn" data-id="<?= $quote->id_quote ?>" title="Ver"><i class="fas fa-eye"></i></button>
                                                        <?php if ($isAdmin || ($isAgent && $quote->lead && $quote->lead->id_user == Yii::$app->user->id)): ?>
                                                            <button class="btn btn-primary btn-sm btn-action edit-quote-btn" data-id="<?= $quote->id_quote ?>" title="Editar"><i class="fas fa-edit"></i></button>
                                                        <?php endif; ?>
                                                        <?php if ($isAdmin || $isSuperAdmin): ?>
                                                            <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $quote->id_quote], [
                                                                'class' => 'btn btn-danger btn-sm btn-action',
                                                                'title' => 'Mover a papelera',
                                                                'data' => [
                                                                    'confirm' => '¿Mover esta cotización a la papelera?',
                                                                    'method' => 'post',
                                                                ],
                                                            ]) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay cotizaciones registradas</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Mostrando <?= $currentCount ?> de <?= $totalCount ?> cotizaciones
                                </small>
                            </div>
                            <div class="col-md-6">
                                <?php if ($dataProvider && $dataProvider->getTotalCount() > 0): ?>
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
                
                <!-- ACTIVIDADES + RESUMEN -->
                <div class="row g-2 mt-3 bottom-cards">
                    <div class="col-md-6">
                        <div class="card activities-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <i class="fas fa-history text-primary"></i>
                                    <span>Últimas Cotizaciones</span>
                                </div>
                                <span class="badge bg-primary"><?= $totalQuotes ?></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="activities-list">
                                    <?php if ($hasData): ?>
                                        <?php foreach (array_slice($quotes, 0, 5) as $quote): ?>
                                            <div class="activity-item">
                                                <div class="activity-icon"><i class="fas fa-file-invoice text-primary"></i></div>
                                                <div class="activity-content">
                                                    <div class="activity-title"><strong><?= $quote->lead ? Html::encode($quote->lead->name . ' ' . $quote->lead->lastname) : 'Lead no disponible' ?></strong> <span class="badge bg-<?= $quote->getStatusBadgeClass() ?>"><?= $quote->getStatusName() ?></span></div>
                                                    <div class="activity-description">$<?= number_format($quote->total_amount ?? 0, 0, '.', ',') ?> - <?= StringHelper::truncate(Html::encode($quote->getNotes() ?? 'Sin observaciones'), 40, '...') ?></div>
                                                    <div class="activity-meta"><span class="activity-date"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($quote->date_quote)) ?></span></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="activity-empty"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i><p class="text-muted">No hay cotizaciones recientes</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card funnel-card dashboard-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <i class="fas fa-chart-pie text-primary"></i>
                                    <span>Resumen de Cotizaciones</span>
                                </div>
                                <span class="badge bg-info text-white"><?= $totalQuotes ?></span>
                            </div>
                            <div class="card-body d-flex align-items-center">
                                <div class="w-100">
                                    <?php if (!empty($statusCounts)): 
                                        $colors = ['warning', 'success', 'danger', 'info', 'secondary', 'primary'];
                                        $i = 0;
                                        foreach ($statusCounts as $name => $count):
                                            $color = $colors[$i % count($colors)];
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                            <span><span class="badge bg-<?= $color ?>"><?= $name ?></span></span>
                                            <span><strong><?= $count ?></strong></span>
                                        </div>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $totalQuotes > 0 ? ($count / $totalQuotes) * 100 : 0 ?>%;"></div>
                                        </div>
                                    <?php $i++; endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-3"><i class="fas fa-inbox"></i> Sin datos</div>
                                    <?php endif; ?>
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
</div>

<?php
$viewModalUrl = Url::to(['quote/view-modal']);
$updateModalUrl = Url::to(['quote/update-modal']);
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
    
    console.log('🚀 Inicializando panel lateral de cotizaciones...');
    
    // ============================================
    // VARIABLES
    // ============================================
    var viewModalUrl = '<?= $viewModalUrl ?>';
    var updateModalUrl = '<?= $updateModalUrl ?>';
    var isOpen = false;
    var currentId = null;
    
    // ============================================
    // FUNCIONES
    // ============================================
    function closePanel(callback) {
        var panel = document.getElementById('panelWrapper');
        var tableWrapper = document.getElementById('tableWrapper');
        
        if (!panel || !tableWrapper) {
            if (callback) callback();
            return;
        }
        
        isOpen = false;
        
        document.querySelectorAll('.quote-row').forEach(function(row) {
            row.classList.remove('quote-row-selected');
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
            currentId = null;
            if (callback) callback();
        }, 300);
    }
    
    function openPanel(id) {
        if (id === currentId && isOpen) {
            closePanel();
            return;
        }
        
        if (isOpen) {
            closePanel(function() {
                setTimeout(function() {
                    openPanel(id);
                }, 300);
            });
            return;
        }
        
        currentId = id;
        isOpen = true;
        
        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var filaSeleccionada = document.querySelector('.quote-row[data-id="' + id + '"]');
        
        if (!panel || !tableWrapper || !panelContent) {
            console.error('❌ Elementos no encontrados');
            return;
        }
        
        document.querySelectorAll('.quote-row').forEach(function(row) {
            row.classList.remove('quote-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('quote-row-selected');
        }
        
        tableWrapper.classList.add('with-panel');
        
        panel.style.display = 'block';
        panel.classList.add('visible');
        
        panelContent.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';
        
        fetch(viewModalUrl + '?id=' + id)
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
                panelContent.innerHTML = '<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-2" onclick="closePanel()">Cerrar</button></div>';
            });
    }
    
    function openEditPanel(id) {
        if (isOpen) {
            closePanel(function() {
                setTimeout(function() {
                    openEditPanel(id);
                }, 300);
            });
            return;
        }
        
        currentId = id;
        isOpen = true;
        
        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var filaSeleccionada = document.querySelector('.quote-row[data-id="' + id + '"]');
        
        if (!panel || !tableWrapper || !panelContent) {
            console.error('❌ Elementos no encontrados');
            return;
        }
        
        document.querySelectorAll('.quote-row').forEach(function(row) {
            row.classList.remove('quote-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('quote-row-selected');
        }
        
        tableWrapper.classList.add('with-panel');
        
        panel.style.display = 'block';
        panel.classList.add('visible');
        
        panelContent.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando formulario...</p></div>';
        
        fetch(updateModalUrl + '?id=' + id)
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function(data) {
                panelContent.innerHTML = data;
                console.log('✅ Formulario cargado');
            })
            .catch(function(error) {
                console.error('❌ Error:', error);
                panelContent.innerHTML = '<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-2" onclick="closePanel()">Cerrar</button></div>';
            });
    }
    
    window.openPanel = openPanel;
    window.openEditPanel = openEditPanel;
    window.closePanel = closePanel;
    
    // ============================================
    // EVENTOS
    // ============================================
    $(document).on('click', '.view-quote-btn', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        if (id) openPanel(id);
    });
    
    $(document).on('click', '.edit-quote-btn', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        if (id) openEditPanel(id);
    });
    
    $(document).on('click', '.quote-row', function(e) {
        if ($(e.target).closest('a, button').length > 0) return;
        var id = $(this).data('id');
        if (id) openPanel(id);
    });
    
    // Tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closePanel();
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
    
    console.log('✅ Panel lateral de cotizaciones inicializado');
}
</script>