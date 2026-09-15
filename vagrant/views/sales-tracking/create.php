<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nuevo Seguimiento';
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// 🔥 Verificar si hay un lead asociado
$lead = isset($lead) ? $lead : null;
$leadId = isset($leadId) ? $leadId : null;
$leadsList = isset($leadsList) ? $leadsList : [];
$statusOptions = isset($statusOptions) ? $statusOptions : [];

// 🔥 Si hay un lead, mostrar su nombre en el título
if ($lead) {
    $this->title = 'Nuevo Seguimiento - ' . $lead->name . ' ' . $lead->lastname;
    $this->params['breadcrumbs'][] = ['label' => $lead->name . ' ' . $lead->lastname, 'url' => ['lead/view', 'id' => $lead->id_lead]];
}
?>

<div class="sales-tracking-create">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle me-2"></i> 
                <?= Html::encode($this->title) ?>
            </h5>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?php if ($lead): ?>
                <!-- Mostrar información del lead -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-user"></i> Lead:</strong> <?= Html::encode($lead->name . ' ' . $lead->lastname) ?></p>
                            <p><strong><i class="fas fa-phone"></i> Teléfono:</strong> <?= Html::encode($lead->phone) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-tag"></i> Estado:</strong> <?= $lead->getStatusName() ?></p>
                            <p><strong><i class="fas fa-calendar"></i> Registro:</strong> <?= date('d/m/Y', strtotime($lead->created_at)) ?></p>
                        </div>
                    </div>
                </div>
                <?= Html::activeHiddenInput($model, 'id_lead') ?>
            <?php else: ?>
                <!-- Selector de leads -->
                <?= $form->field($model, 'id_lead')->dropDownList(
                    $leadsList,
                    ['prompt' => 'Selecciona un lead...', 'class' => 'form-control']
                )->label('Lead') ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'id_status')->dropDownList(
                        $statusOptions,
                        ['prompt' => 'Selecciona el estado...', 'class' => 'form-control']
                    )->label('Estado del Seguimiento') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'date_f')->input('date', [
                        'class' => 'form-control',
                        'placeholder' => 'YYYY-MM-DD'
                    ])->label('Próximo Seguimiento') ?>
                </div>
            </div>

            <?= $form->field($model, 'comments')->textarea([
                'rows' => 4,
                'placeholder' => 'Comentarios del seguimiento...',
                'class' => 'form-control'
            ])->label('Comentarios') ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Seguimiento', [
                    'class' => 'btn btn-success'
                ]) ?>
                <?php if ($lead): ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['lead/view', 'id' => $lead->id_lead], [
                        'class' => 'btn btn-secondary'
                    ]) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], [
                        'class' => 'btn btn-secondary'
                    ]) ?>
                <?php endif; ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>