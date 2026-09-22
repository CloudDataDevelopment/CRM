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
$createTypeUrl = Url::to(['contacts/create-type-ajax']);
?>

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
                                <!-- 🔥 SELECTOR DE TIPO CON OPCIÓN "OTRO" -->
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
                                
                                <!-- 🔥 CAMPO OCULTO PARA NUEVO TIPO -->
                                <div class="form-group mt-2" id="new-type-container" style="display: none;">
                                    <label class="control-label">
                                        Nuevo Tipo de Contacto <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                               id="new-type-name" 
                                               class="form-control" 
                                               placeholder="Ej: Proveedor, Cliente VIP..."
                                               maxlength="50">
                                        <button type="button" 
                                                class="btn btn-success" 
                                                id="btn-create-type">
                                            <i class="fas fa-plus"></i> Crear
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Escribe el nombre y presiona "Crear"
                                    </small>
                                    <div id="new-type-alert" class="mt-2" style="display: none;"></div>
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
                            <li><i class="fas fa-plus-circle text-primary"></i> Puedes crear un tipo nuevo con "Otro"</li>
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

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('contact-type-select');
    var newTypeContainer = document.getElementById('new-type-container');
    var newTypeName = document.getElementById('new-type-name');
    var btnCreateType = document.getElementById('btn-create-type');
    var newTypeAlert = document.getElementById('new-type-alert');
    var createTypeUrl = '<?= $createTypeUrl ?>';
    var csrfToken = '<?= Yii::$app->request->csrfToken ?>';
    var csrfParam = '<?= Yii::$app->request->csrfParam ?>';

    // 🔥 Mostrar/ocultar campo de nuevo tipo
    typeSelect.addEventListener('change', function() {
        if (this.value === '__new__') {
            newTypeContainer.style.display = 'block';
            newTypeName.focus();
        } else {
            newTypeContainer.style.display = 'none';
            newTypeName.value = '';
            newTypeAlert.style.display = 'none';
        }
    });

    // 🔥 Crear nuevo tipo vía AJAX
    btnCreateType.addEventListener('click', function() {
        var typeName = newTypeName.value.trim();

        if (!typeName) {
            newTypeAlert.style.display = 'block';
            newTypeAlert.className = 'alert alert-danger';
            newTypeAlert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Ingresa un nombre para el tipo.';
            return;
        }

        // Deshabilitar mientras se procesa
        btnCreateType.disabled = true;
        var originalText = btnCreateType.innerHTML;
        btnCreateType.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        var formData = new FormData();
        formData.append('type_contact', typeName);
        formData.append(csrfParam, csrfToken);

        fetch(createTypeUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btnCreateType.disabled = false;
            btnCreateType.innerHTML = originalText;

            if (data.success) {
                // 🔥 Agregar la nueva opción al select
                var newOption = new Option(data.type_contact, data.id_type_contact, true, true);

                // Insertar antes de la opción "Otro"
                var otroOption = typeSelect.querySelector('option[value="__new__"]');
                typeSelect.insertBefore(newOption, otroOption);

                // Seleccionar la nueva opción
                typeSelect.value = data.id_type_contact;

                // Ocultar campo de nuevo tipo
                newTypeContainer.style.display = 'none';
                newTypeName.value = '';

                // Mostrar éxito
                newTypeAlert.style.display = 'block';
                newTypeAlert.className = 'alert alert-success';
                newTypeAlert.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;

                setTimeout(function() {
                    newTypeAlert.style.display = 'none';
                }, 2500);
            } else {
                newTypeAlert.style.display = 'block';
                newTypeAlert.className = 'alert alert-danger';
                newTypeAlert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
            }
        })
        .catch(function(err) {
            btnCreateType.disabled = false;
            btnCreateType.innerHTML = originalText;
            newTypeAlert.style.display = 'block';
            newTypeAlert.className = 'alert alert-danger';
            newTypeAlert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error de conexión.';
        });
    });

    // 🔥 Enter en el campo de nuevo tipo
    newTypeName.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnCreateType.click();
        }
    });
});
</script>