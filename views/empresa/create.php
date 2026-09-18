<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Nueva Empresa';
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/create-empresa.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD,
]);
?>

<div class="empresa-create">
    <div class="create-content-wrapper">
        
        <div class="empresas-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Administración</span><span class="separator">›</span>
                    <span>Empresas</span><span class="separator">›</span>
                    <span class="current">Nueva empresa</span>
                </div>
                <h1 class="page-title">
                    Nueva empresa
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::submitButton('<i class="fas fa-save me-1"></i> Guardar', [
                    'class' => 'btn btn-success btn-sm',
                    'form' => 'empresa-form',
                ]) ?>
                <?= Html::resetButton('<i class="fas fa-undo me-1"></i> Limpiar', [
                    'class' => 'btn btn-outline-secondary btn-sm',
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
                                    <div class="logo-placeholder" id="logo-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Subir PNG</span>
                                    </div>
                                    <img id="logo-preview-img" src="" alt="Preview" style="display: none;">
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
                                            <strong>Requisitos del logo:</strong>
                                        </div>
                                        <ul>
                                            <li><i class="fas fa-check-circle text-success"></i> Formato: <strong>Solo PNG</strong></li>
                                            <li><i class="fas fa-check-circle text-success"></i> Peso máximo: <strong>500 KB</strong></li>
                                            <li><i class="fas fa-check-circle text-success"></i> Dimensiones: <strong>200×200 a 2000×2000 px</strong></li>
                                            <li><i class="fas fa-check-circle text-success"></i> Proporción: <strong>cuadrada o rectangular</strong></li>
                                            <li><i class="fas fa-star text-warning"></i> Recomendado: <strong>500×500 px</strong> con fondo transparente</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div id="logo-error-container"></div>
                        </div>

                        <!-- NOMBRE Y DOMINIO -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ej. Trivana',
                                    'class' => 'form-control',
                                ])->label('Nombre de empresa <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'domain')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ej. trivana.com',
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
                        ], [
                            'prompt' => 'Seleccione un tipo',
                            'class' => 'form-select',
                        ])->label('Tipo de empresa') ?>

                        <!-- DESCRIPCIÓN -->
                        <?= $form->field($model, 'description')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Descripción de la empresa...',
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
                        <i class="fas fa-cog text-primary me-2"></i> Módulos del CRM
                    </div>
                    <div class="modulos-card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle"></i> 
                            Selecciona los módulos que estarán disponibles para esta empresa
                        </p>
                        <div class="modulos-list">
                            <?php
                            $modulos = [
                                ['id' => 'modulo-contactos',   'icon' => 'fa-address-book',  'color' => 'primary', 'label' => 'Gestión de contactos', 'checked' => true],
                                ['id' => 'modulo-leads',       'icon' => 'fa-users',         'color' => 'success', 'label' => 'Leads',                'checked' => true],
                                ['id' => 'modulo-ventas',      'icon' => 'fa-shopping-cart', 'color' => 'warning', 'label' => 'Ventas',               'checked' => true],
                                ['id' => 'modulo-tareas',      'icon' => 'fa-tasks',         'color' => 'info',    'label' => 'Tareas',               'checked' => true],
                                ['id' => 'modulo-calendario',  'icon' => 'fa-calendar-alt',  'color' => 'danger',  'label' => 'Calendario',           'checked' => true],
                                ['id' => 'modulo-reportes',    'icon' => 'fa-chart-bar',     'color' => 'success', 'label' => 'Reportes',             'checked' => true],
                                ['id' => 'modulo-dashboard',   'icon' => 'fa-tachometer-alt','color' => 'primary', 'label' => 'Dashboard',            'checked' => true],
                                ['id' => 'modulo-marketing',   'icon' => 'fa-bullhorn',      'color' => 'info',    'label' => 'Marketing',            'checked' => false],
                                ['id' => 'modulo-cotizaciones','icon' => 'fa-file-invoice',  'color' => 'warning', 'label' => 'Cotizaciones',         'checked' => true],
                                ['id' => 'modulo-seguimientos','icon' => 'fa-phone',         'color' => 'success', 'label' => 'Seguimientos',         'checked' => true],
                            ];
                            foreach ($modulos as $modulo): ?>
                                <div class="modulo-item">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="<?= $modulo['id'] ?>" <?= $modulo['checked'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?= $modulo['id'] ?>">
                                            <i class="fas <?= $modulo['icon'] ?> text-<?= $modulo['color'] ?>"></i>
                                            <?= $modulo['label'] ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modulos-card-footer">
                        <span class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Los módulos pueden cambiarse después
                        </span>
                        <button type="button" class="btn-select-all" onclick="seleccionarTodos()">
                            <i class="fas fa-check-double"></i> Seleccionar todos
                        </button>
                    </div>
                </div>

                <div class="tips-card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Consejos Rápidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="tips-list">
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Logo:</strong> PNG 500×500 px con fondo transparente.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Nombre:</strong> Nombre completo de la empresa.
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Módulos:</strong> Activa los necesarios.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// VALIDACIÓN Y PREVIEW DEL LOGO (SOLO PNG)
// ============================================
document.getElementById('logo-input').addEventListener('change', function(e) {
    var file = e.target.files[0];
    var previewImg = document.getElementById('logo-preview-img');
    var placeholder = document.getElementById('logo-placeholder');
    var errorContainer = document.getElementById('logo-error-container');

    // Limpiar errores
    errorContainer.innerHTML = '';

    if (!file) {
        previewImg.style.display = 'none';
        placeholder.style.display = 'flex';
        return;
    }

    // 🔥 1. Validar extensión PNG
    var extension = file.name.split('.').pop().toLowerCase();
    if (extension !== 'png') {
        showLogoError('Solo se permiten archivos PNG. El archivo seleccionado es .' + extension);
        resetLogoInput(e);
        return;
    }

    // 🔥 2. Validar tipo MIME
    if (file.type !== 'image/png') {
        showLogoError('El archivo no es una imagen PNG válida.');
        resetLogoInput(e);
        return;
    }

    // 🔥 3. Validar tamaño (500 KB)
    var maxSize = 500 * 1024;
    if (file.size > maxSize) {
        showLogoError('El archivo pesa ' + formatBytes(file.size) + '. El máximo permitido es 500 KB.');
        resetLogoInput(e);
        return;
    }

    // 🔥 4. Validar dimensiones
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
            showLogoError('Proporción no válida (' + width + '×' + height + '). Usa imagen cuadrada o rectangular.');
            resetLogoInput(e);
            return;
        }

        // ✅ Todo válido → mostrar preview
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

// Seleccionar todos los módulos
function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('.modulo-item .form-check-input');
    const todosActivos = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => { cb.checked = !todosActivos; });
}
</script>