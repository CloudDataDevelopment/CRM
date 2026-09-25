<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Bienvenido al CRM';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/empresa.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);
?>

<div class="empresa-index">
    <div class="text-center mb-5">
        <h1 class="display-4">🏢 Bienvenido al CRM</h1>
        <p class="lead">Sistema de Gestión de Relaciones con Clientes</p>
        <hr class="my-4">
        <p>Selecciona una empresa para comenzar a gestionar sus citas, leads y ventas.</p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="fas fa-plus-circle"></i> Nueva Empresa', ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-plus-circle"></i> Nuevo Usuario', ['site/register'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="row">
        <?php foreach ($empresas as $empresa): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm empresa-card">
                <div class="card-body text-center">
                    <!-- 🔥 LOGO DE LA EMPRESA -->
                    <div class="mb-3">
                        <?php if ($empresa->hasLogo()): ?>
                            <img src="<?= $empresa->getLogoUrl() ?>" 
                                 alt="<?= Html::encode($empresa->name) ?>"
                                 class="empresa-logo-card">
                        <?php else: ?>
                            <div class="empresa-logo-card-placeholder">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5 class="card-title"><?= Html::encode($empresa->name) ?></h5>
                    <p class="card-text text-muted">
                        <?= Html::encode($empresa->description) ?: 'Sin descripción' ?>
                    </p>
                    <p class="card-text">
                        <small class="text-muted">
                            <i class="fas fa-globe"></i> <?= Html::encode($empresa->domain) ?: 'dominio.com' ?>
                        </small>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <?= Html::a('Entrar <i class="fas fa-arrow-right"></i>', ['dashboard', 'id' => $empresa->id_company], [
                            'class' => 'btn btn-primary btn-block'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $empresa->id_company], [
                            'class' => 'btn btn-warning btn-sm',
                            'title' => 'Editar empresa'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $empresa->id_company], [
                            'class' => 'btn btn-danger btn-sm',
                            'title' => 'Eliminar empresa',
                            'data' => [
                                'confirm' => '¿Estás seguro de eliminar esta empresa?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($empresas)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No hay empresas registradas.
        <?= Html::a('Crear primera empresa', ['create'], ['class' => 'btn btn-link']) ?>
    </div>
    <?php endif; ?>
</div>