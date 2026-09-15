<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Cotizaciones';
$this->params['breadcrumbs'][] = $this->title;

// Variables del controlador
$quotes = isset($quotes) ? $quotes : [];
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$statusOptions = isset($statusOptions) ? $statusOptions : [];
?>

<div class="quote-index">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="fas fa-file-invoice text-primary me-2"></i> 
                <?= Html::encode($this->title) ?>
            </h4>
            <small class="text-muted">
                <i class="far fa-clock me-1"></i> 
                <?= date('d/m/Y H:i') ?>
            </small>
        </div>
        <div>
            <?php if ($isAdmin): ?>
                <?= Html::a('<i class="fas fa-plus"></i> Nueva Cotización', ['create'], [
                    'class' => 'btn btn-success btn-sm'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TARJETAS DE MÉTRICAS -->
    <?php
    $totalQuotes = count($quotes);
    $totalAmount = 0;
    $pendingQuotes = 0;
    $approvedQuotes = 0;
    
    foreach ($quotes as $quote) {
        $totalAmount += $quote->total_amount ?? 0;
        $status = $quote->getStatusName();
        if ($status == 'Pendiente') $pendingQuotes++;
        if ($status == 'Aprobado') $approvedQuotes++;
    }
    ?>
    
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-primary dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $totalQuotes ?></div>
                        <div class="stat-label">Total Cotizaciones</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-success dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $approvedQuotes ?></div>
                        <div class="stat-label">Aprobadas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-warning dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number"><?= $pendingQuotes ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card stat-card-info dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon me-2">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-number">$<?= number_format($totalAmount, 0, ',', '.') ?></div>
                        <div class="stat-label">Total Monto</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE COTIZACIONES -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Total</th>
                            <th>Enganche</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($quotes)): ?>
                            <?php foreach ($quotes as $index => $quote): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <?php if ($quote->lead): ?>
                                            <strong><?= Html::encode($quote->lead->name . ' ' . $quote->lead->lastname) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= Html::encode($quote->lead->phone ?? 'Sin teléfono') ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Lead no disponible</span>
                                            <br>
                                            <small class="text-danger">(ID: <?= $quote->id_lead ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($quote->date_quote)) ?></td>
                                    <td><?= date('H:i', strtotime($quote->hour_quote)) ?></td>
                                    <td><strong>$<?= number_format($quote->total_amount ?? 0, 0, ',', '.') ?></strong></td>
                                    <td><?= $quote->down_payment ? '$' . number_format($quote->down_payment, 0, ',', '.') : '-' ?></td>
                                    <td><?= $quote->getFormattedPending() ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = $quote->getStatusBadgeClass();
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= $quote->getStatusName() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $quote->id_quote], [
                                                'class' => 'btn btn-info btn-sm btn-action',
                                                'title' => 'Ver'
                                            ]) ?>
                                            
                                            <?php if ($isAdmin): ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $quote->id_quote], [
                                                    'class' => 'btn btn-primary btn-sm btn-action',
                                                    'title' => 'Editar'
                                                ]) ?>
                                                
                                                <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $quote->id_quote], [
                                                    'class' => 'btn btn-danger btn-sm btn-action',
                                                    'title' => 'Eliminar',
                                                    'data' => [
                                                        'confirm' => '¿Estás seguro de eliminar esta cotización?',
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
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay cotizaciones registradas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// ============================================
// JAVASCRIPT PARA DATATABLE (Opcional)
// ============================================
$this->registerJs("
    $(document).ready(function() {
        var table = $('.table');
        if (table.find('tbody tr').length > 0 && table.find('tbody tr td[colspan]').length === 0) {
            try {
                table.DataTable({
                    'pageLength': 10,
                    'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                    'language': {
                        'url': '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                    },
                    'columnDefs': [
                        { 'orderable': false, 'targets': [0, 8] }
                    ],
                    'order': [[2, 'desc']]
                });
                console.log('✅ DataTable inicializado correctamente');
            } catch(e) {
                console.error('❌ Error al inicializar DataTable:', e);
            }
        }
    });
");
?>