<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use app\models\Company;
use app\models\Authentication;
use app\models\Status;

class MarketingSearch extends Model
{
    public $search;
    public $status;
    public $company_id;
    public $date_from;
    public $date_to;
    public $type;

    public function rules()
    {
        return [
            [['search', 'status', 'company_id', 'date_from', 'date_to', 'type'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'search' => 'Buscar',
            'status' => 'Estado',
            'company_id' => 'Empresa',
            'date_from' => 'Fecha Desde',
            'date_to' => 'Fecha Hasta',
            'type' => 'Tipo',
        ];
    }

    /**
     * Buscar campañas
     */
    public function searchCampaigns($params = [])
    {
        $this->load($params, '');

        $query = Campaign::find()
            ->with('company')
            ->with('status')
            ->orderBy(['start_date' => SORT_DESC]);

        $user = Yii::$app->user->identity;
        if ($user && !$user->isSuperAdmin()) {
            $query->andWhere(['id_company' => $user->id_company]);
        }

        if (!empty($this->search)) {
            $query->andWhere(['or',
                ['like', 'campaign_name', $this->search],
                ['like', 'comments', $this->search],
            ]);
        }

        if (!empty($this->status)) {
            $statusModel = Status::find()->where(['status' => $this->status])->one();
            if ($statusModel) {
                $query->andWhere(['id_status' => $statusModel->id_status]);
            }
        }

        if (!empty($this->company_id) && $user && $user->isSuperAdmin()) {
            $query->andWhere(['id_company' => $this->company_id]);
        }

        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'start_date', $this->date_from]);
        }

        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'end_date', $this->date_to]);
        }

        $campaigns = $query->all();

        // Convertir a modelos Marketing
        $marketingModels = [];
        foreach ($campaigns as $campaign) {
            $marketing = new Marketing();
            $marketing->loadFromCampaign($campaign);
            $marketingModels[] = $marketing;
        }

        return new ArrayDataProvider([
            'allModels' => $marketingModels,
            'pagination' => [
                'pageSize' => 15,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                    'start_date',
                    'end_date',
                ],
            ],
        ]);
    }

    /**
     * Buscar promociones
     */
    public function searchPromotions($params = [])
    {
        $this->load($params, '');

        $query = Promotion::find()
            ->with('company')
            ->with('status')
            ->orderBy(['start_date' => SORT_DESC]);

        $user = Yii::$app->user->identity;
        if ($user && !$user->isSuperAdmin()) {
            $query->andWhere(['id_company' => $user->id_company]);
        }

        if (!empty($this->search)) {
            $query->andWhere(['or',
                ['like', 'promotion_name', $this->search],
                ['like', 'comments', $this->search],
            ]);
        }

        if (!empty($this->status)) {
            $statusModel = Status::find()->where(['status' => $this->status])->one();
            if ($statusModel) {
                $query->andWhere(['id_status' => $statusModel->id_status]);
            }
        }

        if (!empty($this->company_id) && $user && $user->isSuperAdmin()) {
            $query->andWhere(['id_company' => $this->company_id]);
        }

        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'start_date', $this->date_from]);
        }

        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'end_date', $this->date_to]);
        }

        $promotions = $query->all();

        // Convertir a modelos Marketing
        $marketingModels = [];
        foreach ($promotions as $promotion) {
            $marketing = new Marketing();
            $marketing->loadFromPromotion($promotion);
            $marketingModels[] = $marketing;
        }

        return new ArrayDataProvider([
            'allModels' => $marketingModels,
            'pagination' => [
                'pageSize' => 15,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                    'start_date',
                    'end_date',
                ],
            ],
        ]);
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

            $auth = Authentication::find()
                ->where(['id_user' => $user->id])
                ->one();

            if ($auth && $auth->id_role == 1) {
                return Company::find()
                    ->select(['name', 'id_company'])
                    ->indexBy('id_company')
                    ->column();
            }

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
     * Obtener opciones de estado para filtro
     */
    public static function getStatusOptions()
    {
        try {
            return Status::find()
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();
        } catch (\Exception $e) {
            return [];
        }
    }
}