<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Registro de Ventas';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/sales.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

// ============================================
// VARIABLES DEL CONTROLADOR
// ============================================
$sales = isset($sales) ? $sales : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$totalVendidos = isset($totalVendidos) ? $totalVendidos : 0;
$montoVendidos = isset($montoVendidos) ? $montoVendidos : 0;
$totalPending = isset($totalPending) ? $totalPending : 0;
$totalPerdidos = isset($totalPerdidos) ? $totalPerdidos : 0;
$montoPerdidos = isset($montoPerdidos) ? $montoPerdidos : 0;
$thisMonthSales = isset($thisMonthSales) ? $thisMonthSales : 0;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$search = isset($search) ? $search : '';
$status = isset($status) ? $status : '';
$fecha_inicio = isset($fecha_inicio) ? $fecha_inicio : '';
$fecha_fin = isset($fecha_fin) ? $fecha_fin : '';
$statusList = isset($statusList) ? $statusList : [];
$ultimasVentas = isset($ultimasVentas) ? $ultimasVentas : [];

// ============================================
// MÉTRICAS ADICIONALES
// ============================================
$montoPromedio = $totalVendidos > 0 ? $montoVendidos / $totalVendidos : 0;

// ============================================
// VARIABLES DE PAGINACIÓN
// ============================================
$totalCount = $dataProvider ? $dataProvider->getTotalCount() : 0;
$currentCount = $dataProvider ? $dataProvider->getCount() : 0;
$pageCount = $dataProvider ? $dataProvider->getPagination()->getPageCount() : 1;
$currentPage = $dataProvider ? $dataProvider->getPagination()->getPage() + 1 : 1;
$pageSize = $dataProvider ? $dataProvider->getPagination()->getPageSize() : 10;

// ============================================
// DETECTAR SI ESTÁ FILTRANDO POR CANCELADO
// ============================================
$filtrandoCancelado = (strtolower(trim($status)) === 'cancelado');
?>

