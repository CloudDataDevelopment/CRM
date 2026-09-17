<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla "Reports".
 *
 * @property int $id_report
 * @property string|null $report_name
 * @property string|null $report_type
 * @property string|null $date_report
 * @property int|null $id_user
 * @property int|null $id_company
 * @property int|null $id_status
 * @property int|null $id_lead
 *
 * @property Status $status
 * @property User $user
 * @property Company $company
 * @property Lead $lead
 */
class Report extends ActiveRecord
{
    public static function tableName()
    {
        return 'Reports';
    }

    public function rules()
    {
        return [
            [['report_name', 'id_status'], 'required'],
            [['date_report'], 'safe'],
            [['id_user', 'id_company', 'id_status', 'id_lead'], 'integer'],
            [['report_name', 'report_type'], 'string', 'max' => 50],
            [['id_company'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['id_company' => 'id_company']],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
            [['id_lead'], 'exist', 'skipOnError' => true, 'targetClass' => Lead::class, 'targetAttribute' => ['id_lead' => 'id_lead']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_report'   => 'ID',
            'report_name' => 'Nombre de la Evaluación',
            'report_type' => 'Tipo de Evaluación',
            'date_report' => 'Fecha',
            'id_user'     => 'Usuario',
            'id_company'  => 'Empresa',
            'id_status'   => 'Estado',
            'id_lead'     => 'Lead Asociado',
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

    /**
     * 🔥 NUEVA RELACIÓN con Lead
     */
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['id_lead' => 'id_lead']);
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
            'activo'     => 'success',
            'completado' => 'info',
            'pendiente'  => 'warning',
            'cancelado'  => 'danger',
        ];
        $name = strtolower(trim($this->getStatusName()));
        return $badges[$name] ?? 'secondary';
    }

    // ============================================
    // HELPERS DE LEAD
    // ============================================
    public function getLeadName()
    {
        if ($this->lead) {
            return trim($this->lead->name . ' ' . $this->lead->lastname);
        }
        return 'Sin lead asociado';
    }

    public function getLeadPhone()
    {
        if ($this->lead) {
            return $this->lead->phone;
        }
        return 'N/A';
    }

    // ============================================
    // HELPERS DE USUARIO / EMPRESA
    // ============================================
    public function getUserName()
    {
        if ($this->user) {
            return trim($this->user->name . ' ' . $this->user->lastname1);
        }
        return 'N/A';
    }

    public function getCompanyName()
    {
        if ($this->company) {
            return $this->company->name;
        }
        return 'N/A';
    }

    // ============================================
    // HELPERS DE FECHA
    // ============================================
    public function getFormattedDate()
    {
        if ($this->date_report) {
            return date('d/m/Y', strtotime($this->date_report));
        }
        return 'Sin fecha';
    }
}