<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/create-empresa.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);
?>

<div class="empresa-view">
    <div class="create-content-wrapper">
        
        <!-- HEADER -->
        <div class="empresas-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span>
                    <span class="separator">›</span>
                    <span>Administración</span>
                    <span class="separator">›</span>
                    <span>Empresas</span>
                    <span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-building text-info me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id_company], [
                    'class' => 'btn btn-primary btn-sm'
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], [
                    'class' => 'btn btn-secondary btn-sm'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="empresa-form-box">
                    <div class="empresa-form-title">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información General
                    </div>
                    <div class="empresa-form-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'options' => ['class' => 'table table-striped detail-view-custom'],
                            'attributes' => [
                                'id_company',
                                'name',
                                'description',
                                'domain',
                                [
                                    'attribute' => 'id_status',
                                    'value' => function($model) {
                                        return $model->getStatusBadge();
                                    },
                                    'format' => 'raw',
                                ],
                                'type',
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- LOGO CARD -->
                <div class="modulos-card">
                    <div class="modulos-card-title">
                        <i class="fas fa-image text-primary me-2"></i> Logotipo
                    </div>
                    <div class="modulos-card-body text-center">
                        <?php if ($model->hasLogo()): ?>
                            <div class="empresa-logo-showcase">
                                <img src="<?= $model->getLogoUrl() ?>" 
                                     alt="<?= Html::encode($model->name) ?>"
                                     class="empresa-logo-large">
                            </div>
                        <?php else: ?>
                            <div class="empresa-logo-placeholder-large">
                                <i class="fas fa-building"></i>
                                <p class="text-muted mt-2 mb-0">Sin logotipo</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ESTADÍSTICAS -->
                <div class="modulos-card mt-3">
                    <div class="modulos-card-title">
                        <i class="fas fa-chart-bar text-primary me-2"></i> Estadísticas
                    </div>
                    <div class="modulos-card-body">
                        <div class="empresa-info-item">
                            <span class="info-label"><i class="fas fa-users text-primary"></i> Usuarios:</span>
                            <span class="info-value"><?= $model->getUsers()->count() ?></span>
                        </div>
                        <div class="empresa-info-item">
                            <span class="info-label"><i class="fas fa-user-tag text-success"></i> Leads:</span>
                            <span class="info-value"><?= $model->getLeads()->count() ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>