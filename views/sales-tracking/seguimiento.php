<?php

use yii\helpers\Html;

// Registrar CSS externo
$this->registerCssFile('@web/css/sales-tracking-seguimiento.css');

$this->title = 'Seguimiento de Lead #' . $model->id_lead;
$this->params['breadcrumbs'][] = ['label' => 'Seguimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// 🔥 OBTENER ESTADOS DESDE LA TABLA STATUS
$statusOptions = \app\models\Status::find()
    ->select(['status', 'id_status'])
    ->indexBy('id_status')
    ->column();
?>

<div class="sales-tracking-seguimiento">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-clipboard-list"></i> Seguimiento de Lead
                    </h3>
                    <div class="card-tools float-right">
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                            'class' => 'btn btn-secondary btn-sm text-white'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-plus"></i> Agregar seguimiento', 
                            ['create', 'leadId' => $model->id_lead], 
                            ['class' => 'btn btn-success btn-sm text-white']
                        ) ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información del Lead -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-user"></i> Lead:</strong> 
                                <?= Html::encode($model->name . ' ' . $model->lastname) ?>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-phone"></i> Teléfono:</strong> 
                                <?= Html::encode($model->phone) ?>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fas fa-calendar"></i> Registro:</strong> 
                                <?= date('d/m/Y', strtotime($model->created_at)) ?>
                            </div>
                        </div>
                        <?php if ($model->comments): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong><i class="fas fa-comment"></i> Observaciones:</strong> 
                                    <?= nl2br(Html::encode($model->comments)) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Historial de Seguimientos -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-history"></i> Historial de Seguimientos
                                <span class="badge bg-light text-dark float-end"><?= count($seguimientos) ?></span>
                            </h5>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <?php if (!empty($seguimientos)): ?>
                                <div class="timeline">
                                    <?php foreach ($seguimientos as $index => $seg): ?>
                                        <?php
                                        // 🔥 OBTENER ESTADO DESDE id_status
                                        $statusName = $seg->getStatusName();
                                        $badgeClass = $seg->getStatusBadgeClass();
                                        ?>
                                        <div class="timeline-item">
                                            <div class="timeline-badge <?= $index == 0 ? 'bg-success' : 'bg-secondary' ?>"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between">
                                                    <strong>
                                                        <span class="badge bg-<?= $badgeClass ?>">
                                                            <?= $statusName ?>
                                                        </span>
                                                        #<?= $index + 1 ?>
                                                    </strong>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> 
                                                        <?= date('d/m/Y', strtotime($seg->date_s)) ?>
                                                        <?php if ($seg->hour): ?>
                                                            <i class="fas fa-clock"></i> 
                                                            <?= date('H:i', strtotime($seg->hour)) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <?php if ($seg->comments): ?>
                                                    <p class="mt-1 mb-0">
                                                        <i class="fas fa-quote-left text-muted"></i>
                                                        <?= Html::encode($seg->comments) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($seg->date_f): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-check"></i> 
                                                        Próxima: <?= date('d/m/Y', strtotime($seg->date_f)) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-comment fa-3x d-block mb-3"></i>
                                    <p>No hay seguimientos registrados para este lead.</p>
                                    <?= Html::a('<i class="fas fa-plus"></i> Agregar primer seguimiento', 
                                        ['create', 'leadId' => $model->id_lead], 
                                        ['class' => 'btn btn-success']
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver a seguimientos', ['index'], [
                        'class' => 'btn btn-secondary'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-user"></i> Ver lead', ['lead/view', 'id' => $model->id_lead], [
                        'class' => 'btn btn-info'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CSS PARA EL TIMELINE                         -->
<!-- ============================================ -->
<style>
.timeline {
    position: relative;
    padding: 0;
}

.timeline-item {
    display: flex;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-badge {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #28a745;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    margin-top: 2px;
}

.timeline-content {
    flex: 1;
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    position: relative;
}

.timeline-content::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 14px;
    border: 8px solid transparent;
    border-right-color: #f8f9fa;
}

.timeline-content .badge {
    font-size: 0.75rem;
    padding: 4px 10px;
}

@media (max-width: 768px) {
    .timeline-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .timeline-badge {
        margin-bottom: 8px;
    }
    
    .timeline-content::before {
        display: none;
    }
}
</style>