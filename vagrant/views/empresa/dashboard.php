<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Dashboard - ' . $empresa->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $empresa->name;
?>

<div class="empresa-dashboard">

    <h1><?= Html::encode($empresa->name) ?></h1>
    
    <p><strong>Descripción:</strong> <?= Html::encode($empresa->description) ?></p>
    <p><strong>Dominio:</strong> <?= Html::encode($empresa->domain) ?></p>
    <p><strong>Tipo:</strong> <?= Html::encode($empresa->type) ?></p>
    <p><strong>Estado:</strong> <?= Html::encode($empresa->status) ?></p>

    <hr>

    <h3>Estadísticas</h3>
    <ul>
        <li>Total Usuarios: <?= $totalUsuarios ?></li>
        <li>Total Citas: <?= $totalCitas ?></li>
        <li>Total Leads: <?= $totalLeads ?></li>
    </ul>

    <hr>

    <h3>Módulos disponibles</h3>

    <p>
        <?= Html::a('Citas', ['/cita/index'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Leads', ['/cita/index'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Tipos de Cita', ['/tipo-cita/index'], ['class' => 'btn btn-info']) ?>
    </p>

    <p>
        <?= Html::a('Volver a Empresas', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

</div>