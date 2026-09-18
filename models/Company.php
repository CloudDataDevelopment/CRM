<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use Yii;

class Company extends ActiveRecord
{
    public $logoFile;

    public static function tableName()
    {
        return 'Company';
    }

    public static function primaryKey()
    {
        return ['id_company'];
    }

    public function rules()
    {
        return [
            [['id_company'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 45],
            [['domain'], 'string', 'max' => 20],
            [['id_status'], 'integer'],
            [['type'], 'string', 'max' => 10],
            [['name'], 'required', 'message' => 'El nombre de la empresa es obligatorio'],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['id_status' => 'id_status']],

            // 🔥 SOLO PNG
            [['logoFile'], 'file',
                'skipOnEmpty' => true,
                'extensions' => 'png',
                'checkExtensionByMimeType' => true,
                'maxSize' => 500 * 1024,        // 500 KB
                'maxFiles' => 1,
                'wrongExtension' => 'Solo se permiten archivos PNG.',
                'tooBig' => 'El archivo no debe superar los 500 KB.',
                'tooMany' => 'Solo puedes subir 1 archivo.',
            ],

            // 🔥 Validación personalizada de dimensiones
            [['logoFile'], 'validateLogoDimensions'],

            [['logo'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id_company'  => 'ID Empresa',
            'name'        => 'Nombre de Empresa',
            'logo'        => 'Logotipo',
            'logoFile'    => 'Subir Logotipo',
            'description' => 'Descripción',
            'domain'      => 'Dominio',
            'id_status'   => 'Estado',
            'type'        => 'Tipo',
        ];
    }

    // ============================================
    // RELACIONES
    // ============================================
    
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id_company' => 'id_company']);
    }

    public function getLeads()
    {
        return $this->hasMany(Lead::class, ['id_company' => 'id_company']);
    }

    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id_status' => 'id_status']);
    }

    // ============================================
    // MÉTODOS DE ESTADO
    // ============================================
    
    public function getStatusName()
    {
        if ($this->status && isset($this->status->status)) {
            return $this->status->status;
        }
        return 'Sin Estado';
    }

    public function getStatusBadge()
    {
        if (!$this->status) {
            return '<span class="badge bg-secondary">Sin Estado</span>';
        }
        
        $badges = [
            'Activo' => 'success',
            'Inactivo' => 'danger',
            'Suspendido' => 'warning',
        ];
        
        $statusName = $this->status->status ?? 'Sin Estado';
        $class = $badges[$statusName] ?? 'secondary';
        
        return '<span class="badge bg-' . $class . '">' . $statusName . '</span>';
    }

    public static function getStatusOptions()
    {
        return Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();
    }

    public function isActive()
    {
        if (!$this->status) {
            return false;
        }
        return strtolower($this->status->status) === 'activo';
    }

    public function getDisplayName()
    {
        return $this->name ?? 'Empresa #' . $this->id_company;
    }

    public static function getDropdownList()
    {
        $companies = self::find()->orderBy(['name' => SORT_ASC])->all();
        $list = [];
        foreach ($companies as $company) {
            $list[$company->id_company] = $company->getDisplayName();
        }
        return $list;
    }

