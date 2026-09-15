<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = 'Asignación de Tareas';
$this->params['breadcrumbs'][] = $this->title;

$agentList = ArrayHelper::map($agents, 'id_user', function($agent) {
    return $agent->name . ' ' . $agent->lastname1 . ' (' . $agent->username . ')';
});

$assignUrl = Url::to(['task-assign/assign']);
$unassignUrl = Url::to(['task-assign/unassign']);
$massAssignUrl = Url::to(['task-assign/mass-assign']);
?>

<div class="task-assign-index">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>
                    <i class="fas fa-tasks"></i> 
                    <?= Html::encode($this->title) ?>
                </h1>
                <span class="badge bg-info">
                    <i class="fas fa-users"></i> 
                    Agentes: <?= count($agents) ?>
                </span>
            </div>

            <?php if (empty($agents)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>No hay agentes registrados en tu empresa.</strong>
                    <?= Html::a('Crear Agente', ['site/register'], ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            <?php endif; ?>

            <!-- ============================================ -->
            <!-- RESUMEN DE TAREAS                           -->
            <!-- ============================================ -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-plus"></i> Tareas Sin Asignar
                            </h5>
                            <h2 class="mb-0"><?= count($unassignedTasks) ?></h2>
                            <small>Tareas sin agente asignado</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-check"></i> Tareas Asignadas
                            </h5>
                            <h2 class="mb-0"><?= count($assignedTasks) ?></h2>
                            <small>Tareas con agente asignado</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TAREAS SIN ASIGNAR                           -->
    <!-- ============================================ -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus"></i> 
                        Tareas Sin Asignar
                        <span class="badge bg-dark float-end"><?= count($unassignedTasks) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($unassignedTasks)): ?>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all-unassigned">
                                        </th>
                                        <th>#</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unassignedTasks as $index => $task): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="task-checkbox unassigned-checkbox" value="<?= $task->id_task ?>">
                                            </td>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <?= Html::encode($task->comments ?? 'Sin descripción') ?>
                                                <?php if ($task->salesTracking && $task->salesTracking->lead): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> 
                                                        <?= Html::encode($task->salesTracking->lead->name . ' ' . $task->salesTracking->lead->lastname) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= \app\models\Task::getStatusBadgeClass($task->status) ?>">
                                                    <?= \app\models\Task::getStatusOptions()[$task->status] ?? ucfirst($task->status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($agents)): ?>
                                                    <?= Html::a('<i class="fas fa-user-check"></i>', '#', [
                                                        'class' => 'btn btn-success btn-sm assign-task',
                                                        'data-task-id' => $task->id_task,
                                                        'data-task-desc' => Html::encode($task->comments ?? 'Sin descripción'),
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#assignModal',
                                                        'title' => 'Asignar a agente'
                                                    ]) ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($agents)): ?>
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <?= Html::dropDownList('mass_agent_id', null, $agentList, [
                                            'prompt' => 'Seleccionar agente...',
                                            'class' => 'form-control form-control-sm',
                                            'id' => 'mass-agent-select'
                                        ]) ?>
                                    </div>
                                    <div class="col-6">
                                        <?= Html::button('<i class="fas fa-users"></i> Asignar seleccionados', [
                                            'class' => 'btn btn-primary btn-sm w-100',
                                            'id' => 'mass-assign-btn',
                                            'disabled' => true
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-2x d-block mb-2"></i>
                            No hay tareas sin asignar
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TAREAS ASIGNADAS                            -->
        <!-- ============================================ -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check"></i> 
                        Tareas Asignadas
                        <span class="badge bg-light text-dark float-end"><?= count($assignedTasks) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($assignedTasks)): ?>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Descripción</th>
                                        <th>Agente</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignedTasks as $index => $task): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <?= Html::encode($task->comments ?? 'Sin descripción') ?>
                                                <?php if ($task->salesTracking && $task->salesTracking->lead): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> 
                                                        <?= Html::encode($task->salesTracking->lead->name . ' ' . $task->salesTracking->lead->lastname) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($task->user): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-user"></i> 
                                                        <?= Html::encode($task->user->name . ' ' . $task->user->lastname1) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin agente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= \app\models\Task::getStatusBadgeClass($task->status) ?>">
                                                    <?= \app\models\Task::getStatusOptions()[$task->status] ?? ucfirst($task->status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= Html::button('<i class="fas fa-user-slash"></i>', [
                                                    'class' => 'btn btn-danger btn-sm',
                                                    'title' => 'Desasignar agente',
                                                    'onclick' => 'unassignTask(' . $task->id_task . ', "' . Html::encode($task->comments ?? 'Sin descripción') . '")'
                                                ]) ?>
                                                
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['task/view', 'id' => $task->id_task], [
                                                    'class' => 'btn btn-info btn-sm',
                                                    'title' => 'Ver tarea',
                                                    'target' => '_blank'
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            No hay tareas asignadas
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL PARA ASIGNAR TAREA                    -->
<!-- ============================================ -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-check"></i> Asignar Tarea a Agente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm($assignUrl, 'post', ['id' => 'assign-form']) ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Selecciona el agente al que deseas asignar esta tarea.
                    </div>
                    
                    <?= Html::hiddenInput('task_id', '', ['id' => 'assign-task-id']) ?>
                    
                    <div class="form-group">
                        <label><strong>Tarea:</strong></label>
                        <p id="assign-task-desc" class="text-primary"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="assign-agent-id">Agente</label>
                        <?= Html::dropDownList('agent_id', null, $agentList, [
                            'prompt' => 'Selecciona un agente...',
                            'class' => 'form-control',
                            'id' => 'assign-agent-id',
                            'required' => true
                        ]) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?= Html::submitButton('<i class="fas fa-check"></i> Asignar', ['class' => 'btn btn-success']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- FORMULARIO PARA ASIGNACIÓN MASIVA            -->
<!-- ============================================ -->
<?= Html::beginForm($massAssignUrl, 'post', ['id' => 'mass-assign-form']) ?>
    <?= Html::hiddenInput('task_ids', '', ['id' => 'mass-task-ids']) ?>
    <?= Html::hiddenInput('agent_id', '', ['id' => 'mass-agent-id']) ?>
<?= Html::endForm() ?>

<!-- ============================================ -->
<!-- JAVASCRIPT                                   -->
<!-- ============================================ -->
<?php
$js = <<<JS
// ============================================
// ASIGNAR TAREA - MODAL
// ============================================
$(document).on('click', '.assign-task', function() {
    var taskId = $(this).data('task-id');
    var taskDesc = $(this).data('task-desc');
    
    $('#assign-task-id').val(taskId);
    $('#assign-task-desc').text(taskDesc);
});

// ============================================
// DESASIGNAR TAREA
// ============================================
function unassignTask(taskId, taskDesc) {
    if (confirm('¿Estás seguro de que deseas desasignar la tarea "' + taskDesc + '" del agente?')) {
        var form = $('<form>', {
            'method': 'POST',
            'action': '{$unassignUrl}'
        });
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'task_id',
            'value': taskId
        }));
        form.appendTo('body');
        form.submit();
    }
}

