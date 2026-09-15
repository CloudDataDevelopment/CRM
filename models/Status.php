<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "status".
 *
 * @property int $id_status
 * @property string $status
 */
class Status extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_status' => 'ID Estado',
            'status' => 'Estado',
        ];
    }

    /**
     * Get list of statuses for dropdown
     */
    public static function getList()
    {
        return self::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }
}