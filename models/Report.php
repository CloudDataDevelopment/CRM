<?php

namespace app\models;

use Yii;

/**
 * Modelo para la tabla "Report".
 *
 * @property int $id_report
 * @property string|null $report_name
 * @property string|null $report_type
 * @property string|null $date_report
 * @property int|null $id_user
 * @property int|null $id_company
 * @property int|null $id_status
 *
 * @property Status $status
 * @property User $user
 * @property Company $company
 */
class Report extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Reports';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['report_name', 'id_status'], 'required'],
            [['date_report'], 'safe'],
            [['id_user', 'id_company', 'id_status'], 'integer'],
            [['report_name', 'report_type'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_report' => 'ID',
            'report_name' => 'Nombre del Reporte',
            'report_type' => 'Tipo de Reporte',
            'date_report' => 'Fecha',
            'id_user' => 'Usuario',
            'id_company' => 'Empresa',
            'id_status' => 'Estado',
        ];
    }

    // ============================================
    // RELACIONES
    // ============================================
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id_user' => 'id_user']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id_company' => 'id_company']);
    }

    // ============================================
    // HELPERS DE ESTADO
    // ============================================
    public function getStatusName()
    {
        return $this->status ? $this->status->status : 'Sin estado';
    }

    public function getStatusBadgeClass()
    {
        $badges = [
            'activo' => 'success',
            'completado' => 'info',
            'pendiente' => 'warning',
            'cancelado' => 'danger',
        ];
        $name = strtolower(trim($this->getStatusName()));
        return $badges[$name] ?? 'secondary';
    }
}