<div class="sales-container">
    <div class="sales-wrapper">
        
        <!-- HEADER -->
        <div class="sales-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span>
                    <span class="separator">›</span>
                    <span>Ventas</span>
                    <span class="separator">›</span>
                    <span class="current">Registro de Ventas</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <?php if ($isAgent): ?>
                        <span class="badge-role ms-2"><i class="fas fa-user"></i> Mis Ventas</span>
                    <?php endif; ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
        </div>

        <!-- MÉTRICAS PRINCIPALES -->
        <div class="row g-2 mb-3">
            <div class="col-xl-4 col-md-6 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-shopping-cart"></i></div>
                        <div>
                            <div class="stat-number"><?= $totalVendidos ?></div>
                            <div class="stat-label">Total vendidos</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="stat-number text-danger"><?= $totalPerdidos ?></div>
                            <div class="stat-label">Perdidos (Cancelados)</div>
                            <div class="stat-label text-danger">$<?= number_format($montoPerdidos, 0, '.', ',') ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-6">
                <div class="card stat-card stat-card-info dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="stat-number"><?= $thisMonthSales ?></div>
                            <div class="stat-label">Ventas Este Mes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MÉTRICAS SECUNDARIAS -->
        <div class="row g-2 mb-3">
            <div class="col-xl-4 col-md-6 col-6">
                <div class="card stat-card stat-card-secondary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-calculator"></i></div>
                        <div>
                            <div class="stat-number">$<?= number_format($montoPromedio, 0, '.', ',') ?></div>
                            <div class="stat-label">Promedio Venta</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-dollar-sign"></i></div>
                        <div>
                            <div class="stat-number">$<?= number_format($montoVendidos, 0, '.', ',') ?></div>
                            <div class="stat-label">Total Vendido</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-6">
                <div class="card stat-card stat-card-danger dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="stat-number text-danger"><?= $totalPerdidos ?></div>
                            <div class="stat-label">Cancelados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['sales/index']) ?>" id="form-filtros">
                    <div class="row align-items-end">
                        <div class="col-md-1">
                            <label class="form-label fw-bold mb-0" style="font-size: 0.75rem; color: #4a5568;">
                                <i class="fas fa-filter"></i> Filtrar
                            </label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Buscar por cliente..." 
                                   value="<?= Html::encode($search) ?>" id="search-input">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status" id="status-select">
                                <option value="">Todos los estados</option>
                                <?php foreach ($statusList as $id => $nombre): ?>
                                    <option value="<?= Html::encode($nombre) ?>" <?= strtolower($status) == strtolower($nombre) ? 'selected' : '' ?>>
                                        <?= Html::encode(ucfirst($nombre)) ?>
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
                            <a href="<?= Url::to(['sales/index']) ?>" class="btn btn-secondary w-100" title="Limpiar">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA DE VENTAS -->
        <div class="card table-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-list"></i>
                    <span>Listado de Ventas</span>
                    <?php if ($filtrandoCancelado): ?>
                        <span class="badge bg-danger ms-2">Cancelados: <?= $totalCount ?></span>
                    <?php else: ?>
                        <span class="badge bg-success ms-2"><?= $totalCount ?> Registros</span>
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
                    <table class="table table-hover mb-0" id="sales-table">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Monto Total</th>
                                <th>Estatus Venta</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales)): ?>
                                <?php foreach ($sales as $index => $sale): ?>
                                    <?php 
                                    $statusName = $sale->getStatusName() ?? 'Sin estado';
                                    $isCancelado = strtolower($statusName) == 'cancelado';
                                    $isVendido = strtolower($statusName) == 'completado' || 
                                                 strtolower($statusName) == 'pagada' || 
                                                 strtolower($statusName) == 'aprobada';
                                    $rowClass = $isCancelado ? 'table-danger' : 'table-success';
                                    ?>
                                    <tr class="sale-row <?= $rowClass ?>" data-id="<?= $sale->id_quote ?>">
                                        <td>
                                            <strong><?= isset($sale->lead) ? Html::encode($sale->lead->name . ' ' . $sale->lead->lastname) : 'Lead eliminado' ?></strong>
                                        </td>
                                        <td>
                                            <?php if (isset($sale->lead) && $sale->lead->phone): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span><?= $sale->lead->phone ?></span>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sale->lead->phone) ?>" target="_blank" class="text-success" title="WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="<?= $isCancelado ? 'text-danger' : 'text-success' ?>">
                                                $<?= number_format($sale->total_amount, 0, '.', ',') ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($isCancelado): ?>
                                                <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Perdido</span>
                                            <?php elseif ($isVendido): ?>
                                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Vendido</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning"><i class="fas fa-clock"></i> <?= Html::encode($statusName) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($sale->date_quote)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        <?php if ($filtrandoCancelado): ?>
                                            No hay cotizaciones canceladas
                                        <?php elseif (!empty($status)): ?>
                                            No hay ventas con el estado "<?= Html::encode($status) ?>"
                                        <?php else: ?>
                                            No hay ventas registradas
                                            <br><small class="text-muted">Las ventas aparecerán aquí cuando se creen cotizaciones completadas</small>
                                        <?php endif; ?>
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
                            Mostrando <?= $currentCount ?> de <?= $totalCount ?> 
                            <?= $filtrandoCancelado ? 'cancelados' : 'registros' ?>
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

        <!-- ÚLTIMAS VENTAS + RESUMEN -->
        <div class="row g-2 mt-3 bottom-cards">
            <div class="col-md-6">
                <div class="card activities-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-history text-primary"></i>
                            <span>
                                <?= $filtrandoCancelado ? 'Últimos Cancelados' : 'Últimas Ventas' ?>
                            </span>
                        </div>
                        <span class="badge bg-<?= $filtrandoCancelado ? 'danger' : 'primary' ?>">
                            <?= count($ultimasVentas) ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="activities-list">
                            <?php if (!empty($ultimasVentas)): ?>
                                <?php foreach ($ultimasVentas as $sale): ?>
                                    <?php 
                                    $statusName = $sale->getStatusName() ?? 'Sin estado';
                                    $isCancelado = strtolower($statusName) == 'cancelado';
                                    $isVendido = strtolower($statusName) == 'completado' || 
                                                 strtolower($statusName) == 'pagada' || 
                                                 strtolower($statusName) == 'aprobada';
                                    ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <?php if ($isCancelado): ?>
                                                <i class="fas fa-times-circle text-danger"></i>
                                            <?php elseif ($isVendido): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-clock text-warning"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">
                                                <strong><?= isset($sale->lead) ? Html::encode($sale->lead->name . ' ' . $sale->lead->lastname) : 'Lead eliminado' ?></strong>
                                                <?php if ($isCancelado): ?>
                                                    <span class="badge bg-danger">Perdido</span>
                                                <?php elseif ($isVendido): ?>
                                                    <span class="badge bg-success">Vendido</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><?= Html::encode($statusName) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-description">
                                                $<?= number_format($sale->total_amount, 0, '.', ',') ?>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="activity-date">
                                                    <i class="far fa-calendar-alt"></i> 
                                                    <?= date('d/m/Y H:i', strtotime($sale->date_quote)) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="activity-empty">
                                    <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                    <p class="text-muted">
                                        <?= $filtrandoCancelado ? 'No hay cancelados recientes' : 'No hay ventas recientes' ?>
                                    </p>
                                </div>
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
                            <span>Resumen de Ventas</span>
                        </div>
                        <span class="badge bg-info text-white"><?= $totalVendidos ?> vendidos</span>
                    </div>
                    <div class="card-body d-flex align-items-center">
                        <div class="w-100">
                            <!-- Vendidos -->
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                <span><span class="badge bg-success">Vendidos</span></span>
                                <span><strong><?= $totalVendidos ?></strong></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?= ($totalVendidos + $totalPerdidos) > 0 ? ($totalVendidos / ($totalVendidos + $totalPerdidos)) * 100 : 0 ?>%;"></div>
                            </div>
                            
                            <!-- Perdidos -->
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                <span><span class="badge bg-danger">Perdidos</span></span>
                                <span><strong><?= $totalPerdidos ?></strong></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-danger" style="width: <?= ($totalVendidos + $totalPerdidos) > 0 ? ($totalPerdidos / ($totalVendidos + $totalPerdidos)) * 100 : 0 ?>%;"></div>
                            </div>
                            
                            <!-- Monto Vendido -->
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                <span><span class="badge bg-primary">Monto Vendido</span></span>
                                <span><strong>$<?= number_format($montoVendidos, 0, '.', ',') ?></strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// ============================================
// FILTROS AUTOMÁTICOS
// ============================================
(function() {
    var searchInput = document.getElementById('search-input');
    var statusSelect = document.getElementById('status-select');
    var fechaInicio = document.getElementById('fecha-inicio');
    var fechaFin = document.getElementById('fecha-fin');
    var btnFiltrar = document.getElementById('btn-filtrar');
    var form = document.getElementById('form-filtros');

    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            form.submit();
        });
    }

    if (fechaInicio) {
        fechaInicio.addEventListener('change', function() {
            form.submit();
        });
    }

    if (fechaFin) {
        fechaFin.addEventListener('change', function() {
            form.submit();
        });
    }

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function(e) {
            e.preventDefault();
            form.submit();
        });
    }
})();

// ============================================
// RESALTAR FILA AL HACER CLICK
// ============================================
(function() {
    var rows = document.querySelectorAll('.sale-row');
    rows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a, button')) return;
            rows.forEach(function(r) { r.classList.remove('sale-row-selected'); });
            row.classList.add('sale-row-selected');
        });
    });
})();
</script>