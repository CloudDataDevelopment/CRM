<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Seguimientos';
$this->params['breadcrumbs'][] = $this->title;

// ============================================
// REGISTRAR CSS
// ============================================
$this->registerCssFile('@web/css/sales-tracking.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/sales-tracking-panel.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);
$this->registerCssFile('@web/css/sales-tracking-edit-modal.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

// ============================================
// VARIABLES
// ============================================
$trackings = isset($trackings) ? $trackings : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$totalTrackings = isset($totalTrackings) ? $totalTrackings : 0;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$statusList = isset($statusList) ? $statusList : [];
$statusCounts = isset($statusCounts) ? $statusCounts : [];
$ultimosSeguimientos = isset($ultimosSeguimientos) ? $ultimosSeguimientos : [];
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';

$hasData = !empty($trackings);

$metricas = [
    ['class' => 'primary', 'icon' => 'phone', 'label' => 'Total Seguimientos', 'value' => $totalTrackings],
    ['class' => 'warning', 'icon' => 'clock', 'label' => 'Pendientes', 'value' => $statusCounts['Pendiente'] ?? 0],
    ['class' => 'info', 'icon' => 'calendar-check', 'label' => 'Programados', 'value' => $statusCounts['Programado'] ?? 0],
    ['class' => 'purple', 'icon' => 'spinner', 'label' => 'En Progreso', 'value' => $statusCounts['En Progreso'] ?? 0],
    ['class' => 'success', 'icon' => 'check-circle', 'label' => 'Completados', 'value' => $statusCounts['Completado'] ?? 0],
    ['class' => 'danger', 'icon' => 'times-circle', 'label' => 'Cancelados', 'value' => $statusCounts['Cancelado'] ?? 0],
];
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="sales-tracking-index">
    <div class="sales-tracking-wrapper">

        <!-- HEADER -->
        <div class="sales-tracking-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Ventas</span><span class="separator">›</span>
                    <span class="current">Seguimientos</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <?php if ($isAgent): ?>
                        <span class="badge-role ms-2"><i class="fas fa-user"></i> Mis Seguimientos</span>
                    <?php endif; ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= $this->render('/layouts/_report_button') ?>

                <?php if ($isAdmin || $isSuperAdmin): ?>
                    <?= Html::a(
                        '<i class="fas fa-trash"></i> Papelera',
                        ['trash'],
                        ['class' => 'btn btn-outline-danger btn-sm btn-tracking-action']
                    ) ?>
                <?php endif; ?>

                <?= Html::a('<i class="fas fa-plus"></i> Nuevo Seguimiento', ['create'], [
                    'class' => 'btn btn-primary btn-sm btn-tracking-action'
                ]) ?>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-2">
            <?php foreach ($metricas as $metrica): ?>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card tracking-stat-card tracking-stat-<?= $metrica['class'] ?> tracking-dashboard-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon me-2">
                                <i class="fas fa-<?= $metrica['icon'] ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="stat-number"><?= $metrica['value'] ?></div>
                                <div class="stat-label"><?= $metrica['label'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- FILTROS -->
        <div class="card tracking-filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['sales-tracking/index']) ?>" id="form-filtros">
                    <div class="row align-items-end">
                        <div class="col-md-1"><label class="form-label fw-bold mb-0">Filtrar</label></div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status" id="status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($statusList as $id => $nombre): ?>
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
                            <a href="<?= Url::to(['sales-tracking/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- CONTENEDOR PRINCIPAL -->
        <div class="tracking-panel-container">
            <div class="tracking-table-wrapper" id="tableWrapper">
                <div class="card tracking-table-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-list"></i>
                            <span>Listado de Seguimientos</span>
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
                            <table class="table table-hover mb-0" id="trackings-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Lead</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Comentarios</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($hasData): ?>
                                        <?php foreach ($trackings as $tracking): ?>
                                            <tr class="tracking-row" data-id="<?= $tracking->id_sales_tracking ?>">
                                                <td>
                                                    <?php if ($tracking->lead): ?>
                                                        <strong><?= Html::encode($tracking->lead->name . ' ' . $tracking->lead->lastname) ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">Lead eliminado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($tracking->lead && $tracking->lead->phone): ?>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span><?= $tracking->lead->phone ?></span>
                                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $tracking->lead->phone) ?>" target="_blank" class="text-success" title="WhatsApp">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $tracking->getStatusBadgeClass() ?>">
                                                        <?= $tracking->getStatusName() ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($tracking->date_s)) ?>
                                                    <?php if (!empty($tracking->hour)): ?>
                                                        <br><small class="text-muted"><?= date('H:i', strtotime($tracking->hour)) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= StringHelper::truncate(Html::encode($tracking->comments ?? ''), 50, '...') ?></td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button class="btn btn-info btn-sm tracking-btn-action view-tracking-btn" data-id="<?= $tracking->id_sales_tracking ?>" title="Ver">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($isAdmin || $isSuperAdmin): ?>
                                                            <button class="btn btn-primary btn-sm tracking-btn-action edit-tracking-btn" data-id="<?= $tracking->id_sales_tracking ?>" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $tracking->id_sales_tracking], [
                                                                'class' => 'btn btn-danger btn-sm tracking-btn-action',
                                                                'title' => 'Mover a papelera',
                                                                'data' => [
                                                                    'confirm' => '¿Mover este seguimiento a la papelera?',
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
                                                No hay seguimientos registrados
                                                <br><small class="text-muted">Crea un nuevo seguimiento para comenzar</small>
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
                                    Mostrando <?= $dataProvider ? $dataProvider->getCount() : 0 ?> de <?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?> seguimientos
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

                <!-- ÚLTIMOS SEGUIMIENTOS + RESUMEN -->
                <div class="row g-2 mt-3 bottom-cards">
                    <div class="col-md-6">
                        <div class="card tracking-activities-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <i class="fas fa-history text-primary"></i>
                                    <span>Últimos Seguimientos</span>
                                </div>
                                <span class="badge bg-primary"><?= count($ultimosSeguimientos) ?></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="tracking-activities-list">
                                    <?php if (!empty($ultimosSeguimientos)): ?>
                                        <?php foreach ($ultimosSeguimientos as $tracking): ?>
                                            <div class="tracking-activity-item">
                                                <div class="tracking-activity-icon"><i class="fas fa-phone text-success"></i></div>
                                                <div class="tracking-activity-content">
                                                    <div class="tracking-activity-title">
                                                        <strong><?= $tracking->lead ? Html::encode($tracking->lead->name . ' ' . $tracking->lead->lastname) : 'Lead eliminado' ?></strong>
                                                        <span class="badge bg-<?= $tracking->getStatusBadgeClass() ?>"><?= $tracking->getStatusName() ?></span>
                                                    </div>
                                                    <div class="tracking-activity-description"><?= StringHelper::truncate(Html::encode($tracking->comments ?? 'Seguimiento registrado'), 60, '...') ?></div>
                                                    <div class="tracking-activity-meta">
                                                        <span class="activity-date"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($tracking->date_s)) ?> <?= !empty($tracking->hour) ? date('H:i', strtotime($tracking->hour)) : '' ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="tracking-activity-empty">
                                            <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                            <p class="text-muted">No hay seguimientos recientes</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card tracking-funnel-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <i class="fas fa-chart-pie text-primary"></i>
                                    <span>Resumen de Seguimientos</span>
                                </div>
                                <span class="badge bg-info text-white"><?= $totalTrackings ?></span>
                            </div>
                            <div class="card-body d-flex align-items-center">
                                <div class="w-100">
                                    <?php if (!empty($statusCounts)):
                                        $colors = ['warning', 'info', 'success', 'danger', 'secondary', 'primary'];
                                        $i = 0;
                                        foreach ($statusCounts as $name => $count):
                                            $color = $colors[$i % count($colors)];
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                            <span><span class="badge bg-<?= $color ?>"><?= $name ?></span></span>
                                            <span><strong><?= $count ?></strong></span>
                                        </div>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $totalTrackings > 0 ? ($count / $totalTrackings) * 100 : 0 ?>%;"></div>
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

    </div><!-- /.sales-tracking-wrapper -->
</div><!-- /.sales-tracking-index -->

<?php
$viewModalUrl = Url::to(['sales-tracking/view-modal']);
$updateModalUrl = Url::to(['sales-tracking/update-modal']);
?>

<script>
window.onTrackingUpdated = function(t) {
    var row = document.querySelector('.tracking-row[data-id="' + t.id + '"]');
    if (!row) return;

    var cells = row.querySelectorAll('td');
    if (cells[2] && t.status_name) {
        var badge = cells[2].querySelector('.badge');
        if (badge) {
            badge.textContent = t.status_name;
            if (t.status_badge) badge.className = 'badge bg-' + t.status_badge;
        }
    }
    if (cells[4] && t.comments !== undefined) {
        cells[4].textContent = t.comments.substring(0, 50);
    }

    row.style.transition = 'background-color 0.5s';
    row.style.backgroundColor = '#d4edda';
    setTimeout(function() { row.style.backgroundColor = ''; }, 1000);
};

function executeScripts(container) {
    if (!container) return 0;
    var scripts = container.querySelectorAll('script');
    scripts.forEach(function(oldScript) {
        var newScript = document.createElement('script');
        if (oldScript.src) newScript.src = oldScript.src;
        else newScript.textContent = oldScript.textContent;
        document.body.appendChild(newScript);
        oldScript.remove();
    });
    return scripts.length;
}

(function() {
    if (typeof jQuery === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = function() {
            inicializar();
        };
        document.head.appendChild(script);
    } else {
        inicializar();
    }
})();

function inicializar() {
    var $ = jQuery;

    var viewModalUrl = '<?= $viewModalUrl ?>';
    var updateModalUrl = '<?= $updateModalUrl ?>';
    var isOpen = false;
    var currentId = null;

    function closePanel(callback) {
        var panel = document.getElementById('panelWrapper');
        var tableWrapper = document.getElementById('tableWrapper');

        if (!panel || !tableWrapper) {
            if (callback) callback();
            return;
        }

        isOpen = false;

        document.querySelectorAll('.tracking-row').forEach(function(row) {
            row.classList.remove('tracking-row-selected');
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
        var filaSeleccionada = document.querySelector('.tracking-row[data-id="' + id + '"]');

        if (!panel || !tableWrapper || !panelContent) {
            return;
        }

        document.querySelectorAll('.tracking-row').forEach(function(row) {
            row.classList.remove('tracking-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('tracking-row-selected');
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
                executeScripts(panelContent);
            })
            .catch(function(error) {
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
        var filaSeleccionada = document.querySelector('.tracking-row[data-id="' + id + '"]');

        if (!panel || !tableWrapper || !panelContent) {
            return;
        }

        document.querySelectorAll('.tracking-row').forEach(function(row) {
            row.classList.remove('tracking-row-selected');
        });
        if (filaSeleccionada) {
            filaSeleccionada.classList.add('tracking-row-selected');
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
                executeScripts(panelContent);
            })
            .catch(function(error) {
                panelContent.innerHTML = '<div class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x d-block mb-2"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-2" onclick="closePanel()">Cerrar</button></div>';
            });
    }

    window.openPanel = openPanel;
    window.openEditPanel = openEditPanel;
    window.closePanel = closePanel;

    $(document).on('click', '.view-tracking-btn', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        if (id) openPanel(id);
    });

    $(document).on('click', '.edit-tracking-btn', function(e) {
        e.stopPropagation();
        var id = $(this).data('id');
        if (id) openEditPanel(id);
    });

    $(document).on('click', '.tracking-row', function(e) {
        if ($(e.target).closest('a, button').length > 0) return;
        var id = $(this).data('id');
        if (id) openPanel(id);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closePanel();
        }
    });

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
}
</script>