<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Seguimiento';
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$lead = \app\models\Lead::findOne($model->id_lead);
?>

<div class="sales-tracking-update">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit"></i> <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Información del Lead -->
                    <?php if ($lead): ?>
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <i class="fas fa-user"></i> <strong>Lead:</strong> 
                                    <?= Html::encode($lead->name . ' ' . $lead->lastname) ?>
                                </div>
                                <div class="col-md-6">
                                    <i class="fas fa-phone"></i> <strong>Teléfono:</strong> 
                                    <?= Html::encode($lead->phone) ?>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <i class="fas fa-tag"></i> <strong>Estado del Lead:</strong> 
                                    <?php
                                    $statusName = $lead->getStatusName();
                                    $badgeClass = $lead->getStatusBadgeClass();
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <?= $statusName ?>
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <i class="fas fa-user-tie"></i> <strong>Agente:</strong> 
                                    <?= $lead->getAgentName() ?>
                                </div>
                            </div>
                            <?php if ($lead->comments): ?>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <i class="fas fa-comment"></i> <strong>Observaciones:</strong> 
                                        <?= nl2br(Html::encode($lead->comments)) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Información del Seguimiento Actual -->
                    <div class="alert alert-secondary">
                        <div class="row">
                            <div class="col-md-6">
                                <i class="fas fa-calendar"></i> <strong>Fecha de registro:</strong> 
                                <?= date('d/m/Y', strtotime($model->date_s)) ?>
                            </div>
                            <div class="col-md-6">
                                <i class="fas fa-clock"></i> <strong>Hora:</strong> 
                                <?= date('H:i', strtotime($model->hour)) ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <i class="fas fa-tag"></i> <strong>Estado actual:</strong> 
                                <?php
                                $currentStatusName = $model->getStatusName();
                                $currentBadgeClass = $model->getStatusBadgeClass();
                                ?>
                                <span class="badge bg-<?= $currentBadgeClass ?>">
                                    <?= $currentStatusName ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php $form = ActiveForm::begin(); ?>

                    <!-- 🔥 CAMPO id_status (en lugar de status_sales) -->
                    <?= $form->field($model, 'id_status')->dropDownList(
                        $statusOptions,
                        ['prompt' => 'Seleccione un estado...']
                    )->label('Estado del Seguimiento') ?>

                    <?= $form->field($model, 'comments')->textarea([
                        'rows' => 4,
                        'placeholder' => 'Comentarios del seguimiento...'
                    ])->label('Comentarios') ?>

                    <?= $form->field($model, 'date_f')->input('date', [
                        'placeholder' => 'YYYY-MM-DD'
                    ])->label('Próximo Seguimiento') ?>

                    <!-- Campo oculto para id_lead -->
                    <?= $form->field($model, 'id_lead')->hiddenInput()->label(false) ?>

                    <div class="form-group mt-4">
                        <div class="d-grid gap-2">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar seguimiento', [
                                'class' => 'btn btn-warning btn-lg'
                            ]) ?>
                        </div>
                        <div class="text-center mt-2">
                            <?= Html::a('<i class="fas fa-arrow-left"></i> Regresar al lead', 
                                ['lead/view', 'id' => $model->id_lead], 
                                ['class' => 'btn btn-default']
                            ) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>