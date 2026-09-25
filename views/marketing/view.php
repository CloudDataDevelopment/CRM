<?php

use yii\helpers\Html;

$this->title = $model->typeLabel . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$user = Yii::$app->user->identity;
$isAdmin = $user->isAdmin() || $user->isSuperAdmin();
?>

<div class="marketing-index">
    <div class="marketing-wrapper">

        <!-- HEADER -->
        <div class="marketing-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Marketing</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($model->name) ?></span>
                </div>
                <h1 class="page-title">
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                    'class' => 'btn btn-secondary btn-sm btn-header-action'
                ]) ?>
                <?php if ($isAdmin): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], [
                        'class' => 'btn btn-warning btn-sm btn-header-action'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm btn-header-action',
                        'data' => [
                            'confirm' => '¿Estás seguro de eliminar este elemento?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- MENSAJE DE ÉXITO -->
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- DETALLE -->
        <div class="marketing-detail">

            <!-- CARD PRINCIPAL -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <i class="fas <?= $model->typeIcon ?> me-2"></i>
                        <?= Html::encode($this->title) ?>
                    </h4>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-tag me-1"></i> <?= $model->typeLabel ?>
                    </span>
                </div>
                <div class="card-body">

                    <!-- INFO GENERAL -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="detail-card">
                                <div class="card-body">
                                    <h5 class="mb-3" style="font-size:0.9rem;font-weight:700;color:#0a48e2;">
                                        <i class="fas fa-info-circle"></i> Información General
                                    </h5>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">ID</div>
                                        <div class="col-sm-8 detail-value">#<?= $model->id ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Tipo</div>
                                        <div class="col-sm-8 detail-value">
                                            <span class="badge bg-primary"><?= $model->typeLabel ?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Nombre</div>
                                        <div class="col-sm-8 detail-value"><?= Html::encode($model->name) ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Estado</div>
                                        <div class="col-sm-8 detail-value">
                                            <?= $model->getStatusBadge() ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="detail-card">
                                <div class="card-body">
                                    <h5 class="mb-3" style="font-size:0.9rem;font-weight:700;color:#0a48e2;">
                                        <i class="fas fa-calendar-alt"></i> Vigencia
                                    </h5>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Fecha Inicio</div>
                                        <div class="col-sm-8 detail-value">
                                            <?= $model->start_date ? date('d/m/Y', strtotime($model->start_date)) : 'N/A' ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Fecha Fin</div>
                                        <div class="col-sm-8 detail-value">
                                            <?= $model->end_date ? date('d/m/Y', strtotime($model->end_date)) : 'N/A' ?>
                                        </div>
                                    </div>
                                    <?php
                                    if ($model->start_date && $model->end_date) {
                                        $inicio = strtotime($model->start_date);
                                        $fin = strtotime($model->end_date);
                                        $dias = floor(($fin - $inicio) / 86400);
                                        $estado = '';

                                        if (time() < $inicio) {
                                            $estado = '<span class="badge bg-warning">Próximamente</span>';
                                        } elseif (time() > $fin) {
                                            $estado = '<span class="badge bg-secondary">Finalizado</span>';
                                        } else {
                                            $estado = '<span class="badge bg-success">En curso</span>';
                                        }
                                    ?>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Duración</div>
                                        <div class="col-sm-8 detail-value"><?= $dias ?> día(s)</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4 detail-label">Situación</div>
                                        <div class="col-sm-8 detail-value"><?= $estado ?></div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔥 DESCRIPCIÓN DETALLADA (CENTRAL) -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="description-card">
                                <div class="description-header">
                                    <i class="fas fa-align-left"></i>
                                    Descripción Detallada
                                </div>
                                <div class="description-body">
                                    <?php if (!empty($model->comments)): ?>
                                        <div class="description-content">
                                            <?= nl2br(Html::encode($model->comments)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="description-empty">
                                            <i class="fas fa-info-circle fa-2x d-block mb-2"></i>
                                            <p class="mb-1">Sin descripción detallada registrada</p>
                                            <small class="text-muted">Edita este elemento para agregar información completa de la campaña o promoción</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- BOTONES INFERIORES -->
            <div class="form-group mt-3 d-flex gap-2 flex-wrap">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
                <?php if ($isAdmin): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => '¿Estás seguro de eliminar este elemento?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>