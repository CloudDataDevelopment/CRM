<?php

namespace app\models;

use Yii;
use yii\base\Model;

class FormularioCita extends Model
{
    public $name;
    public $lastname;
    public $phone;
    public $comments;
    public $fecha;
    public $hora;
    public $id_sales_tracking;
    public $id_lead; // ✅ Agregar esta propiedad

    public function rules()
    {
        return [
            [['name', 'lastname', 'phone', 'fecha', 'hora', 'id_sales_tracking'], 'required'],
            [['name', 'lastname'], 'string', 'max' => 10],
            [['phone'], 'integer'],
            [['comments'], 'string', 'max' => 50],
            [['fecha', 'hora'], 'safe'],
            [['id_sales_tracking'], 'integer'],
            [['id_lead'], 'integer'], // ✅ Agregar regla para id_lead
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Nombre',
            'lastname' => 'Apellido',
            'phone' => 'Teléfono',
            'comments' => 'Observaciones',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'id_sales_tracking' => 'Tipo de Cita',
            'id_lead' => 'Lead', // ✅ Agregar label para id_lead
        ];
    }

    public static function getHorasDisponibles()
    {
        return [
            '09:00' => '9:00 AM',
            '10:00' => '10:00 AM',
            '11:00' => '11:00 AM',
            '12:00' => '12:00 PM',
            '13:00' => '1:00 PM',
            '14:00' => '2:00 PM',
            '15:00' => '3:00 PM',
            '16:00' => '4:00 PM',
            '17:00' => '5:00 PM',
        ];
    }

    public static function getTiposCita()
    {
        $tipos = SalesTracking::find()
            ->select(['id_sales_tracking', 'status_sales'])
            ->where(['not', ['status_sales' => null]])
            ->andWhere(['<>', 'status_sales', ''])
            ->asArray()
            ->all();
        
        $resultado = [];
        foreach ($tipos as $tipo) {
            $resultado[$tipo['id_sales_tracking']] = $tipo['status_sales'];
        }
        
        return $resultado;
    }
}