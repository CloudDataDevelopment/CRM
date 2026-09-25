<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Nueva Evaluación';
$this->params['breadcrumbs'][] = ['label' => 'Evaluaciones', 'url' => ['index']];

// 🔥 Si viene con leadId, mostrar breadcrumb del lead
$lead = isset($lead) ? $lead : null;
$leadId = isset($leadId) ? $leadId : null;

if ($lead) {
    $this->title = 'Nueva Evaluación - ' . $lead->name . ' ' . $lead->lastname;
    $this->params['breadcrumbs'][] = [
        'label' => $lead->name . ' ' . $lead->lastname,
        'url' => ['lead/details', 'id' => $lead->id_lead]
    ];
}
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/leads.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/evaluaciones.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class], 'position' => \yii\web\View::POS_HEAD]);

$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isAgent = isset($isAgent) ? $isAgent : false;
$statusOptions = isset($statusOptions) ? $statusOptions : [];
$leadsList = isset($leadsList) ? $leadsList : [];
$companyList = isset($companyList) ? $companyList : [];
?>

<!-- 🔥 CONTENEDOR PRINCIPAL CON SOMBRA -->
<div class="report-create">
    <div class="leads-wrapper">

        <!-- HEADER -->
        <div class="leads-header">
            <div>
                <div class="breadcrumb-custom">
                    <span>CRM</span><span class="separator">›</span>
                    <?php if ($lead): ?>
                        <span>Leads</span><span class="separator">›</span>
                        <span><?= Html::encode($lead->name) ?></span><span class="separator">›</span>
                    <?php endif; ?>
                    <span>Evaluaciones</span><span class="separator">›</span>
                    <span class="current">Nueva</span>
                </div>
                <h1 class="page-title">
                    <i class="fas fa-plus text-success me-2"></i>
                    <?= Html::encode($this->title) ?>
                    <small><?= date('d/m/Y H:i') ?></small>
                </h1>
            </div>
            <div class="header-actions">
                <?php if ($lead): ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al Lead',
                        ['lead/details', 'id' => $lead->id_lead],
                        ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Volver',
                        ['index'],
                        ['class' => 'btn btn-secondary btn-sm btn-header-action']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- COLUMNA IZQUIERDA: FORMULARIO -->
            <div class="col-md-8">
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check"></i> Información de la Evaluación
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'id' => 'evaluacion-form',
                            'fieldConfig' => [
                                'options' => ['class' => 'form-group mb-3'],
                                'labelOptions' => ['class' => 'control-label'],
                                'errorOptions' => ['class' => 'help-block'],
                            ],
                        ]); ?>

                        <!-- Nombre de la Evaluación -->
                        <?= $form->field($model, 'report_name')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Ej: Evaluación inicial, Seguimiento mensual...',
                            'class' => 'form-control',
                        ])->label('Nombre de la Evaluación <span class="text-danger">*</span>') ?>

                        <!-- Tipo de Evaluación -->
                        <?= $form->field($model, 'report_type')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Ej: Inicial, Seguimiento, Cierre',
                            'class' => 'form-control',
                        ])->label('Tipo de Evaluación')
                        ->hint('Opcional: clasifica el tipo de evaluación', ['class' => 'text-muted']) ?>

                        <div class="row">
                            <!-- Fecha -->
                            <div class="col-md-6">
                                <?= $form->field($model, 'date_report')->input('date', [
                                    'class' => 'form-control',
                                ])->label('Fecha') ?>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6">
                                <?= $form->field($model, 'id_status')->dropDownList(
                                    $statusOptions,
                                    ['prompt' => 'Seleccione un estado...', 'class' => 'form-select']
                                )->label('Estado <span class="text-danger">*</span>') ?>
                            </div>
                        </div>

                        <!-- 🔥 LEAD: si viene preseleccionado, mostrar info bloqueada -->
                        <?php if ($lead): ?>
                            <div class="form-group mb-3">
                                <label class="control-label">Lead Asociado</label>
                                <div class="lead-locked-box">
                                    <div class="lead-locked-header">
                                        <i class="fas fa-user"></i>
                                        <strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong>
                                        <span class="badge bg-<?= $lead->getStatusBadgeClass() ?> ms-auto">
                                            <?= $lead->getStatusName() ?>
                                        </span>
                                    </div>
                                    <div class="lead-locked-info">
                                        <span>
                                            <i class="fas fa-phone"></i>
                                            <?= Html::encode($lead->phone) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            Registrado: <?= date('d/m/Y', strtotime($lead->created_at)) ?>
                                        </span>
                                        <?php if ($lead->company): ?>
                                            <span>
                                                <i class="fas fa-building"></i>
                                                <?= Html::encode($lead->company->name) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($lead->comments): ?>
                                            <span>
                                                <i class="fas fa-comment"></i>
                                                <?= Html::encode(mb_substr($lead->comments, 0, 50)) ?><?= mb_strlen($lead->comments) > 50 ? '...' : '' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-lock"></i> Este lead está vinculado automáticamente a la evaluación.
                                    </small>
                                </div>
                                <?= Html::activeHiddenInput($model, 'id_lead', ['value' => $lead->id_lead]) ?>
                            </div>
                        <?php else: ?>
                            <?= $form->field($model, 'id_lead')->dropDownList(
                                $leadsList,
                                ['prompt' => 'Seleccione un lead...', 'class' => 'form-select']
                            )->label('Lead Asociado')
                            ->hint('Vincula esta evaluación con un lead específico', ['class' => 'text-muted']) ?>
                        <?php endif; ?>

                        <!-- Empresa (solo SuperAdmin y sin lead preseleccionado) -->
                        <?php if ($isSuperAdmin && !$lead): ?>
                            <?= $form->field($model, 'id_company')->dropDownList(
                                $companyList,
                                ['prompt' => 'Seleccione empresa...', 'class' => 'form-select']
                            )->label('Empresa') ?>
                        <?php endif; ?>

                        <!-- BOTONES -->
                        <div class="form-group mt-4 d-flex gap-2 flex-wrap">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Crear Evaluación', [
                                'class' => 'btn btn-success'
                            ]) ?>
                            <?php if ($lead): ?>
                                <?= Html::a('<i class="fas fa-times"></i> Cancelar', 
                                    ['lead/details', 'id' => $lead->id_lead], 
                                    ['class' => 'btn btn-secondary']) ?>
                            <?php else: ?>
                                <?= Html::a('<i class="fas fa-times"></i> Cancelar', 
                                    ['index'], 
                                    ['class' => 'btn btn-secondary']) ?>
                            <?php endif; ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: SIDEBAR -->
            <div class="col-md-4">

                <?php if ($lead): ?>
                    <!-- 🔥 CARD DEL LEAD VINCULADO -->
                    <div class="card details-card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-check text-primary"></i> Lead Vinculado
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-avatar-large">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h5 class="profile-name-large mt-2">
                                <?= Html::encode($lead->name . ' ' . $lead->lastname) ?>
                            </h5>
                            <p class="profile-phone-large">
                                <i class="fas fa-phone"></i> <?= Html::encode($lead->phone) ?>
                            </p>

                            <div class="mb-2">
                                <span class="badge bg-<?= $lead->getStatusBadgeClass() ?>">
                                    <i class="fas <?= $lead->getStatusIcon() ?>"></i>
                                    <?= $lead->getStatusName() ?>
                                </span>
                            </div>

                            <div class="profile-actions-large">
                                <a href="tel:<?= Html::encode($lead->phone) ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-phone"></i> Llamar
                                </a>
                                <?php if ($lead->phone): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead->phone) ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                    </a>
                                <?php endif; ?>
                                <?= Html::a('<i class="fas fa-eye"></i> Ver',
                                    ['lead/details', 'id' => $lead->id_lead],
                                    ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CARD: INFORMACIÓN -->
                <div class="card details-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary"></i> Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            El <strong>nombre</strong> de la evaluación es obligatorio.
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Selecciona un <strong>estado</strong> para la evaluación.
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            El <strong>tipo</strong> es opcional (Inicial, Seguimiento, Cierre).
                        </p>
                        <?php if ($lead): ?>
                            <hr>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-lock text-primary me-1"></i>
                                Esta evaluación quedará vinculada automáticamente al lead
                                <strong><?= Html::encode($lead->name) ?></strong>.
                            </p>
                        <?php else: ?>
                            <hr>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-user text-primary me-1"></i>
                                Puedes vincular la evaluación con un lead específico usando el selector.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- /.leads-wrapper -->
</div><!-- /.report-create -->