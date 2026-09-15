<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Ventas Completadas';
$this->params['breadcrumbs'][] = $this->title;

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
?>

<div class="sales-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>
            <i class="fas fa-check-circle text-success me-2"></i>
            <?= Html::encode($this->title) ?>
        </h1>
        <div>
            <?php if ($isAdmin): ?>
                <?= Html::a('<i class="fas fa-file-invoice"></i> Cotizaciones', ['/quote/index'], [
                    'class' => 'btn btn-info btn-sm'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-primary">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $totalSales ?? 0 ?></div>
                        <div class="stat-label">Total Ventas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-success">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="stat-number">$<?= number_format($totalAmount ?? 0, 0, ',', '.') ?></div>
                        <div class="stat-label">Monto Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-warning">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="stat-number">$<?= number_format($totalPending ?? 0, 0, ',', '.') ?></div>
                        <div class="stat-label">Pendiente de Pago</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-info">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?= $thisMonthSales ?? 0 ?></div>
                        <div class="stat-label">Este Mes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['sales/index']) ?>" id="form-filtros">
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-0">Buscar</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cliente o folio..." 
                               value="<?= Html::encode($search ?? '') ?>" id="search-input">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-0">Desde</label>
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="<?= Html::encode($fecha_inicio ?? '') ?>" id="fecha-inicio">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-0">Hasta</label>
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="<?= Html::encode($fecha_fin ?? '') ?>" id="fecha-fin">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-0">Estado</label>
                        <select class="form-select" name="status" id="status-select">
                            <option value="">Todos</option>
                            <option value="pagada" <?= ($status ?? '') == 'pagada' ? 'selected' : '' ?>>Pagada</option>
                            <option value="pendiente" <?= ($status ?? '') == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="aprobada" <?= ($status ?? '') == 'aprobada' ? 'selected' : '' ?>>Aprobada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" id="btn-filtrar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="<?= Url::to(['sales/index']) ?>" class="btn btn-secondary w-100" title="Limpiar filtros">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Monto Total</th>
                            <th>Pendiente</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sales)): ?>
                            <?php foreach ($sales as $index => $sale): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <span class="badge bg-secondary">#<?= str_pad($sale->id_quote, 6, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($sale->lead): ?>
                                            <strong><?= Html::encode($sale->lead->name . ' ' . $sale->lead->lastname) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Lead eliminado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sale->lead && $sale->lead->phone): ?>
                                            <?= Html::encode($sale->lead->phone) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">$<?= number_format($sale->total_amount, 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($sale->pending_payment > 0): ?>
                                            <span class="text-danger">$<?= number_format($sale->pending_payment, 0, ',', '.') ?></span>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> $0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'secondary';
                                        $statusName = $sale->getStatusName();
                                        if (strtolower($statusName) == 'aprobada' || strtolower($statusName) == 'pagada') {
                                            $statusClass = 'success';
                                        } elseif (strtolower($statusName) == 'pendiente') {
                                            $statusClass = 'warning';
                                        } elseif (strtolower($statusName) == 'rechazada' || strtolower($statusName) == 'cancelada') {
                                            $statusClass = 'danger';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= $statusName ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($sale->date_quote)) ?></td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $sale->id_quote], [
                                                'class' => 'btn btn-info btn-sm btn-action',
                                                'title' => 'Ver'
                                            ]) ?>
                                            <?php if ($isAdmin): ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $sale->id_quote], [
                                                    'class' => 'btn btn-primary btn-sm btn-action',
                                                    'title' => 'Editar'
                                                ]) ?>
                                            <?php endif; ?>
                                            <?php if ($isAdmin): ?>
                                                <?= Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $sale->id_quote], [
                                                    'class' => 'btn btn-secondary btn-sm btn-action',
                                                    'title' => 'Imprimir',
                                                    'target' => '_blank'
                                                ]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x d-block mb-3"></i>
                                    <p>No hay ventas completadas</p>
                                    <small>Las ventas completadas aparecerán aquí cuando un lead se convierta en cliente</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: #fff;
    border-left: 3px solid transparent;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}
.stat-card .card-body {
    padding: 0.5rem 0.7rem !important;
}
.stat-card .stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.stat-card .stat-number {
    font-size: 1.2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0;
}
.stat-card .stat-label {
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
    opacity: 0.7;
}
.stat-card-primary {
    border-left-color: #4e73df;
}
.stat-card-primary .stat-icon {
    background: rgba(78, 115, 223, 0.12);
    color: #4e73df;
}
.stat-card-primary .stat-number {
    color: #4e73df;
}
.stat-card-success {
    border-left-color: #1cc88a;
}
.stat-card-success .stat-icon {
    background: rgba(28, 200, 138, 0.12);
    color: #1cc88a;
}
.stat-card-success .stat-number {
    color: #1cc88a;
}
.stat-card-warning {
    border-left-color: #f6c23e;
}
.stat-card-warning .stat-icon {
    background: rgba(246, 194, 62, 0.12);
    color: #f6c23e;
}
.stat-card-warning .stat-number {
    color: #f6c23e;
}
.stat-card-info {
    border-left-color: #36b9cc;
}
.stat-card-info .stat-icon {
    background: rgba(54, 185, 204, 0.12);
    color: #36b9cc;
}
.stat-card-info .stat-number {
    color: #36b9cc;
}
.btn-action {
    padding: 0.15rem 0.4rem !important;
    font-size: 0.7rem !important;
    border-radius: 4px;
}
@media (max-width: 768px) {
    .stat-card .stat-number {
        font-size: 1rem !important;
    }
    .stat-card .stat-icon {
        width: 28px !important;
        height: 28px !important;
        font-size: 0.8rem !important;
    }
    .stat-card .stat-label {
        font-size: 0.5rem !important;
    }
}
</style>

<?php
// ============================================
// JAVASCRIPT PARA FILTROS
// ============================================
$js = <<<JS
// Filtrar automáticamente al cambiar cualquier campo
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
JS;
$this->registerJs($js);
?>