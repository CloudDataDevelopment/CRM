<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\models\Task;

$this->title = 'Asignar Tarea';
$this->params['breadcrumbs'][] = ['label' => 'Tareas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/task.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="task-assign">
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
                    <i class="fas fa-user-plus text-primary me-2"></i>
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
                            <i class="fas fa-user-cog"></i>
                            Asignar Tarea a un Usuario
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
                                <i class="fas fa-info-circle"></i> Se muestran tareas sin asignar.
                            </small>
                        </div>

                        <div class="card bg-light p-3 mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user text-primary me-2"></i>
                                <h6 class="mb-0 fw-bold">Asignar a Usuario</h6>
                            </div>
                            <?= $form->field($model, 'id_user')->dropDownList(
                                $userList,
                                ['prompt' => 'Seleccione un usuario...', 'class' => 'form-select']
                            )->label('Usuario <span class="text-danger">*</span>') ?>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> La tarea será asignada al usuario seleccionado.
                            </small>
                        </div>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-user-check"></i> Asignar Tarea', [
                                'class' => 'btn btn-success btn-lg w-100'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            Tareas Sin Asignar
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $tasksWithoutUser = Task::find()
                            ->where(['id_user' => null])
                            ->count();
                        ?>
                        <p class="text-center">
                            <span class="display-4 d-block"><?= $tasksWithoutUser ?></span>
                            <span class="text-muted">tareas sin asignar</span>
                        </p>
                        <?php if ($tasksWithoutUser > 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                Tienes <?= $tasksWithoutUser ?> tarea(s) sin asignar.
                                Asígnalas a los agentes para distribuirlas.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> 
                                Todas las tareas están asignadas.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>