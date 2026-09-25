<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Campaign;
use app\models\Promotion;
use app\models\Status;

class MarketingSearch extends Model
{
    // 🔥 Propiedades para filtros
    public $name;
    public $start_date;
    public $end_date;
    public $id_status;
    public $id_company;
    public $type;

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['start_date', 'end_date'], 'safe'],
            [['id_status', 'id_company'], 'integer'],
            [['type'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Nombre',
            'start_date' => 'Fecha Inicio',
            'end_date' => 'Fecha Fin',
            'id_status' => 'Estado',
            'id_company' => 'Empresa',
            'type' => 'Tipo',
        ];
    }

    // ============================================
    // BUSCAR CAMPAÑAS
    // ============================================
    public function searchCampaigns($params)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        $query = Campaign::find()
            ->alias('c')
            ->with(['status', 'company']);

        // 🔥 FILTRO POR ROL Y EMPRESA
        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $query->andWhere(['c.id_company' => $empresaId]);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $query->andWhere(['c.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $query->andWhere(['c.id_company' => $user->id_company]);
        }

        // 🔥 FILTROS
        $this->load($params);

        if (!$this->validate()) {
            return new ActiveDataProvider([
                'query' => $query->where('1=0'),
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page_campaigns',
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        }

        if (!empty($this->name)) {
            $query->andWhere(['like', 'c.name', $this->name]);
        }

        if (!empty($this->start_date)) {
            $query->andWhere(['>=', 'c.start_date', $this->start_date]);
        }

        if (!empty($this->end_date)) {
            $query->andWhere(['<=', 'c.end_date', $this->end_date]);
        }

        if (!empty($this->id_status)) {
            $query->andWhere(['c.id_status' => $this->id_status]);
        }

        // 🔥 ORDEN POR DEFECTO: ÚLTIMO REGISTRADO PRIMERO
        $query->orderBy(['c.id' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
                'pageSizeParam' => 'per-page',
                'pageParam' => 'page_campaigns',
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => ['asc' => ['c.id' => SORT_ASC], 'desc' => ['c.id' => SORT_DESC]],
                    'name' => ['asc' => ['c.name' => SORT_ASC], 'desc' => ['c.name' => SORT_DESC]],
                    'start_date' => ['asc' => ['c.start_date' => SORT_ASC], 'desc' => ['c.start_date' => SORT_DESC]],
                    'end_date' => ['asc' => ['c.end_date' => SORT_ASC], 'desc' => ['c.end_date' => SORT_DESC]],
                    'id_status' => ['asc' => ['c.id_status' => SORT_ASC], 'desc' => ['c.id_status' => SORT_DESC]],
                ],
            ],
        ]);
    }

    // ============================================
    // BUSCAR PROMOCIONES
    // ============================================
    public function searchPromotions($params)
    {
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        $query = Promotion::find()
            ->alias('p')
            ->with(['status', 'company']);

        // 🔥 FILTRO POR ROL Y EMPRESA
        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $query->andWhere(['p.id_company' => $empresaId]);
            }
        } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
            $query->andWhere(['p.id_company' => $user->id_company]);
        } elseif ($user->isAgent()) {
            $query->andWhere(['p.id_company' => $user->id_company]);
        }

        // 🔥 FILTROS
        $this->load($params);

        if (!$this->validate()) {
            return new ActiveDataProvider([
                'query' => $query->where('1=0'),
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page_promotions',
                ],
                'sort' => [
                    'defaultOrder' => ['id' => SORT_DESC],
                ],
            ]);
        }

        if (!empty($this->name)) {
            $query->andWhere(['like', 'p.name', $this->name]);
        }

        if (!empty($this->start_date)) {
            $query->andWhere(['>=', 'p.start_date', $this->start_date]);
        }

        if (!empty($this->end_date)) {
            $query->andWhere(['<=', 'p.end_date', $this->end_date]);
        }

        if (!empty($this->id_status)) {
            $query->andWhere(['p.id_status' => $this->id_status]);
        }

        // 🔥 ORDEN POR DEFECTO: ÚLTIMO REGISTRADO PRIMERO
        $query->orderBy(['p.id' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
                'pageSizeParam' => 'per-page',
                'pageParam' => 'page_promotions',
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => ['asc' => ['p.id' => SORT_ASC], 'desc' => ['p.id' => SORT_DESC]],
                    'name' => ['asc' => ['p.name' => SORT_ASC], 'desc' => ['p.name' => SORT_DESC]],
                    'start_date' => ['asc' => ['p.start_date' => SORT_ASC], 'desc' => ['p.start_date' => SORT_DESC]],
                    'end_date' => ['asc' => ['p.end_date' => SORT_ASC], 'desc' => ['p.end_date' => SORT_DESC]],
                    'id_status' => ['asc' => ['p.id_status' => SORT_ASC], 'desc' => ['p.id_status' => SORT_DESC]],
                ],
            ],
        ]);
    }

    // ============================================
    // LISTA DE ESTADOS PARA FILTRO
    // ============================================
    public static function getStatusOptions()
    {
        return Status::find()
            ->select(['status', 'id_status'])
            ->where(['IN', 'status', ['Activo', 'Programado', 'Suspendido', 'Publicado', 'Inactivo', 'Cancelado']])
            ->indexBy('id_status')
            ->column();
    }
}