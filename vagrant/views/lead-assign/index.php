<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = 'Asignación de Leads';
$this->params['breadcrumbs'][] = $this->title;

$agentList = ArrayHelper::map($agents, 'id_user', function($agent) {
    return $agent->name . ' ' . $agent->lastname1 . ' (' . $agent->username . ')';
});

$assignUrl = Url::to(['lead-assign/assign']);
$unassignUrl = Url::to(['lead-assign/unassign']);
$reassignUrl = Url::to(['lead-assign/reassign']);
$massAssignUrl = Url::to(['lead-assign/mass-assign']);
?>

<div class="lead-assign-index">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>
                    <i class="fas fa-user-check"></i> 
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
            <!-- RESUMEN DE LEADS                           -->
            <!-- ============================================ -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-plus"></i> Leads Sin Asignar
                            </h5>
                            <h2 class="mb-0"><?= count($unassignedLeads) ?></h2>
                            <small>(Incluye leads sin agente o con agente inválido)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-check"></i> Leads Asignados
                            </h5>
                            <h2 class="mb-0"><?= count($assignedLeads) ?></h2>
                            <small>(Solo agentes válidos)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- LEADS SIN ASIGNAR                           -->
    <!-- ============================================ -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus"></i> 
                        Leads Sin Asignar
                        <span class="badge bg-dark float-end"><?= count($unassignedLeads) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($unassignedLeads)): ?>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all-unassigned">
                                        </th>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                        <th>Asignado a</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unassignedLeads as $index => $lead): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="lead-checkbox unassigned-checkbox" value="<?= $lead->id_lead ?>">
                                            </td>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= Html::encode($lead->comments) ?></small>
                                            </td>
                                            <td><?= $lead->phone ?></td>
                                            <td>
                                                <?php
                                                // 🔥 USAR getStatusName() y getStatusBadgeClass()
                                                $statusName = $lead->getStatusName();
                                                $badgeClass = $lead->getStatusBadgeClass();
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= $statusName ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($lead->id_user)): ?>
                                                    <?php if ($lead->user): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            <?= Html::encode($lead->user->name . ' ' . $lead->user->lastname1) ?>
                                                            (<?= $lead->user->getRoleName() ?>)
                                                        </span>
                                                        <br>
                                                        <small class="text-danger">No es un agente válido</small>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Usuario eliminado</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin asignar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($agents)): ?>
                                                    <?= Html::a('<i class="fas fa-user-check"></i>', '#', [
                                                        'class' => 'btn btn-success btn-sm assign-lead',
                                                        'data-lead-id' => $lead->id_lead,
                                                        'data-lead-name' => Html::encode($lead->name . ' ' . $lead->lastname),
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
                            No hay leads sin asignar
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- LEADS ASIGNADOS                             -->
        <!-- ============================================ -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check"></i> 
                        Leads Asignados a Agentes
                        <span class="badge bg-light text-dark float-end"><?= count($assignedLeads) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($assignedLeads)): ?>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Agente</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignedLeads as $index => $lead): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= Html::encode($lead->comments) ?></small>
                                            </td>
                                            <td><?= $lead->phone ?></td>
                                            <td>
                                                <?php if ($lead->user): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-user"></i> 
                                                        <?= Html::encode($lead->getAgentName()) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin agente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                // 🔥 USAR getStatusName() y getStatusBadgeClass()
                                                $statusName = $lead->getStatusName();
                                                $badgeClass = $lead->getStatusBadgeClass();
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= $statusName ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- 🔥 BOTÓN REASIGNAR -->
                                                <?php if (!empty($agents)): ?>
                                                    <?= Html::button('<i class="fas fa-exchange-alt"></i>', [
                                                        'class' => 'btn btn-primary btn-sm reassign-lead',
                                                        'data-lead-id' => $lead->id_lead,
                                                        'data-lead-name' => Html::encode($lead->name . ' ' . $lead->lastname),
                                                        'data-current-agent' => Html::encode($lead->getAgentName()),
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#reassignModal',
                                                        'title' => 'Reasignar a otro agente'
                                                    ]) ?>
                                                <?php endif; ?>
                                                
                                                <!-- Botón Desasignar -->
                                                <?= Html::button('<i class="fas fa-user-slash"></i>', [
                                                    'class' => 'btn btn-danger btn-sm',
                                                    'title' => 'Desasignar agente',
                                                    'onclick' => 'unassignLead(' . $lead->id_lead . ', "' . Html::encode($lead->name . ' ' . $lead->lastname) . '")'
                                                ]) ?>
                                                
                                                <?= Html::a('<i class="fas fa-eye"></i>', ['lead/view', 'id' => $lead->id_lead], [
                                                    'class' => 'btn btn-info btn-sm',
                                                    'title' => 'Ver lead',
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
                            No hay leads asignados a agentes
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL PARA ASIGNAR LEAD                      -->
<!-- ============================================ -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-check"></i> Asignar Lead a Agente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm($assignUrl, 'post', ['id' => 'assign-form']) ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Selecciona el agente al que deseas asignar este lead.
                    </div>
                    
                    <?= Html::hiddenInput('lead_id', '', ['id' => 'assign-lead-id']) ?>
                    
                    <div class="form-group">
                        <label for="assign-agent-id">Agente</label>
                        <?= Html::dropDownList('agent_id', null, $agentList, [
                            'prompt' => 'Selecciona un agente...',
                            'class' => 'form-control',
                            'id' => 'assign-agent-id',
                            'required' => true
                        ]) ?>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Lead a asignar:</strong>
                        <span id="assign-lead-name" class="text-primary"></span>
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
<!-- 🔥 MODAL PARA REASIGNAR LEAD                 -->
<!-- ============================================ -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Reasignar Lead a otro Agente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <?= Html::beginForm($reassignUrl, 'post', ['id' => 'reassign-form']) ?>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> 
                        Este lead ya está asignado a un agente. Selecciona el nuevo agente para reasignarlo.
                    </div>
                    
                    <?= Html::hiddenInput('lead_id', '', ['id' => 'reassign-lead-id']) ?>
                    
                    <div class="form-group">
                        <label><strong>Lead:</strong></label>
                        <p id="reassign-lead-name" class="text-primary"></p>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Agente Actual:</strong></label>
                        <p id="reassign-current-agent" class="text-warning"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="reassign-agent-id">Nuevo Agente</label>
                        <?= Html::dropDownList('agent_id', null, $agentList, [
                            'prompt' => 'Selecciona el nuevo agente...',
                            'class' => 'form-control',
                            'id' => 'reassign-agent-id',
                            'required' => true
                        ]) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?= Html::submitButton('<i class="fas fa-exchange-alt"></i> Reasignar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- FORMULARIO PARA ASIGNACIÓN MASIVA            -->
<!-- ============================================ -->
<?= Html::beginForm($massAssignUrl, 'post', ['id' => 'mass-assign-form']) ?>
    <?= Html::hiddenInput('lead_ids', '', ['id' => 'mass-lead-ids']) ?>
    <?= Html::hiddenInput('agent_id', '', ['id' => 'mass-agent-id']) ?>
<?= Html::endForm() ?>

<!-- ============================================ -->
<!-- JAVASCRIPT                                   -->
<!-- ============================================ -->
<?php
$js = <<<JS
// ============================================
// ASIGNAR LEAD - MODAL
// ============================================
$(document).on('click', '.assign-lead', function() {
    var leadId = $(this).data('lead-id');
    var leadName = $(this).data('lead-name');
    
    $('#assign-lead-id').val(leadId);
    $('#assign-lead-name').text(leadName);
});

// ============================================
// 🔥 REASIGNAR LEAD - MODAL
// ============================================
$(document).on('click', '.reassign-lead', function() {
    var leadId = $(this).data('lead-id');
    var leadName = $(this).data('lead-name');
    var currentAgent = $(this).data('current-agent');
    
    $('#reassign-lead-id').val(leadId);
    $('#reassign-lead-name').text(leadName);
    $('#reassign-current-agent').text(currentAgent);
});

// ============================================
// DESASIGNAR LEAD
// ============================================
function unassignLead(leadId, leadName) {
    if (confirm('¿Estás seguro de que deseas desasignar el lead "' + leadName + '" del agente?')) {
        var form = $('<form>', {
            'method': 'POST',
            'action': '{$unassignUrl}'
        });
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'lead_id',
            'value': leadId
        }));
        form.appendTo('body');
        form.submit();
    }
}

// ============================================
// SELECCIÓN MÚLTIPLE - LEADS SIN ASIGNAR
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
        btn.html('<i class="fas fa-users"></i> Asignar ' + checked + ' leads');
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
        alert('Selecciona al menos un lead.');
        return;
    }
    
    if (!agentId) {
        alert('Selecciona un agente.');
        return;
    }
    
    if (confirm('¿Asignar ' + selected.length + ' leads al agente seleccionado?')) {
        var leadIds = [];
        selected.each(function() {
            leadIds.push($(this).val());
        });
        
        $('#mass-lead-ids').val(JSON.stringify(leadIds));
        $('#mass-agent-id').val(agentId);
        $('#mass-assign-form').submit();
    }
});
JS;
$this->registerJs($js);
?>