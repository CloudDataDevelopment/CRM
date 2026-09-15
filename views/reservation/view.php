<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detalle de Reservación #' . $model->id_reservation;
$this->params['breadcrumbs'][] = ['label' => 'Reservaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/reservation.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
?>

<div class="reservation-view">
    <div class="reservation-wrapper">
        
        <!-- HEADER -->
        <div class="reservation-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Reservaciones</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-calendar-check" style="color: #4e73df;"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?php if ($isAdmin || ($isAgent && $model->id_user == Yii::$app->user->id)): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id_reservation], [
                        'class' => 'btn btn-primary btn-sm btn-header-action'
                    ]) ?>
                <?php endif; ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                    'class' => 'btn btn-secondary btn-sm btn-header-action'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-body" style="padding: 20px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">ID</label>
                                    <p><span class="badge bg-secondary">#<?= $model->id_reservation ?></span></p>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Nombre</label>
                                    <p style="font-size: 0.95rem; font-weight: 500; color: #2d3748;"><?= Html::encode($model->name_reservation) ?></p>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Fecha</label>
                                    <p style="font-size: 0.95rem; color: #2d3748;"><?= $model->getFormattedDate() ?></p>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Hora</label>
                                    <p style="font-size: 0.95rem; color: #2d3748;"><?= $model->getFormattedHourS() ?> - <?= $model->getFormattedHourF() ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Estado</label>
                                    <p>
                                        <span class="badge-status bg-<?= $model->getStatusClass() ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= $model->getStatusLabel() ?>
                                        </span>
                                    </p>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Lead Asociado</label>
                                    <?php if ($model->lead): ?>
                                        <p style="font-size: 0.95rem; color: #2d3748;">
                                            <?= Html::a(
                                                Html::encode($model->getLeadName()),
                                                ['lead/view', 'id' => $model->lead->id_lead],
                                                ['target' => '_blank', 'style' => 'color: #4e73df; text-decoration: none;']
                                            ) ?>
                                            <br>
                                            <small class="text-muted"><i class="fas fa-phone"></i> <?= Html::encode($model->getLeadPhone()) ?></small>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted">Lead no disponible</p>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="font-weight: 600; color: #4a5568; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Asignado a</label>
                                    <p style="font-size: 0.95rem; color: #2d3748;">
                                        <?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'Sin asignar' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-body" style="padding: 15px;">
                        <h6 style="color: #2d3748; font-weight: 600; margin-bottom: 12px; font-size: 0.85rem;">
                            <i class="fas fa-info-circle" style="color: #4e73df;"></i>
                            Resumen
                        </h6>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #718096; font-size: 0.75rem;">ID Reservación</span>
                            <p style="font-weight: 600; color: #2d3748; font-size: 0.9rem;">#<?= $model->id_reservation ?></p>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #718096; font-size: 0.75rem;">Estado</span>
                            <p>
                                <span class="badge-status bg-<?= $model->getStatusClass() ?>">
                                    <i class="fas fa-circle"></i>
                                    <?= $model->getStatusLabel() ?>
                                </span>
                            </p>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #718096; font-size: 0.75rem;">Fecha Completa</span>
                            <p style="font-weight: 600; color: #2d3748; font-size: 0.9rem;"><?= $model->getFormattedDateTime() ?></p>
                        </div>
                        
                        <?php if ($isAdmin): ?>
                            <hr style="margin: 10px 0;">
                            <?= Html::a('<i class="fas fa-trash"></i> Eliminar Reservación', ['delete', 'id' => $model->id_reservation], [
                                'class' => 'btn btn-danger btn-sm w-100',
                                'style' => 'border-radius: 6px;',
                                'data' => [
                                    'confirm' => '¿Eliminar esta reservación permanentemente?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>