<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "Promotions".
 *
 * @property int $id_promotion
 * @property string|null $promotion_name
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $id_company
 * @property int|null $id_status
 * @property string|null $comments
 */
class Promotion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Promotions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['promotion_name'], 'required'],
            [['promotion_name'], 'string', 'max' => 50],
            [['start_date', 'end_date'], 'safe'],
            [['id_company', 'id_status'], 'integer'],
            [['comments'], 'string', 'max' => 100],
            [['id_company'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['id_company' => 'id_company']],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_promotion' => 'ID Promoción',
            'promotion_name' => 'Nombre de Promoción',
            'start_date' => 'Fecha de Inicio',
            'end_date' => 'Fecha de Fin',
            'id_company' => 'Empresa',
            'id_status' => 'Estado',
            'comments' => 'Comentarios',
        ];
    }

    /**
     * Gets query for [[Company]].
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id_company' => 'id_company']);
    }

    /**
     * Gets query for [[Status]].
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    /**
     * Obtener badge del estado
     */
    public function getStatusBadge()
    {
        if (!$this->status) {
            return '<span class="badge bg-secondary">Sin Estado</span>';
        }
        
        $badges = [
            'Activo' => 'success',
            'Inactivo' => 'danger',
            'Pendiente' => 'warning',
            'Completado' => 'info',
            'Cancelado' => 'secondary',
        ];
        
        $class = $badges[$this->status->status] ?? 'secondary';
        return '<span class="badge bg-' . $class . '">' . $this->status->status . '</span>';
    }

    /**
     * Verificar si está activo
     */
    public function isActive()
    {
        if (!$this->status) {
            return false;
        }
        return $this->status->status === 'Activo';
    }

    /**
     * Obtener nombre del estado
     */
    public function getStatusName()
    {
        if ($this->status) {
            return $this->status->status;
        }
        return 'Sin Estado';
    }
}