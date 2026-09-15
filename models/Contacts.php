<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para la tabla Contacts
 * 
 * @property int $id_contact
 * @property int $id_company
 * @property string $name
 * @property string $last_name
 * @property int $phone
 * @property string $email
 * @property int $id_status
 * @property int $id_type_contact
 *
 * @property Company $company
 * @property Status $status
 * @property TypeContact $typeContact
 */
class Contacts extends ActiveRecord
{
    public static function tableName()
    {
        return 'Contacts';
    }

    public function rules()
    {
        return [
            [['name', 'last_name'], 'required'],
            [['id_company', 'id_status', 'id_type_contact'], 'integer'],
            [['name', 'last_name'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 25],
            [['phone'], 'integer'],
            [['email'], 'email'],
            [['email'], 'unique', 'message' => 'Este correo electrónico ya está registrado.'],
            [['id_company'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['id_company' => 'id_company']],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],
            [['id_type_contact'], 'exist', 'skipOnError' => true, 'targetClass' => TypeContact::class, 'targetAttribute' => ['id_type_contact' => 'id_type_contact']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_contact' => 'ID Contacto',
            'id_company' => 'Empresa',
            'name' => 'Nombre',
            'last_name' => 'Apellido',
            'phone' => 'Teléfono',
            'email' => 'Correo Electrónico',
            'id_status' => 'Estado',
            'id_type_contact' => 'Tipo de Contacto',
            'fullName' => 'Nombre Completo',
        ];
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id_company' => 'id_company']);
    }

    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    public function getTypeContact()
    {
        return $this->hasOne(TypeContact::class, ['id_type_contact' => 'id_type_contact']);
    }

    // ============================================
    // MÉTODOS DE ACCESO
    // ============================================
    
    public function getFullName()
    {
        return trim($this->name . ' ' . $this->last_name);
    }

    public function getCompanyName()
    {
        return $this->company ? $this->company->name : 'Sin empresa';
    }

    public function getStatusName()
    {
        return $this->status ? $this->status->status : 'Sin estado';
    }

    public function getStatusBadgeClass()
    {
        if (!$this->status) {
            return 'secondary';
        }
        
        $badges = [
            'Activo' => 'success',
            'Inactivo' => 'danger',
            'Pendiente' => 'warning',
            'Suspendido' => 'secondary',
        ];
        
        $statusName = trim($this->status->status);
        return $badges[$statusName] ?? 'secondary';
    }

    /**
     * 🔥 Icono según el estado (FontAwesome)
     */
    public function getStatusIcon()
    {
        if (!$this->status) {
            return 'fa-circle';
        }

        $icons = [
            'Activo' => 'fa-check-circle',
            'Inactivo' => 'fa-times-circle',
            'Pendiente' => 'fa-clock',
            'Suspendido' => 'fa-ban',
        ];

        $statusName = trim($this->status->status);
        return $icons[$statusName] ?? 'fa-circle';
    }

    public function getTypeContactName()
    {
        return $this->typeContact ? $this->typeContact->type_contact : 'Sin tipo';
    }

    public function getFormattedPhone()
    {
        $phone = (string)$this->phone;
        if (strlen($phone) === 10) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        }
        return $phone;
    }

    // ============================================
    // MÉTODOS ESTÁTICOS
    // ============================================
    
    public static function getContactsForCompany($companyId)
    {
        return static::find()
            ->where(['id_company' => $companyId])
            ->orderBy(['name' => SORT_ASC, 'last_name' => SORT_ASC])
            ->all();
    }

    public static function getActiveContacts($companyId = null)
    {
        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if (!$statusActivo) {
            return [];
        }
        
        $query = static::find()
            ->where(['id_status' => $statusActivo->id_status])
            ->orderBy(['name' => SORT_ASC, 'last_name' => SORT_ASC]);
        
        if ($companyId) {
            $query->andWhere(['id_company' => $companyId]);
        }
        
        return $query->all();
    }
}