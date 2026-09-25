<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nuevo Contacto';
$this->params['breadcrumbs'][] = ['label' => 'Contactos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/contacts.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="contacts-create">
    <div class="contacts-wrapper">

        <!-- HEADER -->
        <div class="contacts-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <span>Contactos</span><span class="separator">›</span>
                    <span class="current"><?= Html::encode($this->title) ?></span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-user-plus text-success me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card table-card">
                    <div class="card-body">
                        <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ingresa el nombre',
                                    'class' => 'form-control'
                                ])->label('Nombre <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'last_name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ingresa el apellido',
                                    'class' => 'form-control'
                                ])->label('Apellido <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'phone')->textInput([
                            'type' => 'tel',
                            'placeholder' => 'Ej: 5512345678',
                            'maxlength' => 10,
                            'class' => 'form-control'
                        ])->label('Teléfono')
                        ->hint('Ingresa 10 dígitos sin espacios ni guiones', ['class' => 'text-muted']) ?>

                        <?= $form->field($model, 'email')->textInput([
                            'type' => 'email',
                            'placeholder' => 'Ej: contacto@email.com',
                            'maxlength' => 25,
                            'class' => 'form-control'
                        ])->label('Correo Electrónico') ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Tipo de Contacto</label>
                                    <select name="Contacts[id_type_contact]" id="contact-type-select" class="form-select">
                                        <option value="">Seleccione un tipo...</option>
                                        <?php foreach ($typeList as $id => $nombre): ?>
                                            <option value="<?= $id ?>" <?= ($model->id_type_contact == $id) ? 'selected' : '' ?>>
                                                <?= Html::encode($nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="__new__">➕ Otro (crear nuevo tipo)</option>
                                    </select>
                                </div>

                                <div class="form-group mt-2" id="new-type-container" style="display: none;">
                                    <label class="control-label">
                                        Nuevo Tipo de Contacto <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="new_type_contact"
                                           id="new-type-name"
                                           class="form-control"
                                           placeholder="Ej: Proveedor, Cliente VIP..."
                                           maxlength="50">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        El tipo se creará automáticamente al guardar el contacto
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'id_status')->dropDownList(
                                    $statusList,
                                    ['prompt' => 'Seleccione un estado...', 'class' => 'form-select']
                                )->label('Estado <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Contacto', [
                                'class' => 'btn btn-success'
                            ]) ?>
                            <?= Html::a('Cancelar', ['index'], [
                                'class' => 'btn btn-secondary'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card table-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Consejos:</strong></p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success"></i> Nombre y apellido son obligatorios</li>
                            <li><i class="fas fa-check-circle text-success"></i> Teléfono de 10 dígitos</li>
                            <li><i class="fas fa-check-circle text-success"></i> Email válido (opcional)</li>
                            <li><i class="fas fa-check-circle text-success"></i> Selecciona un tipo de contacto</li>
                            <li><i class="fas fa-plus-circle text-primary"></i> Si eliges "Otro", el tipo se creará al guardar</li>
                        </ul>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-address-book"></i> Resumen
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="fas fa-arrow-right"></i>
                            Los contactos te permiten gestionar todas las personas relacionadas con tu negocio.
                        </p>
                        <hr>
                        <p class="text-muted small">
                            <i class="fas fa-arrow-right"></i>
                            Puedes clasificarlos por tipo y estado para mejor organización.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.contacts-wrapper -->
</div><!-- /.contacts-create -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('contact-type-select');
    var newTypeContainer = document.getElementById('new-type-container');
    var newTypeName = document.getElementById('new-type-name');
    var form = document.getElementById('contact-form');

    typeSelect.addEventListener('change', function() {
        if (this.value === '__new__') {
            newTypeContainer.style.display = 'block';
            newTypeName.focus();
        } else {
            newTypeContainer.style.display = 'none';
            newTypeName.value = '';
        }
    });

    form.addEventListener('submit', function(e) {
        if (typeSelect.value === '__new__') {
            var typeName = newTypeName.value.trim();

            if (!typeName) {
                e.preventDefault();
                alert('Ingresa un nombre para el nuevo tipo de contacto.');
                newTypeName.focus();
                return false;
            }

            if (typeName.length < 2) {
                e.preventDefault();
                alert('El nombre del tipo debe tener al menos 2 caracteres.');
                newTypeName.focus();
                return false;
            }
        }
    });

    newTypeName.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            form.querySelector('button[type="submit"]').focus();
        }
    });
});
</script>