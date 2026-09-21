<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Task extends ActiveRecord
{
    public static function tableName()
    {
        return 'Task';
    }

    public function rules()
    {
        return [
            [['comments', 'id_user', 'date_s', 'date_time'], 'required'],
            [['id_status', 'id_user'], 'integer'],
            [['comments'], 'string', 'max' => 255],
            [['date_s', 'date_time'], 'safe'],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['id_user' => 'id_user']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_task'   => 'ID Actividad',
            'comments'  => 'Descripción',
            'id_status' => 'Estado',
            'date_s'    => 'Fecha',
            'date_time' => 'Fecha y Hora',
            'id_user'   => 'Usuario Asignado',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            // 🔥 AUTO-ASIGNAR USUARIO AL CREAR (siempre)
            if (empty($this->id_user) || $this->id_user == 0) {
                $this->id_user = Yii::$app->user->id ?: 1;
            }

            // Fecha automática si no viene
            if (empty($this->date_s)) {
                $this->date_s = date('Y-m-d');
            }
            if (empty($this->date_time)) {
                $this->date_time = date('Y-m-d H:i:s');
            }

            // Estado por defecto si no viene
            if (empty($this->id_status) || $this->id_status == 0) {
                $statusPorDefecto = Status::find()->where(['status' => 'Por hacer'])->one();
                if ($statusPorDefecto) {
                    $this->id_status = $statusPorDefecto->id_status;
                }
            }
        }

        return true;
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

    // ============================================
    // MÉTODOS DE ESTADO
    // ============================================

    public function getStatusName()
    {
        try {
            if ($this->status) {
                return $this->status->status;
            }
            return 'Sin Estado';
        } catch (\Exception $e) {
            return 'Sin Estado';
        }
    }

    public function getStatusBadgeClass()
    {
        try {
            if (!$this->status) {
                return 'secondary';
            }

            $lowerStatus = strtolower(trim($this->status->status));

            $badges = [
                'por hacer'   => 'warning',
                'pendiente'   => 'warning',
                'en progreso' => 'info',
                'en_progreso' => 'info',
                'en revisión' => 'primary',
                'en revision' => 'primary',
                'programado'  => 'info',
                'completado'  => 'success',
                'completada'  => 'success',
                'cancelado'   => 'danger',
            ];

            return $badges[$lowerStatus] ?? 'secondary';
        } catch (\Exception $e) {
            return 'secondary';
        }
    }

    public static function getStatusOptions()
    {
        try {
            $statusNames = ['Por hacer', 'En progreso', 'En revisión', 'Programado', 'Completado', 'Cancelado'];

            return Status::find()
                ->where(['IN', 'status', $statusNames])
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getFormattedDate()
    {
        if ($this->date_s) {
            return date('d/m/Y', strtotime($this->date_s));
        }
        return 'Sin fecha';
    }

    public function getFormattedDateTime()
    {
        if ($this->date_time) {
            return date('d/m/Y H:i', strtotime($this->date_time));
        }
        return 'Sin fecha';
    }

    public function getTrackingDate()
    {
        if (!empty($this->date_s)) {
            return date('d/m/Y', strtotime($this->date_s));
        }
        if (!empty($this->date_time)) {
            return date('d/m/Y H:i', strtotime($this->date_time));
        }
        return 'Sin fecha';
    }

    public function getUserName()
    {
        if ($this->user) {
            return trim($this->user->name . ' ' . $this->user->lastname1);
        }
        return 'Sin asignar';
    }
}