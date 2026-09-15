<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Detalle de Cotización #' . $model->id_quote;
$this->params['breadcrumbs'][] = ['label' => 'Cotizaciones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="quote-view">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice"></i> 
                        Detalle de Cotización #<?= $model->id_quote ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Lead:</strong> 
                                <?= Html::a(
                                    $model->lead->name . ' ' . $model->lead->lastname,
                                    ['lead/view', 'id' => $model->id_lead],
                                    ['target' => '_blank']
                                ) ?>
                            </p>
                            <p><strong>Teléfono:</strong> <?= $model->lead->phone ?></p>
                            <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($model->date_quote)) ?></p>
                            <p><strong>Hora:</strong> <?= date('H:i:s', strtotime($model->hour_quote)) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong> 
                                <?php
                                // 🔥 OBTENER NOMBRE DEL ESTADO DESDE id_status
                                $statusName = $model->getStatusName();
                                $badgeClass = $model->getStatusBadgeClass();
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= $statusName ?>
                                </span>
                            </p>
                            <p><strong>Monto Total:</strong> <h4><?= $model->getFormattedTotal() ?></h4></p>
                            <p><strong>Pago Inicial:</strong> <?= $model->down_payment ? '$' . number_format($model->down_payment, 0, ',', '.') : 'Sin pago inicial' ?></p>
                            <p><strong>Pago Pendiente:</strong> <strong class="text-danger"><?= $model->getFormattedPending() ?></strong></p>
                        </div>
                    </div>
                    
                    <?php if ($model->comments): ?>
                        <div class="alert alert-secondary mt-3">
                            <strong><i class="fas fa-comment"></i> Observaciones:</strong>
                            <?= Html::encode($model->comments) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- ACCIONES RÁPIDAS                             -->
        <!-- ============================================ -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> 
                        Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Cambiar Estado (usando id_status) -->
                    <?php $form = ActiveForm::begin(['action' => ['quote/update-status'], 'method' => 'post']); ?>
                        <?= Html::hiddenInput('id_quote', $model->id_quote) ?>
                        <div class="form-group">
                            <label>Cambiar Estado</label>
                            <?= Html::dropDownList('status', $model->getStatusName(), $statusOptions, [
                                'class' => 'form-control',
                                'id' => 'status-select'
                            ]) ?>
                        </div>
                        <?= Html::submitButton('<i class="fas fa-save"></i> Actualizar Estado', [
                            'class' => 'btn btn-warning w-100 mt-2'
                        ]) ?>
                    <?php ActiveForm::end(); ?>

                    <hr>

                    <div class="d-grid gap-2">
                        <?php if ($isAdmin || ($isAgent && $model->lead->id_user == Yii::$app->user->id)): ?>
                            <?= Html::a('<i class="fas fa-edit"></i> Editar Cotización', 
                                ['update', 'id' => $model->id_quote], 
                                ['class' => 'btn btn-primary']
                            ) ?>
                        <?php endif; ?>
                        
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al listado', 
                            ['index'], 
                            ['class' => 'btn btn-secondary']
                        ) ?>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> 
                        Resumen
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Total Cotización:</strong> <?= $model->getFormattedTotal() ?></p>
                    <p><strong>Pagado:</strong> <?= $model->down_payment ? '$' . number_format($model->down_payment, 0, ',', '.') : '$0' ?></p>
                    <p><strong>Pendiente:</strong> <?= $model->getFormattedPending() ?></p>
                    <hr>
                    <p><strong>Lead:</strong> <?= Html::encode($model->lead->name . ' ' . $model->lead->lastname) ?></p>
                    <p><strong>Estado Lead:</strong> 
                        <?php
                        $leadStatusName = $model->lead->getStatusName();
                        $leadBadgeClass = $model->lead->getStatusBadgeClass();
                        ?>
                        <span class="badge bg-<?= $leadBadgeClass ?>">
                            <?= $leadStatusName ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>