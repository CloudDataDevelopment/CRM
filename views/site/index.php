<?php

use yii\helpers\Html;

$this->title = 'Dashboard';
?>

<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Bienvenido al CRM</h1>
        <p class="lead">Sistema de gestión de clientes y citas</p>
    </div>

    <div class="body-content">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>📊 Resumen</h3>
                    </div>
                    <div class="card-body">
                        <p>Próximamente podrás ver estadísticas y resúmenes del sistema.</p>
                        <div class="alert alert-info">
                            <strong>🚧 En construcción</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3>📅 Citas</h3>
                    </div>
                    <div class="card-body">
                        <p>Gestiona tus citas desde el menú lateral.</p>
                        <?= Html::a('Ir a Citas', ['/cita/index'], ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3>🏢 Empresas</h3>
                    </div>
                    <div class="card-body">
                        <p>Administra las empresas del sistema.</p>
                        <?= Html::a('Ir a Empresas', ['/empresa/index'], ['class' => 'btn btn-info']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>