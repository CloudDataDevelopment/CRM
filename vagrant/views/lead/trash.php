<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Papelera de Leads';
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="lead-trash">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-trash text-danger"></i> <?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver a Leads', ['index'], ['class' => 'btn btn-default']) ?>
            <?php if (!empty($leads)): ?>
                <?= Html::a('<i class="fas fa-trash-alt"></i> Vaciar Papelera', ['empty-trash'], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => '¿Estás seguro de vaciar la papelera? Esta acción es irreversible.',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Fecha Eliminación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leads)): ?>
                            <?php foreach ($leads as $index => $lead): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= Html::encode($lead->name . ' ' . $lead->lastname) ?></strong></td>
                                    <td><?= $lead->phone ?></td>
                                    <td>
                                        <span class="badge bg-dark">Eliminado</span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($lead->created_at)) ?></td>
                                    <td>
                                        <?= Html::a('<i class="fas fa-undo"></i>', ['restore', 'id' => $lead->id_lead], [
                                            'class' => 'btn btn-success btn-sm',
                                            'title' => 'Restaurar',
                                            'data' => [
                                                'confirm' => '¿Restaurar este lead?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-trash-alt"></i>', ['hard-delete', 'id' => $lead->id_lead], [
                                            'class' => 'btn btn-danger btn-sm',
                                            'title' => 'Eliminar permanentemente',
                                            'data' => [
                                                'confirm' => '¿Eliminar permanentemente este lead? Esta acción no se puede deshacer.',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    La papelera está vacía
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>