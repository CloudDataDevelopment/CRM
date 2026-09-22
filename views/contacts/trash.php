<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Papelera de Contactos';
$this->params['breadcrumbs'][] = ['label' => 'Contactos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/contacts.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);

$isAdmin = isset($isAdmin) ? $isAdmin : false;
$isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
$contacts = isset($contacts) ? $contacts : [];
$dataProvider = isset($dataProvider) ? $dataProvider : null;
$totalContacts = isset($totalContacts) ? $totalContacts : 0;
$search = isset($search) ? $search : '';
?>

<div class="contacts-trash">
    <div class="contacts-header">
        <div>
            <div class="breadcrumb-custom">
                <span>CRM</span><span class="separator">›</span>
                <span>Contactos</span><span class="separator">›</span>
                <span class="current">Papelera</span>
            </div>
            <h1 class="page-title">
                <i class="fas fa-trash text-danger me-2"></i>
                <?= Html::encode($this->title) ?>
                <small><?= date('d/m/Y H:i') ?></small>
            </h1>
        </div>
        <div class="header-actions">
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver a Contactos', ['index'], [
                'class' => 'btn btn-secondary btn-sm btn-header-action'
            ]) ?>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card filtros-card mb-3">
        <div class="card-body">
            <form method="get" action="<?= Url::to(['contacts/trash']) ?>" id="form-filtros">
                <div class="row align-items-end g-2">
                    <div class="col-md-1"><label class="form-label fw-bold mb-0">Buscar</label></div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Buscar contacto en papelera..." 
                               value="<?= Html::encode($search) ?>" id="search-input">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= Url::to(['contacts/trash']) ?>" class="btn btn-secondary w-100" title="Limpiar"><i class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card table-card">
        <div class="card-header">
            <div class="header-left">
                <i class="fas fa-trash"></i>
                <span>Contactos en Papelera</span>
                <span class="badge bg-danger ms-2"><?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Empresa</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contacts)): ?>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><strong><?= Html::encode($contact->getFullName()) ?></strong></td>
                                    <td><?= $contact->phone ?></td>
                                    <td>
                                        <?php if ($contact->email): ?>
                                            <a href="mailto:<?= Html::encode($contact->email) ?>" class="text-primary">
                                                <?= Html::encode($contact->email) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= Html::encode($contact->getTypeContactName()) ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $company = $contact->company;
                                        echo $company ? Html::encode($company->name) : '<span class="text-muted">Sin empresa</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?= Html::a(
                                                '<i class="fas fa-undo"></i> Restaurar',
                                                ['restore', 'id' => $contact->id_contact],
                                                [
                                                    'class' => 'btn btn-sm btn-success btn-action',
                                                    'title' => 'Restaurar contacto',
                                                    'data' => [
                                                        'confirm' => '¿Restaurar este contacto?',
                                                        'method' => 'post',
                                                    ],
                                                ]
                                            ) ?>
                                            <?= Html::a(
                                                '<i class="fas fa-eye"></i>',
                                                ['view', 'id' => $contact->id_contact],
                                                [
                                                    'class' => 'btn btn-sm btn-info btn-action',
                                                    'title' => 'Ver',
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-trash fa-3x d-block mb-3 text-muted"></i>
                                    <p>No hay contactos en la papelera.</p>
                                    <a href="<?= Url::to(['contacts/index']) ?>" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-arrow-left"></i> Volver a Contactos
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($dataProvider && $dataProvider->pagination->pageCount > 1): ?>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando <?= $dataProvider->getCount() ?> de <?= $dataProvider->getTotalCount ?> contactos
                </small>
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                    'linkOptions' => ['class' => 'page-link'],
                    'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                    'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                    'maxButtonCount' => 5,
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') document.getElementById('form-filtros').submit();
});
</script>