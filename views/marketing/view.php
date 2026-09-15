<?php

use yii\helpers\Html;

$this->title = $model->typeLabel . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Marketing', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registrar CSS del marketing
$this->registerCssFile('@web/css/marketing.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$user = Yii::$app->user->identity;
$isAdmin = $user->isAdmin() || $user->isSuperAdmin();
?>

<div class="marketing-view">
    <div class="card shadow marketing-detail">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4><i class="fas <?= $model->typeIcon ?> me-2"></i> <?= Html::encode($this->title) ?></h4>
            <div>
                <span class="badge bg-light text-dark">
                    <i class="fas fa-tag me-1"></i> <?= $model->typeLabel ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card detail-card">
                        <div class="card-body">
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
                                <div class="col-sm-4 detail-label">Fecha de Inicio</div>
                                <div class="col-sm-8 detail-value">
                                    <?= $model->start_date ? date('d/m/Y', strtotime($model->start_date)) : 'N/A' ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Fecha de Fin</div>
                                <div class="col-sm-8 detail-value">
                                    <?= $model->end_date ? date('d/m/Y', strtotime($model->end_date)) : 'N/A' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card detail-card">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Estado</div>
                                <div class="col-sm-8 detail-value">
                                    <?= $model->getStatusBadge() ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Empresa</div>
                                <div class="col-sm-8 detail-value">
                                    <?php
                                    $company = $model->getCompany();
                                    echo $company ? Html::encode($company->name) : 'N/A';
                                    ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Comentarios</div>
                                <div class="col-sm-8 detail-value">
                                    <?= $model->comments ? Html::encode($model->comments) : '<span class="text-muted">Sin comentarios</span>' ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4 detail-label">Creado</div>
                                <div class="col-sm-8 detail-value">
                                    <?= $model->created_at ? date('d/m/Y H:i', strtotime($model->created_at)) : 'N/A' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-3">
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