    public static function getActiveCompanies()
    {
        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if (!$statusActivo) {
            return self::find()->all();
        }
        
        return self::find()
            ->where(['id_status' => $statusActivo->id_status])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    // ============================================
    // 🔥 VALIDACIÓN DE DIMENSIONES DEL LOGO
    // ============================================
    public function validateLogoDimensions($attribute, $params)
    {
        if (empty($this->logoFile)) {
            return;
        }

        // Verificar que sea una imagen válida
        $imageInfo = @getimagesize($this->logoFile->tempName);
        if ($imageInfo === false) {
            $this->addError($attribute, 'No se pudo leer la imagen. Asegúrate de que sea un PNG válido.');
            return;
        }

        list($width, $height, $type) = $imageInfo;

        // Verificar que sea PNG (IMAGETYPE_PNG = 3)
        if ($type !== IMAGETYPE_PNG) {
            $this->addError($attribute, 'El archivo debe ser una imagen PNG válida.');
            return;
        }

        // 🔥 Dimensiones mínimas
        if ($width < 200 || $height < 200) {
            $this->addError($attribute,
                "La imagen es muy pequeña ({$width}×{$height} px). " .
                "El mínimo es 200×200 px."
            );
            return;
        }

        // 🔥 Dimensiones máximas
        if ($width > 2000 || $height > 2000) {
            $this->addError($attribute,
                "La imagen es muy grande ({$width}×{$height} px). " .
                "El máximo es 2000×2000 px."
            );
            return;
        }

        // 🔥 Proporción (ratio entre 1:3 y 3:1)
        $ratio = $width / $height;
        if ($ratio < 0.33 || $ratio > 3) {
            $this->addError($attribute,
                "La proporción de la imagen ({$width}×{$height}) no es válida. " .
                "Usa una imagen cuadrada o rectangular (entre 1:3 y 3:1)."
            );
            return;
        }
    }

    // ============================================
    // 🔥 MÉTODOS DEL LOGO
    // ============================================

    public function getLogoPath()
    {
        if (empty($this->logo)) {
            return null;
        }
        return Yii::getAlias('@webroot/uploads/logos/') . $this->logo;
    }

    public function getLogoUrl()
    {
        if (empty($this->logo)) {
            return null;
        }
        return Yii::getAlias('@web/uploads/logos/') . $this->logo;
    }

    public function hasLogo()
    {
        if (empty($this->logo)) {
            return false;
        }
        return file_exists($this->getLogoPath());
    }

    /**
     * 🔥 Sube el archivo del logo al servidor
     */
    public function uploadLogo()
    {
        if (empty($this->logoFile)) {
            return true;
        }

        $uploadDir = Yii::getAlias('@webroot/uploads/logos/');

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                $this->addError('logoFile', 'No se pudo crear la carpeta de logos.');
                return false;
            }
        }

        if (!is_writable($uploadDir)) {
            $this->addError('logoFile', 'La carpeta de logos no tiene permisos de escritura.');
            return false;
        }

        // 🔥 Validar MIME real (solo PNG)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $this->logoFile->tempName);
        finfo_close($finfo);

        if ($realMime !== 'image/png') {
            $this->addError('logoFile', 'El archivo no es una imagen PNG válida.');
            return false;
        }

        $baseName = 'logo_company_' . ($this->id_company ?? 'new');
        $timestamp = date('YmdHis');
        $filename = $baseName . '_' . $timestamp . '.png';

        $fullPath = $uploadDir . $filename;

        // Eliminar logo anterior si existe
        if (!empty($this->logo) && $this->hasLogo()) {
            @unlink($this->getLogoPath());
        }

        if ($this->logoFile->saveAs($fullPath)) {
            $this->logo = $filename;
            return true;
        }

        $this->addError('logoFile', 'Error al guardar el archivo del logo.');
        return false;
    }

    /**
     * 🔥 Elimina el logo actual
     */
    public function deleteLogo()
    {
        if ($this->hasLogo()) {
            @unlink($this->getLogoPath());
        }
        $this->logo = null;
    }

    // ============================================
    // BEFORE SAVE
    // ============================================
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->logoFile instanceof UploadedFile) {
            if (!$this->uploadLogo()) {
                return false;
            }
        }

        return true;
    }

    // ============================================
    // AFTER DELETE
    // ============================================
    public function afterDelete()
    {
        parent::afterDelete();
        $this->deleteLogo();
    }

    // ============================================
    // MÉTODOS DE DEPURACIÓN
    // ============================================
    
    public function debug()
    {
        return [
            'id_company' => $this->id_company,
            'name' => $this->name,
            'logo' => $this->logo,
            'has_logo' => $this->hasLogo(),
            'id_status' => $this->id_status,
            'status_name' => $this->getStatusName(),
            'isActive' => $this->isActive(),
        ];
    }
}