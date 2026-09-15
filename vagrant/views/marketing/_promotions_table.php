<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\MarketingSearch;

$isAdmin = isset($isAdmin) ? $isAdmin : false;
?>

<div class="marketing-table">
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-hover'],
            'filterRowOptions' => ['class' => 'filter-row'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'label' => 'Nombre',
                    'value' => 'name',
                    'contentOptions' => ['style' => 'font-weight: 500;'],
                    'filterInputOptions' => [
                        'class' => 'form-control form-control-sm',
                        'placeholder' => 'Buscar por nombre...',
                    ],
                ],
                [
                    'attribute' => 'start_date',
                    'label' => 'Inicio',
                    'value' => function($model) {
                        return $model->start_date ? date('d/m/Y', strtotime($model->start_date)) : 'N/A';
                    },
                    'contentOptions' => ['style' => 'font-size: 0.8rem;'],
                    'filterInputOptions' => [
                        'class' => 'form-control form-control-sm',
                        'type' => 'date',
                    ],
                ],
                [
                    'attribute' => 'end_date',
                    'label' => 'Fin',
                    'value' => function($model) {
                        return $model->end_date ? date('d/m/Y', strtotime($model->end_date)) : 'N/A';
                    },
                    'contentOptions' => ['style' => 'font-size: 0.8rem;'],
                    'filterInputOptions' => [
                        'class' => 'form-control form-control-sm',
                        'type' => 'date',
                    ],
                ],
                [
                    'attribute' => 'id_status',
                    'label' => 'Estado',
                    'value' => function($model) {
                        return $model->getStatusBadge();
                    },
                    'format' => 'raw',
                    'filter' => MarketingSearch::getStatusOptions(),
                    'filterInputOptions' => [
                        'class' => 'form-select form-select-sm',
                        'prompt' => 'Todos',
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'visibleButtons' => [
                        'update' => $isAdmin,
                        'delete' => $isAdmin,
                    ],
                    'buttons' => [
                        'view' => function($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-primary marketing-btn-action',
                                'title' => 'Ver',
                            ]);
                        },
                        'update' => function($url, $model) {
                            return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-warning marketing-btn-action',
                                'title' => 'Editar',
                            ]);
                        },
                        'delete' => function($url, $model) {
                            return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-danger marketing-btn-action',
                                'title' => 'Eliminar',
                                'data' => [
                                    'confirm' => '¿Estás seguro de eliminar este elemento?',
                                    'method' => 'post',
                                ],
                            ]);
                        },
                    ],
                ],
            ],
            'pager' => [
                'class' => 'yii\bootstrap5\LinkPager',
                'options' => ['class' => 'pagination justify-content-center'],
            ],
            'summary' => '<div class="text-muted summary-text">Mostrando {begin}-{end} de {totalCount} elementos</div>',
        ]); ?>
    </div>
</div>