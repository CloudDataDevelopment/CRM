<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Actualizar Reservación #' . $model->id_reservation;
$this->params['breadcrumbs'][] = ['label' => 'Reservaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Reservación #' . $model->id_reservation, 'url' => ['view', 'id' => $model->id_reservation]];
$this->params['breadcrumbs'][] = 'Actualizar';

$this->registerCssFile('@web/css/reservation.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
?>

<div class="reservation-update">
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
                    <i class="fas fa-edit" style="color: #f6c23e;"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['view', 'id' => $model->id_reservation], [
                    'class' => 'btn btn-secondary btn-sm btn-header-action'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-body" style="padding: 20px;">
                        <?php $form = ActiveForm::begin(['options' => ['class' => 'reservation-form']]); ?>

                        <?= $form->field($model, 'name_reservation')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Ej: Reunión con cliente',
                            'class' => 'form-control'
                        ])->label('Nombre de la Reservación <span class="text-danger">*</span>') ?>

                        <?= $form->field($model, 'id_lead')->dropDownList(
                            $leadsList,
                            ['prompt' => 'Seleccione un lead...', 'class' => 'form-select']
                        )->label('Lead Asociado <span class="text-danger">*</span>') ?>

                        <?= $form->field($model, 'date_reservation')->input('date', [
                            'class' => 'form-control'
                        ])->label('Fecha de Reservación <span class="text-danger">*</span>') ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'hour_s')->input('time', [
                                    'class' => 'form-control'
                                ])->label('Hora de Inicio <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'hour_f')->input('time', [
                                    'class' => 'form-control'
                                ])->label('Hora de Fin <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="info-box mb-3">
                                <div class="info-title">
                                    <i class="fas fa-user-cog"></i>
                                    Asignación de Usuario
                                </div>
                                <?= $form->field($model, 'id_user')->dropDownList(
                                    $userList,
                                    ['prompt' => 'Seleccione un usuario...', 'class' => 'form-select']
                                )->label('Asignar a') ?>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Cambia el usuario asignado a esta reservación.
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" style="border-radius: 6px; border-left: 3px solid #4e73df;">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Reservación asignada a:</strong> 
                                <?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'Sin asignar' ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Reservación', [
                                'class' => 'btn btn-primary',
                                'style' => 'border-radius: 6px;'
                            ]) ?>
                            <?= Html::a('Cancelar', ['view', 'id' => $model->id_reservation], [
                                'class' => 'btn btn-secondary',
                                'style' => 'border-radius: 6px;'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-body" style="padding: 15px;">
                        <h6 style="color: #2d3748; font-weight: 600; margin-bottom: 12px; font-size: 0.85rem;">
                            <i class="fas fa-info-circle" style="color: #4e73df;"></i>
                            Información
                        </h6>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #718096; font-size: 0.75rem;">ID Reservación</span>
                            <p style="font-weight: 600; color: #2d3748; font-size: 0.9rem;">#<?= $model->id_reservation ?></p>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #718096; font-size: 0.75rem;">Estado actual</span>
                            <p>
                                <span class="badge-status bg-<?= $model->getStatusClass() ?>">
                                    <i class="fas fa-circle"></i>
                                    <?= $model->getStatusLabel() ?>
                                </span>
                            </p>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #718096; font-size: 0.75rem;">Asignado a</span>
                            <p style="font-weight: 600; color: #2d3748; font-size: 0.9rem;"><?= $model->user ? Html::encode($model->user->name . ' ' . $model->user->lastname1) : 'N/A' ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="card table-card">
                        <div class="card-body" style="padding: 15px;">
                            <h6 style="color: #dc2626; font-weight: 600; margin-bottom: 12px; font-size: 0.85rem;">
                                <i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>
                                Zona de Administración
                            </h6>
                            <?= Html::a('<i class="fas fa-trash"></i> Eliminar Reservación', ['delete', 'id' => $model->id_reservation], [
                                'class' => 'btn btn-danger w-100',
                                'style' => 'border-radius: 6px;',
                                'data' => [
                                    'confirm' => '¿Eliminar esta reservación permanentemente?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>