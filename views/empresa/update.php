<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Empresa: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id_company]];
$this->params['breadcrumbs'][] = 'Editar';

$this->registerCssFile('@web/css/create-empresa.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);
?>

<div class="empresa-update">
    <div class="create-content-wrapper">
        
        <div class="empresas-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Administración</span><span class="separator">›</span>
                    <span>Empresas</span><span class="separator">›</span>
                    <span class="current">Editar empresa</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Editar empresa
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::submitButton('<i class="fas fa-save me-1"></i> Guardar', [
                    'class' => 'btn btn-primary btn-sm',
                    'form' => 'empresa-form',
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left me-1"></i> Cancelar', ['index'], [
                    'class' => 'btn btn-secondary btn-sm'
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7">
                <div class="empresa-form-box">
                    <div class="empresa-form-title">
                        <i class="fas fa-building text-primary me-2"></i> Información de la Empresa
                    </div>
                    
                    <div class="empresa-form-body">
                        <?php $form = ActiveForm::begin([
                            'options' => [
                                'class' => 'needs-validation',
                                'id' => 'empresa-form',
                                'enctype' => 'multipart/form-data'
                            ],
                        ]); ?>

                        <!-- LOGOTIPO -->
                        <div class="empresa-logo-upload mb-3">
                            <label class="form-label">
                                <i class="fas fa-image text-primary me-1"></i> Logotipo de la empresa
                            </label>
                            <div class="logo-upload-area">
                                <div class="logo-preview-container">
                                    <?php if ($model->hasLogo()): ?>
                                        <img id="logo-preview-img" src="<?= $model->getLogoUrl() ?>" alt="Logo" style="display: block;">
                                        <div class="logo-placeholder" id="logo-placeholder" style="display: none;">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Subir PNG</span>
                                        </div>
                                    <?php else: ?>
                                        <img id="logo-preview-img" src="" alt="Preview" style="display: none;">
                                        <div class="logo-placeholder" id="logo-placeholder">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Subir PNG</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="logo-upload-info">
                                    <?= $form->field($model, 'logoFile')->fileInput([
                                        'accept' => 'image/png',
                                        'class' => 'form-control form-control-sm',
                                        'id' => 'logo-input',
                                    ])->label(false) ?>

                                    <div class="logo-requirements">
                                        <div class="requirement-title">
                                            <i class="fas fa-info-circle text-primary"></i>
                                            <strong><?= $model->hasLogo() ? 'Reemplazar logo:' : 'Requisitos del logo:' ?></strong>
                                        </div>
                                        <ul>
                                            <li><i class="fas fa-check-circle text-success"></i> Formato: <strong>Solo PNG</strong></li>
                                            <li><i class="fas fa-check-circle text-success"></i> Peso máximo: <strong>500 KB</strong></li>
                                            <li><i class="fas fa-check-circle text-success"></i> Dimensiones: <strong>200×200 a 2000×2000 px</strong></li>
                                            <li><i class="fas fa-check-circle text-success"></i> Proporción: <strong>cuadrada o rectangular</strong></li>
                                        </ul>
                                    </div>

                                    <?php if ($model->hasLogo()): ?>
                                        <div class="mt-2">
                                            <?= Html::a('<i class="fas fa-trash"></i> Eliminar logo actual', 
                                                ['delete-logo', 'id' => $model->id_company], [
                                                'class' => 'btn btn-sm btn-outline-danger',
                                                'data' => [
                                                    'confirm' => '¿Eliminar el logotipo actual?',
                                                    'method' => 'post',
                                                ],
                                            ]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div id="logo-error-container"></div>
                        </div>

                        <!-- NOMBRE Y DOMINIO -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'class' => 'form-control',
                                ])->label('Nombre de empresa <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'domain')->textInput([
                                    'maxlength' => true,
                                    'class' => 'form-control',
                                ])->label('Dominio') ?>
                            </div>
                        </div>

                        <!-- TIPO -->
                        <?= $form->field($model, 'type')->dropDownList([
                            'Tecnología' => 'Tecnología',
                            'Marketing' => 'Marketing',
                            'Consultoría' => 'Consultoría',
                            'Finanzas' => 'Finanzas',
                            'Salud' => 'Salud',
                            'Educación' => 'Educación',
                            'Comercio' => 'Comercio',
                            'Otro' => 'Otro',
                        ], ['prompt' => 'Seleccione un tipo', 'class' => 'form-select'])->label('Tipo de empresa') ?>

                        <!-- DESCRIPCIÓN -->
                        <?= $form->field($model, 'description')->textarea([
                            'rows' => 4,
                            'class' => 'form-control',
                            'style' => 'resize: vertical;',
                        ])->label('Descripción') ?>

                        <!-- ESTADO -->
                        <?php if (isset($statusList) && !empty($statusList)): ?>
                            <?= $form->field($model, 'id_status')->dropDownList(
                                $statusList,
                                ['prompt' => 'Seleccione un estado', 'class' => 'form-select']
                            )->label('Estado') ?>
                        <?php endif; ?>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="modulos-card">
                    <div class="modulos-card-title">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información
                    </div>
                    <div class="modulos-card-body">
                        <div class="empresa-info-item">
                            <span class="info-label">ID:</span>
                            <span class="info-value">#<?= $model->id_company ?></span>
                        </div>
                        <div class="empresa-info-item">
                            <span class="info-label">Estado:</span>
                            <span class="info-value"><?= $model->getStatusBadge() ?></span>
                        </div>
                        <div class="empresa-info-item">
                            <span class="info-label">Usuarios:</span>
                            <span class="info-value"><?= $model->getUsers()->count() ?></span>
                        </div>
                        <div class="empresa-info-item">
                            <span class="info-label">Leads:</span>
                            <span class="info-value"><?= $model->getLeads()->count() ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('logo-input').addEventListener('change', function(e) {
    var file = e.target.files[0];
    var previewImg = document.getElementById('logo-preview-img');
    var placeholder = document.getElementById('logo-placeholder');
    var errorContainer = document.getElementById('logo-error-container');

    errorContainer.innerHTML = '';

    if (!file) return;

    // Validar extensión
    var extension = file.name.split('.').pop().toLowerCase();
    if (extension !== 'png') {
        showLogoError('Solo se permiten archivos PNG.');
        resetLogoInput(e);
        return;
    }

    // Validar MIME
    if (file.type !== 'image/png') {
        showLogoError('El archivo no es PNG válido.');
        resetLogoInput(e);
        return;
    }

    // Validar tamaño
    if (file.size > 500 * 1024) {
        showLogoError('El archivo pesa ' + formatBytes(file.size) + '. Máximo: 500 KB.');
        resetLogoInput(e);
        return;
    }

    // Validar dimensiones
    var img = new Image();
    img.onload = function() {
        var width = img.width;
        var height = img.height;

        if (width < 200 || height < 200) {
            showLogoError('Imagen muy pequeña (' + width + '×' + height + ' px). Mínimo: 200×200 px.');
            resetLogoInput(e);
            return;
        }

        if (width > 2000 || height > 2000) {
            showLogoError('Imagen muy grande (' + width + '×' + height + ' px). Máximo: 2000×2000 px.');
            resetLogoInput(e);
            return;
        }

        var ratio = width / height;
        if (ratio < 0.33 || ratio > 3) {
            showLogoError('Proporción no válida. Usa imagen cuadrada o rectangular.');
            resetLogoInput(e);
            return;
        }

        previewImg.src = img.src;
        previewImg.style.display = 'block';
        placeholder.style.display = 'none';
    };
    img.src = URL.createObjectURL(file);
});

function showLogoError(message) {
    var errorContainer = document.getElementById('logo-error-container');
    errorContainer.innerHTML = '<div class="alert alert-danger mt-2 mb-0" style="font-size: 0.8rem;">' +
        '<i class="fas fa-exclamation-triangle"></i> ' + message + '</div>';
}

function resetLogoInput(e) {
    e.target.value = '';
    document.getElementById('logo-preview-img').style.display = 'none';
    document.getElementById('logo-placeholder').style.display = 'flex';
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}
</script>