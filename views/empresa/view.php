<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="empresa-view">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-building"></i> <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id_company',
                    'name',
                    'description',
                    'domain',
                    [
                        'attribute' => 'id_status',
                        'value' => function($model) {
                            return $model->getStatusBadge();
                        },
                        'format' => 'raw',
                    ],
                    'type',
                ],
            ]); ?>
        </div>
        <div class="card-footer">
            <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id_company], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id_company], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de eliminar esta empresa?',
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</div>