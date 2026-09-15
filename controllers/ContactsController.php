<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Contacts;
use app\models\TypeContact;
use app\models\Status;
use app\models\Company;
use app\components\ErrorManager;
use yii\web\Response;

class ContactsController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    // ============================================
    // LISTA DE CONTACTOS CON PAGINACIÓN
    // ============================================
    public function actionIndex()
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user) {
                return $this->redirect(['site/login']);
            }

            if ($user->isSuperAdmin() && empty($empresaId)) {
                Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
                return $this->redirect(['empresa/index']);
            }

            // 🔥 QUERY BASE
            $query = Contacts::find()
                ->joinWith(['company', 'status', 'typeContact']);

            // FILTRO POR ROL Y EMPRESA
            if ($user->isSuperAdmin()) {
                if (!empty($empresaId)) {
                    $query->andWhere(['Contacts.id_company' => $empresaId]);
                }
            } elseif ($user->isAdmin() && !$user->isSuperAdmin()) {
                $query->andWhere(['Contacts.id_company' => $user->id_company]);
            } elseif ($user->isAgent()) {
                $query->andWhere(['Contacts.id_company' => $user->id_company]);
            }

            // 🔥 FILTROS DE BÚSQUEDA
            $search = Yii::$app->request->get('search', '');
            $status = Yii::$app->request->get('status', '');
            $type = Yii::$app->request->get('type', '');

            if (!empty($search)) {
                $query->andWhere(['or',
                    ['like', 'Contacts.name', $search],
                    ['like', 'Contacts.last_name', $search],
                    ['like', 'Contacts.phone', $search],
                    ['like', 'Contacts.email', $search],
                ]);
            }

            if (!empty($status)) {
                $statusModel = Status::find()->where(['status' => $status])->one();
                if ($statusModel) {
                    $query->andWhere(['Contacts.id_status' => $statusModel->id_status]);
                }
            }

            if (!empty($type)) {
                $typeModel = TypeContact::find()->where(['type_contact' => $type])->one();
                if ($typeModel) {
                    $query->andWhere(['Contacts.id_type_contact' => $typeModel->id_type_contact]);
                }
            }

            // 🔥 DATAPROVIDER CON PAGINACIÓN
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                    'pageSizeParam' => 'per-page',
                    'pageParam' => 'page',
                ],
                'sort' => [
                    'defaultOrder' => [
                        'name' => SORT_ASC,
                        'last_name' => SORT_ASC,
                    ],
                    'attributes' => [
                        'id_contact' => [
                            'asc' => ['Contacts.id_contact' => SORT_ASC],
                            'desc' => ['Contacts.id_contact' => SORT_DESC],
                        ],
                        'name' => [
                            'asc' => ['Contacts.name' => SORT_ASC],
                            'desc' => ['Contacts.name' => SORT_DESC],
                        ],
                        'last_name' => [
                            'asc' => ['Contacts.last_name' => SORT_ASC],
                            'desc' => ['Contacts.last_name' => SORT_DESC],
                        ],
                        'phone' => [
                            'asc' => ['Contacts.phone' => SORT_ASC],
                            'desc' => ['Contacts.phone' => SORT_DESC],
                        ],
                        'email' => [
                            'asc' => ['Contacts.email' => SORT_ASC],
                            'desc' => ['Contacts.email' => SORT_DESC],
                        ],
                        'id_status' => [
                            'asc' => ['Contacts.id_status' => SORT_ASC],
                            'desc' => ['Contacts.id_status' => SORT_DESC],
                        ],
                        'id_type_contact' => [
                            'asc' => ['Contacts.id_type_contact' => SORT_ASC],
                            'desc' => ['Contacts.id_type_contact' => SORT_DESC],
                        ],
                    ],
                ],
            ]);

            $contacts = $dataProvider->getModels();

            // 🔥 ESTADÍSTICAS
            $countQuery = clone $query;
            $totalContacts = $countQuery->count();

            $statusActivo = Status::find()->where(['status' => 'Activo'])->one();

            $activeQuery = Contacts::find();
            if ($user->isSuperAdmin() && !empty($empresaId)) {
                $activeQuery->andWhere(['id_company' => $empresaId]);
            } elseif (!$user->isSuperAdmin()) {
                $activeQuery->andWhere(['id_company' => $user->id_company]);
            }
            if ($statusActivo) {
                $activeQuery->andWhere(['id_status' => $statusActivo->id_status]);
            }
            $activeContacts = $activeQuery->count();

            $inactiveContacts = $totalContacts - $activeContacts;

            // Listas para filtros
            $statusList = Status::find()
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            $typeList = TypeContact::find()
                ->select(['type_contact', 'id_type_contact'])
                ->indexBy('id_type_contact')
                ->column();

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'contacts' => $contacts,
                'totalContacts' => $totalContacts,
                'activeContacts' => $activeContacts,
                'inactiveContacts' => $inactiveContacts,
                'statusList' => $statusList,
                'typeList' => $typeList,
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionIndex: ' . $e->getMessage(), 'contacts');
            Yii::$app->session->setFlash('error', 'Error al cargar los contactos: ' . $e->getMessage());

            return $this->render('index', [
                'dataProvider' => new ActiveDataProvider([
                    'query' => Contacts::find()->where('1=0'),
                ]),
                'contacts' => [],
                'totalContacts' => 0,
                'activeContacts' => 0,
                'inactiveContacts' => 0,
                'statusList' => [],
                'typeList' => [],
                'search' => '',
                'status' => '',
                'type' => '',
                'isAdmin' => false,
                'isAgent' => false,
                'isSuperAdmin' => false,
            ]);
        }
    }

    // ============================================
    // VER CONTACTO - MODAL (PANEL LATERAL)
    // ============================================
    public function actionViewModal($id)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_HTML;
            
            $model = Contacts::find()
                ->where(['id_contact' => $id])
                ->with(['company', 'status', 'typeContact'])
                ->one();
            
            if (!$model) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'Contacto no encontrado'
                ]);
            }

            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'No tienes permiso para ver este contacto.'
                ]);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                return $this->renderPartial('_view_modal', [
                    'error' => 'No tienes permiso para ver este contacto.'
                ]);
            }

            return $this->renderPartial('_view_modal', [
                'model' => $model,
                'error' => null,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionViewModal: ' . $e->getMessage(), 'contacts');
            return $this->renderPartial('_view_modal', [
                'error' => 'Error al cargar el contacto: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // ACTUALIZAR CONTACTO - MODAL (PANEL LATERAL)
    // ============================================
    public function actionUpdateModal($id)
    {
        try {
            Yii::$app->response->format = Response::FORMAT_HTML;
            
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            $model = Contacts::find()
                ->where(['id_contact' => $id])
                ->one();
            
            if (!$model) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'Contacto no encontrado'
                ]);
            }

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar este contacto.'
                ]);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                return $this->renderPartial('_update_modal', [
                    'error' => 'No tienes permiso para editar este contacto.'
                ]);
            }

            $statusList = Status::find()
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            $typeList = TypeContact::find()
                ->select(['type_contact', 'id_type_contact'])
                ->indexBy('id_type_contact')
                ->column();

            $companyList = [];
            if ($user->isSuperAdmin()) {
                $companyList = Company::find()
                    ->select(['name', 'id_company'])
                    ->orderBy(['name' => SORT_ASC])
                    ->indexBy('id_company')
                    ->column();
            }

            if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        return $this->renderPartial('_update_modal', [
                            'model' => $model,
                            'statusList' => $statusList,
                            'typeList' => $typeList,
                            'companyList' => $companyList,
                            'success' => 'Contacto actualizado exitosamente'
                        ]);
                    } else {
                        return $this->renderPartial('_update_modal', [
                            'model' => $model,
                            'statusList' => $statusList,
                            'typeList' => $typeList,
                            'companyList' => $companyList,
                            'error' => 'Error al actualizar: ' . implode(', ', $model->getFirstErrors())
                        ]);
                    }
                } catch (\Exception $e) {
                    return $this->renderPartial('_update_modal', [
                        'model' => $model,
                        'statusList' => $statusList,
                        'typeList' => $typeList,
                        'companyList' => $companyList,
                        'error' => 'Error al actualizar: ' . $e->getMessage()
                    ]);
                }
            }

            return $this->renderPartial('_update_modal', [
                'model' => $model,
                'statusList' => $statusList,
                'typeList' => $typeList,
                'companyList' => $companyList,
            ]);

        } catch (\Exception $e) {
            Yii::error('Error en actionUpdateModal: ' . $e->getMessage(), 'contacts');
            return $this->renderPartial('_update_modal', [
                'error' => 'Error al cargar el formulario: ' . $e->getMessage()
            ]);
        }
    }

    // ============================================
    // VER CONTACTO (Vista completa)
    // ============================================
    public function actionView($id)
    {
        try {
            $model = $this->findModel($id);
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver este contacto.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para ver este contacto.');
                return $this->redirect(['index']);
            }

            return $this->render('view', [
                'model' => $model,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Contacto no encontrado.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en actionView: ' . $e->getMessage(), 'contacts');
            Yii::$app->session->setFlash('error', 'Error al cargar el contacto.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // CREAR CONTACTO
    // ============================================
    public function actionCreate()
    {
        $model = new Contacts();
        $user = Yii::$app->user->identity;
        $empresaId = Yii::$app->session->get('empresa_id');

        if ($user->isSuperAdmin() && empty($empresaId)) {
            Yii::$app->session->setFlash('warning', 'Por favor, selecciona una empresa para continuar.');
            return $this->redirect(['empresa/index']);
        }

        if ($user->isSuperAdmin()) {
            if (!empty($empresaId)) {
                $model->id_company = $empresaId;
            }
        } elseif ($user && !empty($user->id_company)) {
            $model->id_company = $user->id_company;
        }

        $statusActivo = Status::find()->where(['status' => 'Activo'])->one();
        if ($statusActivo) {
            $model->id_status = $statusActivo->id_status;
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Contacto creado exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id_contact]);
                } else {
                    $errors = $model->getErrors();
                    $errorMessages = [];
                    foreach ($errors as $attribute => $errorList) {
                        $label = $model->getAttributeLabel($attribute);
                        $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                    }
                    Yii::$app->session->setFlash('error', 'Error al guardar el contacto:<br>' . implode('<br>', $errorMessages));
                }
            } catch (\Exception $e) {
                Yii::error('Error en actionCreate: ' . $e->getMessage(), 'contacts');
                Yii::$app->session->setFlash('error', 'Error al crear el contacto: ' . $e->getMessage());
            }
        }

        $statusList = Status::find()
            ->select(['status', 'id_status'])
            ->indexBy('id_status')
            ->column();

        $typeList = TypeContact::find()
            ->select(['type_contact', 'id_type_contact'])
            ->indexBy('id_type_contact')
            ->column();

        return $this->render('create', [
            'model' => $model,
            'statusList' => $statusList,
            'typeList' => $typeList,
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    // ============================================
    // ACTUALIZAR CONTACTO (Vista completa)
    // ============================================
    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar este contacto.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para editar este contacto.');
                return $this->redirect(['index']);
            }

            if ($model->load(Yii::$app->request->post())) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Contacto actualizado exitosamente.');
                        return $this->redirect(['view', 'id' => $model->id_contact]);
                    } else {
                        $errors = $model->getErrors();
                        $errorMessages = [];
                        foreach ($errors as $attribute => $errorList) {
                            $label = $model->getAttributeLabel($attribute);
                            $errorMessages[] = $label . ': ' . implode(', ', $errorList);
                        }
                        Yii::$app->session->setFlash('error', 'Error al actualizar el contacto:<br>' . implode('<br>', $errorMessages));
                    }
                } catch (\Exception $e) {
                    Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'contacts');
                    Yii::$app->session->setFlash('error', 'Error al actualizar el contacto: ' . $e->getMessage());
                }
            }

            $statusList = Status::find()
                ->select(['status', 'id_status'])
                ->indexBy('id_status')
                ->column();

            $typeList = TypeContact::find()
                ->select(['type_contact', 'id_type_contact'])
                ->indexBy('id_type_contact')
                ->column();

            return $this->render('update', [
                'model' => $model,
                'statusList' => $statusList,
                'typeList' => $typeList,
                'isAdmin' => $user->isAdmin(),
                'isAgent' => $user->isAgent(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Contacto no encontrado.');
            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error en actionUpdate: ' . $e->getMessage(), 'contacts');
            Yii::$app->session->setFlash('error', 'Error al actualizar el contacto.');
            return $this->redirect(['index']);
        }
    }

    // ============================================
    // ELIMINAR CONTACTO
    // ============================================
    public function actionDelete($id)
    {
        try {
            $user = Yii::$app->user->identity;
            $empresaId = Yii::$app->session->get('empresa_id');

            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar contactos.');
                return $this->redirect(['index']);
            }

            $model = $this->findModel($id);

            if ($user->isSuperAdmin() && !empty($empresaId) && $model->id_company != $empresaId) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este contacto.');
                return $this->redirect(['index']);
            }

            if (!$user->isSuperAdmin() && $model->id_company != $user->id_company) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar este contacto.');
                return $this->redirect(['index']);
            }

            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Contacto eliminado exitosamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al eliminar el contacto.');
            }

        } catch (NotFoundHttpException $e) {
            Yii::$app->session->setFlash('error', 'Contacto no encontrado.');
        } catch (\Exception $e) {
            Yii::error('Error en actionDelete: ' . $e->getMessage(), 'contacts');
            Yii::$app->session->setFlash('error', 'Error al eliminar el contacto.');
        }

        return $this->redirect(['index']);
    }

    // ============================================
    // TIPOS DE CONTACTO (CRUD)
    // ============================================
    
    public function actionTypes()
    {
        try {
            $user = Yii::$app->user->identity;
            
            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para gestionar tipos de contacto.');
                return $this->redirect(['index']);
            }

            $types = TypeContact::find()
                ->orderBy(['type_contact' => SORT_ASC])
                ->all();

            return $this->render('types', [
                'types' => $types,
                'isAdmin' => $user->isAdmin(),
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        } catch (\Exception $e) {
            ErrorManager::handle($e, 'Error al cargar tipos de contacto');
            return $this->render('types', [
                'types' => [],
                'isAdmin' => false,
                'isSuperAdmin' => false,
            ]);
        }
    }

    public function actionCreateType()
    {
        $user = Yii::$app->user->identity;
        
        if (!$user->isAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para crear tipos de contacto.');
            return $this->redirect(['types']);
        }

        $model = new TypeContact();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tipo de contacto creado exitosamente.');
            return $this->redirect(['types']);
        }

        return $this->render('create-type', [
            'model' => $model,
        ]);
    }

    public function actionUpdateType($id)
    {
        $user = Yii::$app->user->identity;
        
        if (!$user->isAdmin()) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para editar tipos de contacto.');
            return $this->redirect(['types']);
        }

        $model = TypeContact::findOne($id);
        
        if (!$model) {
            throw new NotFoundHttpException('Tipo de contacto no encontrado.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tipo de contacto actualizado exitosamente.');
            return $this->redirect(['types']);
        }

        return $this->render('update-type', [
            'model' => $model,
        ]);
    }

    public function actionDeleteType($id)
    {
        try {
            $user = Yii::$app->user->identity;
            
            if (!$user->isAdmin()) {
                Yii::$app->session->setFlash('error', 'No tienes permiso para eliminar tipos de contacto.');
                return $this->redirect(['types']);
            }

            $model = TypeContact::findOne($id);
            
            if (!$model) {
                throw new NotFoundHttpException('Tipo de contacto no encontrado.');
            }

            $contactsCount = Contacts::find()
                ->where(['id_type_contact' => $id])
                ->count();

            if ($contactsCount > 0) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este tipo porque tiene contactos asociados.');
                return $this->redirect(['types']);
            }

            $model->delete();
            Yii::$app->session->setFlash('success', 'Tipo de contacto eliminado exitosamente.');

        } catch (\Exception $e) {
            Yii::error('Error en actionDeleteType: ' . $e->getMessage(), 'contacts');
            Yii::$app->session->setFlash('error', 'Error al eliminar el tipo de contacto.');
        }

        return $this->redirect(['types']);
    }

    // ============================================
    // FINDER
    // ============================================
    protected function findModel($id)
    {
        $model = Contacts::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('El contacto solicitado no existe.');
        }
        return $model;
    }
}