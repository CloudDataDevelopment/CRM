<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Registrar Usuario';

// Variables del controlador
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$roleName = isset($roleName) ? $roleName : 'Usuario';
$rolesList = isset($rolesList) ? $rolesList : [];
$headerColor = isset($headerColor) ? $headerColor : 'bg-primary';
$backUrl = isset($backUrl) ? $backUrl : ['/dashboard/index'];
$backLabel = isset($backLabel) ? $backLabel : 'Volver';
$companiesList = isset($companiesList) ? $companiesList : [];
$showCompanyField = isset($showCompanyField) ? $showCompanyField : false;
$hideSidebar = isset($hideSidebar) ? $hideSidebar : false;
$useSimpleLayout = isset($useSimpleLayout) ? $useSimpleLayout : false;
$isFromEmpresa = isset($isFromEmpresa) ? $isFromEmpresa : false;
$isFromCrm = isset($isFromCrm) ? $isFromCrm : false;
?>

<div class="user-register site-register">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow">
                
                <!-- CARD HEADER -->
                <div class="card-header <?= $headerColor ?> text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i> 
                            <?= Html::encode($this->title) ?>
                        </h3>
                        <span class="badge bg-light text-dark">
                            <i class="fas <?= $isSuperAdmin ? 'fa-crown' : 'fa-user-shield' ?> me-1"></i>
                            <?= $roleName ?>
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mensajes flash -->
                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> 
                            <?= Yii::$app->session->getFlash('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i> 
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Información de acceso -->
                    <?php if ($isSuperAdmin): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> 
                            <strong>Acceso total:</strong> Puedes asignar cualquier rol y empresa.
                            <?php if ($useSimpleLayout): ?>
                                <br><i class="fas fa-arrow-left me-1"></i> 
                                <strong>Origen:</strong> Vienes desde <span class="badge bg-danger">Empresas</span>
                            <?php else: ?>
                                <br><i class="fas fa-bars me-1"></i> 
                                <strong>Origen:</strong> Vienes desde el <span class="badge bg-primary">Menú principal</span>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($isAdmin): ?>
                        <div class="alert alert-primary">
                            <i class="fas fa-info-circle me-2"></i> 
                            <strong>Acceso limitado:</strong> Puedes asignar roles de Administrador, Agente o Cliente.
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin(); ?>

                    <!-- Datos personales -->
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput([
                                'placeholder' => 'Nombre',
                                'class' => 'form-control'
                            ])->label('Nombre') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'lastname1')->textInput([
                                'placeholder' => 'Apellido Paterno',
                                'class' => 'form-control'
                            ])->label('Apellido Paterno') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'lastname2')->textInput([
                                'placeholder' => 'Apellido Materno',
                                'class' => 'form-control'
                            ])->label('Apellido Materno') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'phone')->textInput([
                                'placeholder' => 'Teléfono',
                                'maxlength' => 15,
                                'class' => 'form-control'
                            ])->label('Teléfono') ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'username')->textInput([
                        'placeholder' => 'Usuario',
                        'maxlength' => 20,
                        'class' => 'form-control'
                    ])->label('Usuario') ?>

                    <?= $form->field($model, 'email')->textInput([
                        'type' => 'email',
                        'placeholder' => 'correo@ejemplo.com',
                        'class' => 'form-control'
                    ])->label('Correo Electrónico') ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'password')->passwordInput([
                                'placeholder' => 'Mínimo 4 caracteres',
                                'class' => 'form-control'
                            ])->label('Contraseña') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'confirm_password')->passwordInput([
                                'placeholder' => 'Repita su contraseña',
                                'class' => 'form-control'
                            ])->label('Confirmar') ?>
                        </div>
                    </div>

                    <!-- Empresa (Solo para Super Admin) -->
                    <?php if ($showCompanyField && $isSuperAdmin): ?>
                        <div class="form-group">
                            <?= Html::activeLabel($model, 'id_company') ?>
                            <?= Html::activeDropDownList($model, 'id_company', 
                                $companiesList,
                                [
                                    'prompt' => 'Seleccione una empresa', 
                                    'class' => 'form-select',
                                    'id' => 'company-select'
                                ]
                            ) ?>
                            <?= Html::error($model, 'id_company', ['class' => 'help-block']) ?>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <strong><i class="fas fa-building"></i> Empresa:</strong>
                            El usuario será asignado a la empresa seleccionada.
                            <?php if (empty($companiesList)): ?>
                                <div class="text-danger mt-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>No hay empresas registradas.</strong> 
                                    Debes crear al menos una empresa antes de registrar usuarios.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Rol -->
                    <div class="form-group mt-3">
                        <?= Html::activeLabel($model, 'id_role') ?>
                        <?= Html::activeDropDownList($model, 'id_role', 
                            $rolesList,
                            [
                                'prompt' => 'Seleccione un rol', 
                                'class' => 'form-select',
                                'id' => 'role-select'
                            ]
                        ) ?>
                        <?= Html::error($model, 'id_role', ['class' => 'help-block']) ?>
                    </div>

                    <!-- Descripción de roles -->
                    <?php if ($isSuperAdmin): ?>
                        <div class="alert alert-info mt-3">
                            <strong><i class="fas fa-info-circle"></i> Roles disponibles:</strong>
                            <ul class="mb-0">
                                <li><span class="badge bg-danger">Super Admin</span> - Acceso total al sistema</li>
                                <li><span class="badge bg-warning">Administrador</span> - Gestión de empresa</li>
                                <li><span class="badge bg-info">Agente</span> - Gestión de ventas</li>
                                <li><span class="badge bg-secondary">Cliente</span> - Acceso limitado</li>
                            </ul>
                        </div>
                        <div class="alert alert-danger">
                            <strong><i class="fas fa-exclamation-triangle"></i> Precaución:</strong>
                            Asignar <strong>Super Administrador</strong> otorga acceso total al sistema.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-primary mt-3">
                            <strong><i class="fas fa-info-circle"></i> Roles disponibles:</strong>
                            <ul class="mb-0">
                                <li><span class="badge bg-warning">Administrador</span> - Gestión de empresa</li>
                                <li><span class="badge bg-info">Agente</span> - Gestión de ventas</li>
                                <li><span class="badge bg-secondary">Cliente</span> - Acceso limitado</li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Campo oculto para empresa (no Super Admin) -->
                    <?php if (!$showCompanyField): ?>
                        <?= $form->field($model, 'id_company')->hiddenInput()->label(false) ?>
                    <?php endif; ?>

                    <!-- Botones -->
                    <div class="form-group mt-4">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Registrar Usuario', [
                            'class' => 'btn ' . ($isSuperAdmin ? 'btn-danger' : 'btn-primary')
                        ]) ?>
                        <?= Html::a('<i class="fas fa-arrow-left"></i> ' . $backLabel, 
                            $backUrl, 
                            ['class' => 'btn btn-default']
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript de confirmación -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role-select');
    
    <?php if ($isSuperAdmin): ?>
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            if (this.value == '1') {
                if (!confirm(
                    '⚠️ ¿Estás seguro de asignar el rol de SUPER ADMINISTRADOR?\n\n' +
                    'Este usuario tendrá acceso total al sistema.\n\n' +
                    '¿Continuar?'
                )) {
                    this.value = '';
                    this.dispatchEvent(new Event('change'));
                }
            }
        });
    }
    <?php endif; ?>
    
    <?php if ($showCompanyField): ?>
    const companySelect = document.getElementById('company-select');
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (companySelect && !companySelect.value) {
                e.preventDefault();
                alert('⚠️ Por favor, selecciona una empresa para el usuario.');
                companySelect.focus();
                companySelect.style.borderColor = 'red';
            }
        });
        
        if (companySelect) {
            companySelect.addEventListener('change', function() {
                this.style.borderColor = '';
            });
        }
    }
    <?php endif; ?>
});
</script>