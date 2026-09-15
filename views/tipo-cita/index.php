<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Tipos de Cita';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tipo-cita-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::a('Nuevo Tipo de Cita', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $tipos]),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'status_sales',
                'label' => 'Tipo de Cita',
            ],
            'comments',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('Editar', ['update', 'id' => $model->id_sales_tracking], [
                            'class' => 'btn btn-warning btn-sm',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('Eliminar', ['delete', 'id' => $model->id_sales_tracking], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => '¿Eliminar este tipo de cita?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>