<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nueva Reservación';
$this->params['breadcrumbs'][] = ['label' => 'Reservaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/reservation.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
?>

<div class="reservation-create">
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
                    <i class="fas fa-plus-circle" style="color: #1cc88a;"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
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
                            'class' => 'form-control',
                            'min' => date('Y-m-d')
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

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-body" style="padding: 15px;">
                        <h6 style="color: #2d3748; font-weight: 600; margin-bottom: 12px; font-size: 0.85rem;">
                            <i class="fas fa-lightbulb" style="color: #f6c23e;"></i>
                            Consejos Rápidos
                        </h6>
                        <ul class="tips-list">
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Fecha válida</strong>
                                    <span>La fecha no puede ser anterior a hoy</span>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Horario correcto</strong>
                                    <span>La hora de fin debe ser mayor a la hora de inicio</span>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <strong>Lead asignado</strong>
                                    <span>Selecciona un lead existente para la reservación</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-body" style="padding: 15px;">
                        <h6 style="color: #2d3748; font-weight: 600; margin-bottom: 12px; font-size: 0.85rem;">
                            <i class="fas fa-info-circle" style="color: #4e73df;"></i>
                            Información
                        </h6>
                        <p style="color: #718096; font-size: 0.8rem; line-height: 1.5;">
                            <i class="fas fa-arrow-right" style="color: #4e73df;"></i>
                            Las reservaciones te permiten agendar citas y reuniones con tus leads.
                        </p>
                        <hr style="margin: 10px 0;">
                        <p style="color: #718096; font-size: 0.8rem; line-height: 1.5;">
                            <i class="fas fa-arrow-right" style="color: #4e73df;"></i>
                            Puedes ver todas tus reservaciones en el listado principal.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>