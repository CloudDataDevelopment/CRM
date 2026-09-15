<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Seguimientos';
$this->params['breadcrumbs'][] = $this->title;

$trackings = isset($trackings) ? $trackings : [];
$totalTrackings = isset($totalTrackings) ? $totalTrackings : 0;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
?>

<div class="sales-tracking-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-phone text-primary me-2"></i> <?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-plus"></i> Nuevo Seguimiento', ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-sync"></i> Actualizar', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <!-- ESTADÍSTICA -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Total de seguimientos:</strong> <?= $totalTrackings ?>
    </div>

    <!-- TABLA -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>ID Lead</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Comentarios</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($trackings)): ?>
                            <?php foreach ($trackings as $index => $tracking): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <?php if ($tracking->lead): ?>
                                            <strong><?= Html::encode($tracking->lead->name . ' ' . $tracking->lead->lastname) ?></strong>
                                            <br>
                                            <small class="text-muted">ID: <?= $tracking->id_lead ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Lead eliminado</span>
                                            <br>
                                            <small class="text-muted">ID: <?= $tracking->id_lead ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($tracking->date_s)) ?></td>
                                    <td><?= date('H:i', strtotime($tracking->hour)) ?></td>
                                    <td>
                                        <?php
                                        try {
                                            $badgeClass = $tracking->getStatusBadgeClass();
                                            $statusName = $tracking->getStatusName();
                                        } catch (\Exception $e) {
                                            $badgeClass = 'secondary';
                                            $statusName = 'Sin Estado';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= $statusName ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= Html::encode(substr($tracking->comments ?? '', 0, 40)) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $tracking->id_sales_tracking], [
                                                'class' => 'btn btn-info btn-sm',
                                                'title' => 'Ver'
                                            ]) ?>
                                            <?php if ($isAdmin): ?>
                                                <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $tracking->id_sales_tracking], [
                                                    'class' => 'btn btn-primary btn-sm',
                                                    'title' => 'Editar'
                                                ]) ?>
                                                <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $tracking->id_sales_tracking], [
                                                    'class' => 'btn btn-danger btn-sm',
                                                    'title' => 'Eliminar',
                                                    'data' => [
                                                        'confirm' => '¿Eliminar este seguimiento?',
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay seguimientos registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>