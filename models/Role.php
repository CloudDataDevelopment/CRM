<?php

namespace app\models;

use yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    public static function tableName()
    {
        return 'Role';
    }

    public function rules()
    {
        return [
            [['role_type'], 'string', 'max' => 50],
        ];
    }
}