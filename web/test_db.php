<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';
new yii\web\Application($config);

use app\models\Lead;

echo "<h2>Prueba de conexión y guardado</h2>";

// Probar conexión
try {
    Yii::$app->db->open();
    echo "✅ Conexión a base de datos exitosa<br>";
} catch (\Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    exit;
}

// Probar guardado directo
$lead = new Lead();
$lead->name = 'Prueba';
$lead->lastname = 'Test';
$lead->phone = 1234567890;
$lead->comments = 'Cita de prueba';
$lead->status = 'pendiente';
$lead->created_at = date('Y-m-d');
$lead->id_user = 1;

if ($lead->save()) {
    echo "✅ Guardado exitoso. ID: " . $lead->id_lead . "<br>";
} else {
    echo "❌ Error al guardar:<br>";
    foreach ($lead->getErrors() as $field => $errors) {
        echo "- $field: " . implode(', ', $errors) . "<br>";
    }
}
?>