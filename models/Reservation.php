<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla reservations
 * 
 * @property int $id_reservation
 * @property string $name_reservation
 * @property string $date_reservation
 * @property int $id_lead
 * @property int $id_user
 * @property int $id_company
 * @property string $hour_s
 * @property string $hour_f
 *
 * @property Lead $lead
 * @property User $user
 * @property Company $company
 */
class Reservation extends ActiveRecord
{
    public static function tableName()
    {
        return 'reservations';
    }

    public function rules()
    {
        return [
            [['name_reservation', 'date_reservation', 'id_lead', 'id_user', 'hour_s', 'hour_f'], 'required'],
            [['date_reservation'], 'safe'],
            [['id_lead', 'id_user', 'id_company'], 'integer'],
            [['hour_s', 'hour_f'], 'safe'],
            [['name_reservation'], 'string', 'max' => 50],
            [['id_lead'], 'exist', 'skipOnError' => true, 'targetClass' => Lead::class, 'targetAttribute' => ['id_lead' => 'id_lead']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['id_user' => 'id_user']],
            [['id_company'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['id_company' => 'id_company']],
            ['hour_f', 'compare', 'compareAttribute' => 'hour_s', 'operator' => '>', 'message' => 'La hora de fin debe ser mayor a la hora de inicio.'],
            ['date_reservation', 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '>=', 'message' => 'La fecha no puede ser anterior a hoy.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_reservation' => 'ID Reservación',
            'name_reservation' => 'Nombre de la Reservación',
            'date_reservation' => 'Fecha de Reservación',
            'id_lead' => 'Lead Asociado',
            'id_user' => 'Usuario Asignado',
            'id_company' => 'Empresa',
            'hour_s' => 'Hora de Inicio',
            'hour_f' => 'Hora de Fin',
            'leadName' => 'Lead',
            'userName' => 'Usuario Asignado',
        ];
    }

    // ============================================
    // 🔥 BEFORE SAVE - ASIGNAR EMPRESA AUTOMÁTICAMENTE
    // ============================================
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Si no viene id_company, asignarla desde el lead
        if (empty($this->id_company) && !empty($this->id_lead)) {
            $lead = Lead::findOne($this->id_lead);
            if ($lead) {
                $this->id_company = $lead->id_company;
            }
        }

        // Fallback: usar la empresa del usuario actual
        if (empty($this->id_company)) {
            $user = Yii::$app->user->identity;
            if ($user && !empty($user->id_company)) {
                $this->id_company = $user->id_company;
            }
        }

        return true;
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['id_lead' => 'id_lead']);
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
    // MÉTODOS DE ACCESO
    // ============================================
    
    public function getLeadName()
    {
        if ($this->lead) {
            return $this->lead->name . ' ' . $this->lead->lastname;
        }
        return 'Lead no disponible';
    }

    public function getUserName()
    {
        if ($this->user) {
            return $this->user->name . ' ' . $this->user->lastname1;
        }
        return 'Usuario no disponible';
    }

    public function getLeadPhone()
    {
        if ($this->lead) {
            return $this->lead->phone;
        }
        return 'Sin teléfono';
    }

    public function getCompanyName()
    {
        if ($this->company) {
            return $this->company->name;
        }
        return 'Sin empresa';
    }

    public function getFormattedDate()
    {
        return date('d/m/Y', strtotime($this->date_reservation));
    }

    public function getFormattedHourS()
    {
        return date('H:i', strtotime($this->hour_s));
    }

    public function getFormattedHourF()
    {
        return date('H:i', strtotime($this->hour_f));
    }

    public function getFormattedDateTime()
    {
        return $this->getFormattedDate() . ' ' . $this->getFormattedHourS() . ' - ' . $this->getFormattedHourF();
    }

    // ============================================
    // MÉTODOS ESTÁTICOS
    // ============================================
    
    public static function getReservationsForUser($userId)
    {
        return static::find()
            ->where(['id_user' => $userId])
            ->orderBy(['date_reservation' => SORT_DESC, 'hour_s' => SORT_DESC])
            ->all();
    }

    public static function getReservationsForLead($leadId)
    {
        return static::find()
            ->where(['id_lead' => $leadId])
            ->orderBy(['date_reservation' => SORT_DESC, 'hour_s' => SORT_DESC])
            ->all();
    }

    public static function getTodayReservations($userId = null)
    {
        $query = static::find()
            ->where(['date_reservation' => date('Y-m-d')])
            ->orderBy(['hour_s' => SORT_ASC]);

        if ($userId) {
            $query->andWhere(['id_user' => $userId]);
        }

        return $query->all();
    }

    public static function getUpcomingReservations($days = 7, $userId = null)
    {
        $query = static::find()
            ->where(['>=', 'date_reservation', date('Y-m-d')])
            ->andWhere(['<=', 'date_reservation', date('Y-m-d', strtotime("+$days days"))])
            ->orderBy(['date_reservation' => SORT_ASC, 'hour_s' => SORT_ASC]);

        if ($userId) {
            $query->andWhere(['id_user' => $userId]);
        }

        return $query->all();
    }

    // ============================================
    // MÉTODOS DE VALIDACIÓN
    // ============================================
    
    public function isToday()
    {
        return $this->date_reservation == date('Y-m-d');
    }

    public function isPast()
    {
        return $this->date_reservation < date('Y-m-d');
    }

    public function isFuture()
    {
        return $this->date_reservation > date('Y-m-d');
    }

    public function getStatusClass()
    {
        if ($this->isToday()) {
            return 'warning';
        } elseif ($this->isPast()) {
            return 'secondary';
        } else {
            return 'success';
        }
    }

    public function getStatusLabel()
    {
        if ($this->isToday()) {
            return 'Hoy';
        } elseif ($this->isPast()) {
            return 'Pasada';
        } else {
            return 'Próxima';
        }
    }
}