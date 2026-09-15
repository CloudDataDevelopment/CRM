<?php

namespace app\models;

use Yii;
use yii\base\Model;

class QuoteForm extends Model
{
    public $date_quote;
    public $hour_quote;
    public $id_status; // 🔥 Cambiado de status a id_status
    public $pending_payment;
    public $down_payment;
    public $comments;
    public $total_amount;

    public function rules()
    {
        return [
            [['date_quote', 'id_status', 'total_amount'], 'required'],
            [['date_quote', 'hour_quote'], 'safe'],
            [['pending_payment', 'total_amount'], 'integer'],
            [['id_status'], 'integer'],
            [['down_payment'], 'string', 'max' => 20],
            [['comments'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'date_quote' => 'Fecha',
            'hour_quote' => 'Hora',
            'id_status' => 'Estado',
            'pending_payment' => 'Pago Pendiente',
            'down_payment' => 'Pago Inicial',
            'comments' => 'Observaciones',
            'total_amount' => 'Monto Total',
        ];
    }
}