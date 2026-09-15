<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = 'Asignación de Leads';
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS
$this->registerCssFile('@web/css/lead-assign.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

// ============================================
// VARIABLES DEL CONTROLADOR
// ============================================
$unassignedLeads = isset($unassignedLeads) ? $unassignedLeads : [];
$assignedLeads = isset($assignedLeads) ? $assignedLeads : [];
$agents = isset($agents) ? $agents : [];
$leadsTotal = isset($leadsTotal) ? $leadsTotal : [];
$agentCounts = isset($agentCounts) ? $agentCounts : [];
$selectedAgentId = isset($selectedAgentId) ? $selectedAgentId : null;
$totalAssigned = isset($totalAssigned) ? $totalAssigned : count($assignedLeads);
$totalUnassigned = isset($totalUnassigned) ? $totalUnassigned : count($unassignedLeads);

// ============================================
// 🔥 LISTA DE AGENTES PARA EL SELECT (solo nombre y apellido)
// ============================================
$agentList = ArrayHelper::map($agents, 'id_user', function($agent) {
    $fullName = trim($agent->name . ' ' . $agent->lastname1);
    if (empty($fullName)) {
        $fullName = $agent->username;
    }
    return $fullName;
});

$unassignUrl = Url::to(['lead-assign/unassign']);
$massAssignUrl = Url::to(['lead-assign/mass-assign']);

// ============================================
// 🔥 FILTRAR LEADS ASIGNADOS SI HAY AGENTE SELECCIONADO
// ============================================
if ($selectedAgentId !== null && $selectedAgentId !== '') {
    $assignedLeads = array_filter($assignedLeads, function($lead) use ($selectedAgentId) {
        return $lead->id_user == $selectedAgentId;
    });
    $assignedLeads = array_values($assignedLeads);
}
?>

<div class="lead-assign-index">
    <div class="assign-wrapper">
        
        <!-- ============================================ -->
        <!-- HEADER -->
        <!-- ============================================ -->
        <div class="assign-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Leads</span><span class="separator">›</span>
                    <span class="current">Asignación</span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <span class="badge bg-info">
                    <i class="fas fa-users"></i> 
                    Agentes: <?= count($agents) ?>
                </span>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['lead/index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>

        <?php if (empty($agents)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>No hay agentes registrados en tu empresa.</strong>
                <?= Html::a('Crear Agente', ['site/register'], ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- MÉTRICAS -->
        <!-- ============================================ -->
        <div class="row g-2 mb-3">
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-primary dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= count($leadsTotal ?? []) ?></div>
                            <div class="stat-label">Total Leads</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-warning dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalUnassigned ?></div>
                            <div class="stat-label">Sin Asignar</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="card stat-card stat-card-success dashboard-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon me-2">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stat-number"><?= $totalAssigned ?></div>
                            <div class="stat-label">Asignados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 🔥 FILTRO POR AGENTE -->
        <!-- ============================================ -->
        <div class="card filtros-card mb-3">
            <div class="card-body">
                <form method="get" action="<?= Url::to(['lead-assign/index']) ?>" id="form-filtros">
                    <div class="row align-items-end g-2">
                        <div class="col-md-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="fas fa-filter"></i> Filtrar por Agente:
                            </label>
                        </div>
                        <div class="col-md-5">
                            <select class="form-select" name="agent_id" id="agent-select">
                                <option value="">Todos los agentes</option>
                                <?php foreach ($agents as $agent): ?>
                                    <?php 
                                    $agentName = trim($agent->name . ' ' . $agent->lastname1);
                                    if (empty($agentName)) {
                                        $agentName = $agent->username;
                                    }
                                    $count = isset($agentCounts[$agent->id_user]) ? $agentCounts[$agent->id_user] : 0;
                                    ?>
                                    <option value="<?= $agent->id_user ?>" 
                                        <?= $selectedAgentId == $agent->id_user ? 'selected' : '' ?>>
                                        <?= Html::encode($agentName) ?> — <?= $count ?> <?= $count == 1 ? 'lead' : 'leads' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-3 text-end">
                            <?php if ($selectedAgentId): ?>
                                <a href="<?= Url::to(['lead-assign/index']) ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i> Quitar filtro
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- CONTENIDO PRINCIPAL -->
        <!-- ============================================ -->
        <div class="row g-3">
            <!-- LEADS SIN ASIGNAR -->
            <div class="col-md-6">
                <div class="card assign-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-user-plus text-warning"></i>
                            <span>Leads Sin Asignar</span>
                        </div>
                        <span class="badge bg-warning text-dark"><?= count($unassignedLeads) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($unassignedLeads)): ?>
                            <?= Html::beginForm($massAssignUrl, 'post', ['id' => 'mass-assign-form']) ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 assign-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="30">
                                                    <input type="checkbox" id="select-all-unassigned" class="form-check-input">
                                                </th>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Teléfono</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($unassignedLeads as $index => $lead): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input lead-checkbox unassigned-checkbox" name="lead_ids[]" value="<?= $lead->id_lead ?>">
                                                    </td>
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                                        <?php if (!empty($lead->comments)): ?>
                                                            <br>
                                                            <small class="text-muted"><?= Html::encode($lead->comments) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-phone text-success"></i>
                                                        <?= $lead->phone ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusName = $lead->getStatusName();
                                                        $badgeClass = $lead->getStatusBadgeClass();
                                                        ?>
                                                        <span class="badge bg-<?= $badgeClass ?>">
                                                            <?= $statusName ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if (!empty($agents)): ?>
                                    <div class="card-footer">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-7">
                                                <?= Html::dropDownList('agent_id', null, $agentList, [
                                                    'prompt' => 'Seleccionar agente...',
                                                    'class' => 'form-select form-select-sm',
                                                    'id' => 'mass-agent-select',
                                                    'required' => true
                                                ]) ?>
                                            </div>
                                            <div class="col-5">
                                                <?= Html::submitButton('<i class="fas fa-users"></i> Asignar', [
                                                    'class' => 'btn btn-primary btn-sm w-100',
                                                    'id' => 'mass-assign-btn',
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <p>No hay leads sin asignar</p>
                                <span class="text-muted">Todos los leads están asignados a agentes</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- LEADS ASIGNADOS -->
            <div class="col-md-6">
                <div class="card assign-card">
                    <div class="card-header">
                        <div class="header-left">
                            <i class="fas fa-user-check text-success"></i>
                            <span>
                                Leads Asignados
                                <?php if ($selectedAgentId): ?>
                                    <?php 
                                    $selectedAgent = null;
                                    foreach ($agents as $a) {
                                        if ($a->id_user == $selectedAgentId) {
                                            $selectedAgent = $a;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($selectedAgent): ?>
                                        <?php 
                                        $selName = trim($selectedAgent->name . ' ' . $selectedAgent->lastname1);
                                        if (empty($selName)) {
                                            $selName = $selectedAgent->username;
                                        }
                                        ?>
                                        — <strong><?= Html::encode($selName) ?></strong>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <span class="badge bg-success"><?= count($assignedLeads) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($assignedLeads)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 assign-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Agente</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignedLeads as $index => $lead): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                                    <?php if (!empty($lead->comments)): ?>
                                                        <br>
                                                        <small class="text-muted"><?= Html::encode($lead->comments) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($lead->user && $lead->user->isAgent()): ?>
                                                        <?php 
                                                        $leadAgentName = trim($lead->user->name . ' ' . $lead->user->lastname1);
                                                        if (empty($leadAgentName)) {
                                                            $leadAgentName = $lead->user->username;
                                                        }
                                                        ?>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-user"></i> 
                                                            <?= Html::encode($leadAgentName) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Sin agente válido</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusName = $lead->getStatusName();
                                                    $badgeClass = $lead->getStatusBadgeClass();
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?>">
                                                        <?= $statusName ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button class="btn btn-danger btn-sm btn-action" 
                                                                title="Desasignar"
                                                                onclick="unassignLead(<?= $lead->id_lead ?>, '<?= Html::encode($lead->name . ' ' . $lead->lastname) ?>')">
                                                            <i class="fas fa-user-slash"></i>
                                                        </button>
                                                        <?= Html::a('<i class="fas fa-eye"></i>', ['lead/view', 'id' => $lead->id_lead], [
                                                            'class' => 'btn btn-info btn-sm btn-action',
                                                            'title' => 'Ver lead',
                                                            'target' => '_blank'
                                                        ]) ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>
                                    <?php if ($selectedAgentId): ?>
                                        Este agente no tiene leads asignados
                                    <?php else: ?>
                                        No hay leads asignados
                                    <?php endif; ?>
                                </p>
                                <span class="text-muted">
                                    <?php if ($selectedAgentId): ?>
                                        Selecciona otro agente o asigna leads desde la izquierda
                                    <?php else: ?>
                                        Asigna leads desde la columna izquierda
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function() {
    // ============================================
    // SELECCIÓN MÚLTIPLE - ACTUALIZAR BOTÓN
    // ============================================
    function updateMassAssignButton() {
        var checked = $('.unassigned-checkbox:checked').length;
        var btn = $('#mass-assign-btn');
        if (checked > 0) {
            btn.prop('disabled', false);
            btn.html('<i class="fas fa-users"></i> Asignar ' + checked);
        } else {
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-users"></i> Asignar');
        }
    }

    // Seleccionar todos
    $('#select-all-unassigned').on('change', function() {
        $('.unassigned-checkbox').prop('checked', $(this).prop('checked'));
        updateMassAssignButton();
    });

    // Checkbox individual
    $('.unassigned-checkbox').on('change', function() {
        updateMassAssignButton();
    });

    // Inicializar botón
    updateMassAssignButton();

    // ============================================
    // FILTRO AUTOMÁTICO AL CAMBIAR
    // ============================================
    $('#agent-select').on('change', function() {
        $('#form-filtros').submit();
    });

    // ============================================
    // DESASIGNAR LEAD
    // ============================================
    window.unassignLead = function(leadId, leadName) {
        if (confirm('¿Desasignar el lead "' + leadName + '" del agente?')) {
            var form = $('<form>', {
                'method': 'POST',
                'action': '<?= $unassignUrl ?>'
            });
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'lead_id',
                'value': leadId
            }));
            form.appendTo('body');
            form.submit();
        }
    };
});
</script>