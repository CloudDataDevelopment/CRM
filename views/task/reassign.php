<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Task;

$this->title = 'Re-asignar Tarea';
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="task-reassign">
    <div class="task-wrapper">
        
        <!-- HEADER -->
        <div class="task-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Tareas</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-user-edit text-primary me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit"></i>
                            Re-asignar Tarea a Otro Usuario
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin(); ?>

                        <div class="card bg-light p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-tasks text-primary me-2"></i>
                                <h6 class="mb-0 fw-bold">Seleccionar Tarea</h6>
                            </div>
                            <?= $form->field($model, 'id_task')->dropDownList(
                                $taskList,
                                ['prompt' => 'Seleccione una tarea...', 'class' => 'form-select']
                            )->label('Tarea <span class="text-danger">*</span>') ?>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Se muestran tareas que ya están asignadas.
                            </small>
                        </div>

                        <div class="card bg-light p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user text-primary me-2"></i>
                                <h6 class="mb-0 fw-bold">Re-asignar a Usuario</h6>
                            </div>
                            <?= $form->field($model, 'id_user')->dropDownList(
                                $userList,
                                ['prompt' => 'Seleccione un usuario...', 'class' => 'form-select']
                            )->label('Nuevo Usuario <span class="text-danger">*</span>') ?>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> La tarea será re-asignada al nuevo usuario seleccionado.
                            </small>
                        </div>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-user-check"></i> Re-asignar Tarea', [
                                'class' => 'btn btn-success btn-lg w-100'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i>
                            Resumen de Asignación
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalAsignadas = Task::find()
                            ->where(['not', ['id_user' => null]])
                            ->count();
                        $totalSinAsignar = Task::find()
                            ->where(['id_user' => null])
                            ->count();
                        $totalTareas = $totalAsignadas + $totalSinAsignar;
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fas fa-user-check text-success"></i> Asignadas</span>
                            <span class="badge bg-success"><?= $totalAsignadas ?></span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $totalTareas > 0 ? ($totalAsignadas / $totalTareas) * 100 : 0 ?>%;"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="fas fa-user-times text-warning"></i> Sin Asignar</span>
                            <span class="badge bg-warning"><?= $totalSinAsignar ?></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: <?= $totalTareas > 0 ? ($totalSinAsignar / $totalTareas) * 100 : 0 ?>%;"></div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle"></i> 
                            Re-asignar una tarea permite cambiar el usuario responsable.
                        </p>
                        <p class="text-muted small">
                            <i class="fas fa-arrow-right"></i> 
                            Selecciona la tarea y el nuevo usuario para re-asignarla.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>