<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Creación de Empresa';
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="empresa-create">
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-building"></i> <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <h5 class="mb-3 text-muted">Información básica</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Ej. Trivana'
                            ])->label('Nombre de empresa') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'domain')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Ej. trivana.com'
                            ])->label('Dominio') ?>
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

                    <?= $form->field($model, 'description')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Descripción de la empresa...'
                    ])->label('Descripción') ?>

                    <?php if (isset($statusList) && !empty($statusList)): ?>
                        <?= $form->field($model, 'id_status')->dropDownList(
                            $statusList,
                            ['prompt' => 'Seleccione un estado']
                        )->label('Estado') ?>
                    <?php endif; ?>

                    <div class="form-group mt-4">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Empresa', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-default']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0"><i class="fas fa-cogs"></i> Configuración del CRM</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Módulos disponibles para esta empresa</p>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Gestión de contactos</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Tareas y actividades</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Leads</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Documentos</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Análisis y reportes</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Panel de ventas</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Dashboard</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>