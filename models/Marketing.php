<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Campaign;
use app\models\Promotion;
use app\models\Company;
use app\models\Status;
use app\models\Authentication;

class Marketing extends Model
{
    const TYPE_CAMPAIGN = 'campaign';
    const TYPE_PROMOTION = 'promotion';

    public $type;
    public $id;
    public $name;
    public $start_date;
    public $end_date;
    public $id_company;
    public $id_status;
    public $comments;
    public $created_by;
    public $created_at;
    public $updated_at;

    private $_campaign;
    private $_promotion;

    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['id_company', 'id_status', 'created_by'], 'integer'],
            [['type'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 100],
            [['comments'], 'string', 'max' => 255],
            [['type'], 'in', 'range' => [self::TYPE_CAMPAIGN, self::TYPE_PROMOTION]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Tipo',
            'name' => 'Nombre',
            'start_date' => 'Fecha de Inicio',
            'end_date' => 'Fecha de Fin',
            'id_company' => 'Empresa',
            'id_status' => 'Estado',
            'comments' => 'Comentarios',
            'created_by' => 'Creado por',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * Guardar según el tipo
     */
    public function save()
    {
        if ($this->validate()) {
            if ($this->type === self::TYPE_CAMPAIGN) {
                return $this->saveCampaign();
            } elseif ($this->type === self::TYPE_PROMOTION) {
                return $this->savePromotion();
            }
        }
        return false;
    }

    /**
     * Guardar como Campaña (tabla Campaign)
     */
    private function saveCampaign()
    {
        try {
            if ($this->id && $this->id > 0) {
                $campaign = Campaign::findOne($this->id);
                if (!$campaign) {
                    $this->addError('id', 'Campaña no encontrada.');
                    return false;
                }
            } else {
                $campaign = new Campaign();
            }

            $campaign->campaign_name = $this->name;
            $campaign->start_date = $this->start_date;
            $campaign->end_date = $this->end_date;
            $campaign->id_company = $this->id_company;
            $campaign->id_status = $this->id_status;
            $campaign->comments = $this->comments;

            if ($campaign->save()) {
                $this->id = $campaign->id_campaign;
                $this->_campaign = $campaign;
                return true;
            }

            if ($campaign->hasErrors()) {
                foreach ($campaign->getErrors() as $attribute => $errors) {
                    $this->addError($attribute, implode(', ', $errors));
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->addError('type', 'Error al guardar campaña: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar como Promoción (tabla Promotions)
     */
    private function savePromotion()
    {
        try {
            if ($this->id && $this->id > 0) {
                $promotion = Promotion::findOne($this->id);
                if (!$promotion) {
                    $this->addError('id', 'Promoción no encontrada.');
                    return false;
                }
            } else {
                $promotion = new Promotion();
            }

            $promotion->promotion_name = $this->name;
            $promotion->start_date = $this->start_date;
            $promotion->end_date = $this->end_date;
            $promotion->id_company = $this->id_company;
            $promotion->id_status = $this->id_status;
            $promotion->comments = $this->comments;

            if ($promotion->save()) {
                $this->id = $promotion->id_promotion;
                $this->_promotion = $promotion;
                return true;
            }

            if ($promotion->hasErrors()) {
                foreach ($promotion->getErrors() as $attribute => $errors) {
                    $this->addError($attribute, implode(', ', $errors));
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->addError('type', 'Error al guardar promoción: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar según el tipo
     */
    public function delete()
    {
        if ($this->type === self::TYPE_CAMPAIGN) {
            $campaign = Campaign::findOne($this->id);
            if ($campaign) {
                return $campaign->delete();
            }
        } elseif ($this->type === self::TYPE_PROMOTION) {
            $promotion = Promotion::findOne($this->id);
            if ($promotion) {
                return $promotion->delete();
            }
        }
        return false;
    }

    /**
     * Buscar un elemento por ID y tipo
     */
    public static function findOne($condition)
    {
        if (is_array($condition)) {
            $id = $condition['id'] ?? null;
            $type = $condition['type'] ?? null;
        } else {
            $id = $condition;
            $type = null;
        }

        $model = new self();

        // Buscar en Campaign
        if ($type === null || $type === self::TYPE_CAMPAIGN) {
            $campaign = Campaign::findOne($id);
            if ($campaign) {
                $model->loadFromCampaign($campaign);
                return $model;
            }
        }

        // Buscar en Promotions
        if ($type === null || $type === self::TYPE_PROMOTION) {
            $promotion = Promotion::findOne($id);
            if ($promotion) {
                $model->loadFromPromotion($promotion);
                return $model;
            }
        }

        return null;
    }

    /**
     * Cargar datos desde Campaign (tabla Campaign)
     */
    public function loadFromCampaign($campaign)
    {
        $this->type = self::TYPE_CAMPAIGN;
        $this->id = (int)$campaign->id_campaign;
        $this->name = $campaign->campaign_name;
        $this->start_date = $campaign->start_date;
        $this->end_date = $campaign->end_date;
        $this->id_company = $campaign->id_company;
        $this->id_status = $campaign->id_status;
        $this->comments = $campaign->comments;
        $this->created_at = $campaign->created_at ?? date('Y-m-d H:i:s');
        $this->updated_at = $campaign->updated_at ?? date('Y-m-d H:i:s');
        $this->_campaign = $campaign;
        return $this;
    }

    /**
     * Cargar datos desde Promotion (tabla Promotions)
     */
    public function loadFromPromotion($promotion)
    {
        $this->type = self::TYPE_PROMOTION;
        $this->id = (int)$promotion->id_promotion;
        $this->name = $promotion->promotion_name;
        $this->start_date = $promotion->start_date;
        $this->end_date = $promotion->end_date;
        $this->id_company = $promotion->id_company;
        $this->id_status = $promotion->id_status;
        $this->comments = $promotion->comments;
        $this->created_at = $promotion->created_at ?? date('Y-m-d H:i:s');
        $this->updated_at = $promotion->updated_at ?? date('Y-m-d H:i:s');
        $this->_promotion = $promotion;
        return $this;
    }

    /**
     * Obtener la empresa relacionada
     */
    public function getCompany()
    {
        if ($this->type === self::TYPE_CAMPAIGN && $this->_campaign) {
            return $this->_campaign->company;
        } elseif ($this->type === self::TYPE_PROMOTION && $this->_promotion) {
            return $this->_promotion->company;
        }
        
        // Si no tenemos el objeto cargado, intentar buscarlo
        if ($this->type === self::TYPE_CAMPAIGN && $this->id) {
            $campaign = Campaign::findOne($this->id);
            if ($campaign) {
                return $campaign->company;
            }
        } elseif ($this->type === self::TYPE_PROMOTION && $this->id) {
            $promotion = Promotion::findOne($this->id);
            if ($promotion) {
                return $promotion->company;
            }
        }
        return null;
    }

    /**
     * Obtener el estado relacionado
     */
    public function getStatus()
    {
        try {
            if ($this->type === self::TYPE_CAMPAIGN && $this->_campaign) {
                return $this->_campaign->status;
            } elseif ($this->type === self::TYPE_PROMOTION && $this->_promotion) {
                return $this->_promotion->status;
            }
            
            // Si no tenemos el objeto cargado, intentar buscarlo
            if ($this->type === self::TYPE_CAMPAIGN && $this->id) {
                $campaign = Campaign::findOne($this->id);
                if ($campaign) {
                    return $campaign->status;
                }
            } elseif ($this->type === self::TYPE_PROMOTION && $this->id) {
                $promotion = Promotion::findOne($this->id);
                if ($promotion) {
                    return $promotion->status;
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * Obtener el badge del estado
     */
    public function getStatusBadge()
    {
        try {
            $status = $this->getStatus();
            if (!$status) {
                return '<span class="badge bg-secondary">Sin Estado</span>';
            }
            
            $badges = [
                'Activo' => 'success',
                'Inactivo' => 'danger',
                'Pendiente' => 'warning',
                'Finalizado' => 'info',
                'Cancelado' => 'secondary',
                'Completado' => 'info',
            ];
            
            $class = $badges[$status->status] ?? 'secondary';
            return '<span class="badge bg-' . $class . '">' . $status->status . '</span>';
        } catch (\Exception $e) {
            return '<span class="badge bg-secondary">Sin Estado</span>';
        }
    }

    /**
     * Obtener el nombre del estado
     */
    public function getStatusName()
    {
        $status = $this->getStatus();
        return $status ? $status->status : 'Sin Estado';
    }

    /**
     * Verificar si está activo
     */
    public function isActive()
    {
        $status = $this->getStatus();
        if (!$status) {
            return false;
        }
        return $status->status === 'Activo';
    }

    /**
     * Obtener label del tipo
     */
    public function getTypeLabel()
    {
        $labels = [
            self::TYPE_CAMPAIGN => 'Campaña',
            self::TYPE_PROMOTION => 'Promoción',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Obtener icono del tipo
     */
    public function getTypeIcon()
    {
        $icons = [
            self::TYPE_CAMPAIGN => 'fa-bullhorn',
            self::TYPE_PROMOTION => 'fa-gift',
        ];
        return $icons[$this->type] ?? 'fa-tag';
    }

    /**
     * Obtener lista de tipos para dropdown
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_CAMPAIGN => 'Campaña',
            self::TYPE_PROMOTION => 'Promoción',
        ];
    }

    /**
     * Obtener lista de estados para dropdown
     */
    public static function getStatusList()
    {
        try {
            if (class_exists('app\models\Status')) {
                $statuses = Status::find()
                    ->select(['status', 'id_status'])
                    ->indexBy('id_status')
                    ->column();
                if (!empty($statuses)) {
                    return $statuses;
                }
            }
        } catch (\Exception $e) {
            // Si hay error, usar lista por defecto
        }
        
        return [
            1 => 'Activo',
            2 => 'Inactivo',
            3 => 'Pendiente',
            4 => 'Finalizado',
            5 => 'Cancelado',
        ];
    }

    /**
     * Obtener lista de empresas para el filtro
     */
    public static function getCompanyList()
    {
        try {
            $user = Yii::$app->user->identity;
            if (!$user) {
                return [];
            }

            // Verificar si es SuperAdmin (id_role = 1)
            $auth = Authentication::find()
                ->where(['id_user' => $user->id])
                ->one();

            // Si es SuperAdmin, ver todas las empresas
            if ($auth && $auth->id_role == 1) {
                return Company::find()
                    ->select(['name', 'id_company'])
                    ->indexBy('id_company')
                    ->column();
            }

            // Si no es SuperAdmin, solo su empresa
            return Company::find()
                ->select(['name', 'id_company'])
                ->where(['id_company' => $user->id_company])
                ->indexBy('id_company')
                ->column();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener estadísticas por tipo
     */
    public static function getStats($type = null)
    {
        $total = 0;
        $active = 0;
        $user = Yii::$app->user->identity;
        $companyFilter = ($user && !$user->isSuperAdmin()) ? $user->id_company : null;

        // Campañas (tabla Campaign)
        if ($type === null || $type === self::TYPE_CAMPAIGN) {
            $query = Campaign::find();
            if ($companyFilter) {
                $query->andWhere(['id_company' => $companyFilter]);
            }
            $total += $query->count();
            
            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
            if ($statusActivo) {
                $activeQuery = Campaign::find()->where(['id_status' => $statusActivo->id_status]);
                if ($companyFilter) {
                    $activeQuery->andWhere(['id_company' => $companyFilter]);
                }
                $active += $activeQuery->count();
            }
        }

        // Promociones (tabla Promotions)
        if ($type === null || $type === self::TYPE_PROMOTION) {
            $query = Promotion::find();
            if ($companyFilter) {
                $query->andWhere(['id_company' => $companyFilter]);
            }
            $total += $query->count();
            
            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
            if ($statusActivo) {
                $activeQuery = Promotion::find()->where(['id_status' => $statusActivo->id_status]);
                if ($companyFilter) {
                    $activeQuery->andWhere(['id_company' => $companyFilter]);
                }
                $active += $activeQuery->count();
            }
        }

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
        ];
    }
}