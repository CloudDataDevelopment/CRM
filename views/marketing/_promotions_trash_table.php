<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

$isAdmin = isset($isAdmin) ? $isAdmin : false;
?>

<div class="marketing-table">
    <div class="card table-card">
        <div class="card-header">
            <div class="header-left">
                <i class="fas fa-trash text-danger"></i>
                <span>Promociones Eliminadas</span>
                <span class="badge bg-danger ms-2"><?= $dataProvider->getTotalCount() ?></span>
            </div>
            <div class="header-right">
                <span class="badge bg-secondary">
                    Página <?= $dataProvider->getPagination()->getPage() + 1 ?> de <?= $dataProvider->getPagination()->getPageCount() ?>
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dataProvider->getCount() > 0): ?>
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                                <tr>
                                    <td><strong><?= Html::encode($model->promotion_name) ?></strong></td>
                                    <td>
                                        <?= $model->start_date ? date('d/m/Y', strtotime($model->start_date)) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <?= $model->end_date ? date('d/m/Y', strtotime($model->end_date)) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <?= $model->getStatusBadge() ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id_promotion], [
                                                'class' => 'btn btn-sm marketing-btn-action btn-outline-primary',
                                                'title' => 'Ver',
                                            ]) ?>
                                            <?php if ($isAdmin): ?>
                                                <?= Html::a('<i class="fas fa-undo"></i> Restaurar', ['restore', 'id' => $model->id_promotion], [
                                                    'class' => 'btn btn-sm marketing-btn-action btn-outline-success',
                                                    'title' => 'Restaurar promoción',
                                                    'data' => [
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-trash fa-2x d-block mb-2"></i>
                                    No hay promociones en la papelera
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            Mostrando <?= $dataProvider->getCount() ?> de <?= $dataProvider->getTotalCount() ?> promociones
                        </small>
                    </div>
                    <div class="col-md-6">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->getPagination(),
                            'options' => ['class' => 'pagination justify-content-end mb-0'],
                            'linkOptions' => ['class' => 'page-link'],
                            'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                            'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                            'maxButtonCount' => 5,
                            'hideOnSinglePage' => false,
                        ]) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>