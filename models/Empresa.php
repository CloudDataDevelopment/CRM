<?php

namespace app\models;

use yii\db\ActiveRecord;

class Empresa extends ActiveRecord
{
    public static function tableName()
    {
        return 'Company';  // La tabla se llama Company
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 45],
            [['domain'], 'string', 'max' => 10],
            [['status'], 'string', 'max' => 10],
            [['type'], 'string', 'max' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_company' => 'ID',
            'name' => 'Nombre',
            'description' => 'Descripción',
            'domain' => 'Dominio',
            'status' => 'Estado',
            'type' => 'Tipo',
        ];
    }
}