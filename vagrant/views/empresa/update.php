<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Empresa: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id_company]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="empresa-update">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-edit"></i> <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Nombre') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'domain')->textInput(['maxlength' => true])->label('Dominio') ?>
                </div>
            </div>

            <?= $form->field($model, 'type')->dropDownList([
                'Tecnología' => 'Tecnología',
                'Marketing' => 'Marketing',
                'Consultoría' => 'Consultoría',
                'Finanzas' => 'Finanzas',
                'Salud' => 'Salud',
                'Educación' => 'Educación',
                'Comercio' => 'Comercio',
                'Otro' => 'Otro',
            ], ['prompt' => 'Seleccione un tipo'])->label('Tipo de empresa') ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 3])->label('Descripción') ?>

            <?php if (isset($statusList) && !empty($statusList)): ?>
                <?= $form->field($model, 'id_status')->dropDownList(
                    $statusList,
                    ['prompt' => 'Seleccione un estado']
                )->label('Estado') ?>
            <?php endif; ?>

            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['index'], ['class' => 'btn btn-default']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>