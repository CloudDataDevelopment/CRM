<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla Types_contacts
 * 
 * @property int $id_type_contact
 * @property string $type_contact
 */
class TypeContact extends ActiveRecord
{
    public static function tableName()
    {
        // 🔥 La tabla se llama "Types_contacts" (con c minúscula)
        return 'Types_contacts';
    }

    public function rules()
    {
        return [
            [['type_contact'], 'required'],
            [['type_contact'], 'string', 'max' => 50],
            [['type_contact'], 'unique', 'message' => 'Este tipo de contacto ya existe.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_type_contact' => 'ID Tipo',
            'type_contact' => 'Tipo de Contacto',
        ];
    }

    public static function getDropdownList()
    {
        return self::find()
            ->select(['type_contact', 'id_type_contact'])
            ->indexBy('id_type_contact')
            ->column();
    }

    /**
     * Obtener tipos de contacto activos (con contactos asociados)
     */
    public static function getActiveTypes()
    {
        $subQuery = Contacts::find()
            ->select('id_type_contact')
            ->distinct()
            ->where(['not', ['id_type_contact' => null]]);

        return self::find()
            ->where(['in', 'id_type_contact', $subQuery])
            ->orderBy(['type_contact' => SORT_ASC])
            ->all();
    }
}