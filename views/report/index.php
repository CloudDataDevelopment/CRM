<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Evaluaciones';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/quote.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$reports = isset($reports) ? $reports : [];
$totalReports = isset($totalReports) ? $totalReports : count($reports);
$statusCounts = isset($statusCounts) ? $statusCounts : [];
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$statusOptions = isset($statusOptions) ? $statusOptions : [];
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';
?>

<div class="quote-container">
    <div class="quote-index">

        <!-- HEADER -->
        <div class="quote-header">
            <div class="header-left">
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span class="current">Reportes</span>
                </div>
                <h1 class="page-title"><?= Html::encode($this->title) ?> <small><?= date('d/m/Y H:i') ?></small></h1>
            </div>
            <div class="header-actions">
                <?php if ($isAdmin || $isSuperAdmin): ?>
                    <?= Html::a('<i class="fas fa-plus"></i> Nuevo Reporte', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div class="row g-2 mb-2">
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-file-alt"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalReports ?></div>
                            <div class="stat-label">Total Reportes</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-check-circle"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Completado'] ?? $statusCounts['completado'] ?? 0 ?></div>
                            <div class="stat-label">Completados</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-clock"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Pendiente'] ?? $statusCounts['pendiente'] ?? 0 ?></div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="card stat-card stat-card-info dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-bolt"></i></div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $statusCounts['Activo'] ?? $statusCounts['activo'] ?? 0 ?></div>
                            <div class="stat-label">Activos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['report/index']) ?>" id="form-filtros">
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
                        <div class="col-md-1"><a href="<?= Url::to(['report/index']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA + PANEL -->
        <div class="panel-container">
            <div class="table-wrapper" id="tableWrapper">
                <div class="card table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="reports-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <?php if ($isSuperAdmin): ?><th>Empresa</th><?php endif; ?>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reports)): ?>
                                        <?php foreach ($reports as $index => $report): ?>
                                            <tr class="quote-row" data-id="<?= $report->id_report ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><strong><?= Html::encode($report->report_name) ?></strong></td>
                                                <td><?= Html::encode($report->report_type ?? '-') ?></td>
                                                <td><?= $report->date_report ? date('d/m/Y', strtotime($report->date_report)) : '-' ?></td>
                                                <?php if ($isSuperAdmin): ?>
                                                    <td><?= $report->company ? Html::encode($report->company->name) : '-' ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <span class="badge bg-<?= $report->getStatusBadgeClass() ?>"><?= $report->getStatusName() ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button class="btn btn-info btn-sm btn-action view-report-btn" data-id="<?= $report->id_report ?>" title="Ver"><i class="fas fa-eye"></i></button>
                                                        <?php if ($isAdmin || $isSuperAdmin): ?>
                                                            <button class="btn btn-primary btn-sm btn-action edit-report-btn" data-id="<?= $report->id_report ?>" title="Editar"><i class="fas fa-edit"></i></button>
                                                            <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $report->id_report], [
                                                                'class' => 'btn btn-danger btn-sm btn-action',
                                                                'title' => 'Eliminar',
                                                                'data' => [
                                                                    'confirm' => '¿Eliminar este reporte?',
                                                                    'method' => 'post',
                                                                ],
                                                            ]) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No hay reportes registrados</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL LATERAL -->
            <div class="panel-wrapper" id="panelWrapper" style="display:none;">
                <div class="slide-panel" id="slidePanelContent"></div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewModalUrl = '<?= Url::to(['report/view-modal']) ?>';
    var updateModalUrl = '<?= Url::to(['report/update-modal']) ?>';

    var currentId = null;
    var isOpen = false;

    function cerrarPanel(callback) {
        var panel = document.getElementById('panelWrapper');
        var tableWrapper = document.getElementById('tableWrapper');
        if (panel) {
            panel.classList.remove('visible');
            panel.style.display = 'none';
        }
        if (tableWrapper) tableWrapper.classList.remove('with-panel');
        document.querySelectorAll('.quote-row').forEach(function (row) {
            row.classList.remove('quote-row-selected');
        });
        isOpen = false;
        currentId = null;
        if (typeof callback === 'function') callback();
    }

    function abrirReporte(id) {
        if (id === currentId && isOpen) {
            cerrarPanel();
            return;
        }
        if (isOpen) {
            cerrarPanel(function () {
                setTimeout(function () { abrirReporte(id); }, 300);
            });
            return;
        }

        currentId = id;
        isOpen = true;

        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var fila = document.querySelector('.quote-row[data-id="' + id + '"]');

        if (!panel || !tableWrapper || !panelContent) return;

        document.querySelectorAll('.quote-row').forEach(function (row) {
            row.classList.remove('quote-row-selected');
        });
        if (fila) fila.classList.add('quote-row-selected');

        tableWrapper.classList.add('with-panel');
        panel.style.display = 'block';
        panel.classList.add('visible');
        panelContent.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Cargando...</p></div>';

        fetch(viewModalUrl + '?id=' + id)
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
            .then(function (data) { panelContent.innerHTML = data; })
            .catch(function () {
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-3" onclick="cerrarPanel()">Cerrar</button></div>';
            });
    }

    function editarReporte(id) {
        if (isOpen) {
            cerrarPanel(function () {
                setTimeout(function () { abrirEdicion(id); }, 300);
            });
        } else {
            abrirEdicion(id);
        }
    }

    function abrirEdicion(id) {
        currentId = id;
        isOpen = true;

        var panel = document.getElementById('panelWrapper');
        var panelContent = document.getElementById('slidePanelContent');
        var tableWrapper = document.getElementById('tableWrapper');
        var fila = document.querySelector('.quote-row[data-id="' + id + '"]');

        if (!panel || !tableWrapper || !panelContent) return;

        document.querySelectorAll('.quote-row').forEach(function (row) {
            row.classList.remove('quote-row-selected');
        });
        if (fila) fila.classList.add('quote-row-selected');

        tableWrapper.classList.add('with-panel');
        panel.style.display = 'block';
        panel.classList.add('visible');
        panelContent.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Cargando formulario...</p></div>';

        fetch(updateModalUrl + '?id=' + id)
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
            .then(function (data) { panelContent.innerHTML = data; })
            .catch(function () {
                panelContent.innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x"></i><p>Error al cargar</p><button class="btn btn-secondary btn-sm mt-3" onclick="cerrarPanel()">Cerrar</button></div>';
            });
    }

    window.abrirReporte = abrirReporte;
    window.editarReporte = editarReporte;
    window.cerrarPanel = cerrarPanel;

    document.addEventListener('click', function (e) {
        var viewBtn = e.target.closest('.view-report-btn');
        if (viewBtn) { e.stopPropagation(); abrirReporte(viewBtn.dataset.id); return; }

        var editBtn = e.target.closest('.edit-report-btn');
        if (editBtn) { e.stopPropagation(); editarReporte(editBtn.dataset.id); return; }

        var row = e.target.closest('.quote-row');
        if (row && !e.target.closest('a, button')) {
            abrirReporte(row.dataset.id);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) cerrarPanel();
    });

    var searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') document.getElementById('form-filtros').submit();
        });
    }
    ['status-select', 'fecha-inicio', 'fecha-fin'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', function () { document.getElementById('form-filtros').submit(); });
    });
    var btnFiltrar = document.getElementById('btn-filtrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('form-filtros').submit();
        });
    }
});
</script>