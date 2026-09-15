<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class DebugController extends Controller
{
    public $layout = false;
    
    // Verificar estructura de la tabla Lead
    public function actionCheckLead()
    {
        echo "<h2>🔍 Depuración de Tabla Lead</h2>";
        
        try {
            // Obtener estructura de la tabla Lead
            $columns = Yii::$app->db->createCommand("DESCRIBE Lead")->queryAll();
            
            echo "<h3>📋 Estructura de la tabla 'Lead':</h3>";
            echo "<table border='1' cellpadding='8' cellspacing='0'>";
            echo "<tr style='background:#333;color:#fff'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th></tr>";
            foreach ($columns as $col) {
                $bg = ($col['Null'] == 'NO' && $col['Default'] === null && $col['Key'] != 'PRI') ? '#ffcccc' : '#ffffff';
                echo "<tr style='background:{$bg}'>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p style='color:#d9534f;'><strong>🔴 Los campos en rojo son OBLIGATORIOS (NOT NULL) y no tienen valor por defecto</strong></p>";
            
            // Verificar último intento de inserción
            echo "<h3>📝 Último error registrado:</h3>";
            $lastError = Yii::$app->session->getFlash('db_error');
            if ($lastError) {
                echo "<pre style='background:#f0f0f0;padding:10px;border-left:3px solid #d9534f;'>{$lastError}</pre>";
            } else {
                echo "<p>No hay errores recientes</p>";
            }
            
            // Mostrar algunos registros existentes
            $leads = Yii::$app->db->createCommand("SELECT * FROM Lead LIMIT 5")->queryAll();
            echo "<h3>📊 Últimos registros en Lead:</h3>";
            if (count($leads) > 0) {
                echo "<table border='1' cellpadding='8' cellspacing='0'>";
                echo "<tr style='background:#333;color:#fff'>";
                foreach (array_keys($leads[0]) as $col) {
                    echo "<th>{$col}</th>";
                }
                echo "</tr>";
                foreach ($leads as $lead) {
                    echo "<tr>";
                    foreach ($lead as $value) {
                        echo "<td>" . ($value ?: 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No hay registros en la tabla Lead</p>";
            }
            
        } catch (\Exception $e) {
            echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Verificar todos los campos requeridos para una cita
    public function actionRequiredFields()
    {
        echo "<h2>🔍 Campos Requeridos para Agendar Cita</h2>";
        
        // Campos que debe enviar el formulario
        $formFields = [
            'name' => 'Nombre del cliente',
            'lastname' => 'Apellido del cliente', 
            'phone' => 'Teléfono',
            'tipo_cita' => 'Tipo de cita',
            'fecha' => 'Fecha de la cita',
            'hora' => 'Hora de la cita',
            'comments' => 'Observaciones'
        ];
        
        echo "<h3>📋 Campos del formulario:</h3>";
        echo "<table border='1' cellpadding='8' cellspacing='0'>";
        echo "<tr style='background:#333;color:#fff'><th>Campo</th><th>Descripción</th><th>Estado</th></tr>";
        
        foreach ($formFields as $field => $desc) {
            $value = Yii::$app->request->post($field);
            $status = $value ? '✅ Recibido' : '❓ Pendiente';
            echo "<tr><td><strong>{$field}</strong></td><td>{$desc}</td><td>{$status}</td></tr>";
        }
        echo "</table>";
        
        // Verificar campos de la tabla Lead
        echo "<h3>📋 Campos de la tabla Lead (requeridos):</h3>";
        
        $requiredColumns = [
            'name' => 'Campo obligatorio',
            'lastname' => 'Campo obligatorio',
            'phone' => 'Campo obligatorio',
            'status' => 'Se asigna automáticamente',
            'created_at' => 'Se asigna automáticamente',
            'id_user' => 'Se asigna automáticamente'
        ];
        
        echo "<table border='1' cellpadding='8' cellspacing='0'>";
        echo "<tr style='background:#333;color:#fff'><th>Campo</th><th>Acción</th><th>Valor</th></tr>";
        
        foreach ($requiredColumns as $col => $action) {
            $value = '';
            if ($col == 'status') $value = 'pendiente';
            if ($col == 'created_at') $value = date('Y-m-d');
            if ($col == 'id_user') $value = Yii::$app->user->identity->id_user ?? '1';
            
            echo "<tr>";
            echo "<td><strong>{$col}</strong></td>";
            echo "<td>{$action}</td>";
            echo "<td style='color:green'>{$value}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si la tabla Sales_tracking existe
        echo "<h3>📋 Verificación de tabla Sales_tracking:</h3>";
        try {
            $trackingCols = Yii::$app->db->createCommand("DESCRIBE Sales_tracking")->queryAll();
            echo "<table border='1' cellpadding='8' cellspacing='0'>";
            echo "<tr style='background:#333;color:#fff'><th>Campo</th><th>Tipo</th><th>Nulo</th></tr>";
            foreach ($trackingCols as $col) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td></tr>";
            }
            echo "</table>";
        } catch (\Exception $e) {
            echo "<p style='color:orange'>⚠️ Tabla Sales_tracking no existe o no está configurada</p>";
        }
    }
    
    // Probar inserción directa
    public function actionTestInsert()
    {
        echo "<h2>🔍 Prueba de Inserción Directa</h2>";
        
        $testData = [
            'name' => 'Prueba',
            'lastname' => 'Test',
            'phone' => 1234567890,
            'comments' => 'Cita de prueba - Tipo: valoracion',
            'status' => 'pendiente',
            'created_at' => date('Y-m-d'),
            'id_user' => 1
        ];
        
        echo "<h3>📝 Datos a insertar:</h3>";
        echo "<pre>";
        print_r($testData);
        echo "</pre>";
        
        try {
            $inserted = Yii::$app->db->createCommand()->insert('Lead', $testData)->execute();
            if ($inserted) {
                $id = Yii::$app->db->getLastInsertID();
                echo "<p style='color:green'>✅ Inserción exitosa! ID: {$id}</p>";
                
                // Limpiar registro de prueba
                Yii::$app->db->createCommand()->delete('Lead', ['id_lead' => $id])->execute();
                echo "<p>🗑️ Registro de prueba eliminado</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
            
            // Extraer mensaje específico de la columna
            if (strpos($e->getMessage(), "Field '") !== false) {
                preg_match("/Field '(.+?)' doesn't have a default value/", $e->getMessage(), $matches);
                if (isset($matches[1])) {
                    echo "<p style='color:#d9534f;font-size:16px'><strong>⚠️ Te falta el campo: {$matches[1]}</strong></p>";
                }
            }
        }
    }
}