// ============================================
// SELECCIÓN MÚLTIPLE - TAREAS SIN ASIGNAR
// ============================================
$('#select-all-unassigned').on('change', function() {
    $('.unassigned-checkbox').prop('checked', $(this).prop('checked'));
    updateMassAssignButton();
});

$('.unassigned-checkbox').on('change', function() {
    updateMassAssignButton();
});

function updateMassAssignButton() {
    var checked = $('.unassigned-checkbox:checked').length;
    var btn = $('#mass-assign-btn');
    if (checked > 0) {
        btn.prop('disabled', false);
        btn.html('<i class="fas fa-users"></i> Asignar ' + checked + ' tareas');
    } else {
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-users"></i> Asignar seleccionados');
    }
}

// ============================================
// ASIGNACIÓN MASIVA
// ============================================
$('#mass-assign-btn').on('click', function() {
    var selected = $('.unassigned-checkbox:checked');
    var agentId = $('#mass-agent-select').val();
    
    if (selected.length === 0) {
        alert('Selecciona al menos una tarea.');
        return;
    }
    
    if (!agentId) {
        alert('Selecciona un agente.');
        return;
    }
    
    if (confirm('¿Asignar ' + selected.length + ' tareas al agente seleccionado?')) {
        var taskIds = [];
        selected.each(function() {
            taskIds.push($(this).val());
        });
        
        $('#mass-task-ids').val(JSON.stringify(taskIds));
        $('#mass-agent-id').val(agentId);
        $('#mass-assign-form').submit();
    }
});
JS;
$this->registerJs($js);
?>