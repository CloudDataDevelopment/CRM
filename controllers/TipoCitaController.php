<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\SalesTracking;

class TipoCitaController extends Controller
{
    // Lista de tipos de cita
    public function actionIndex()
    {
        $tipos = SalesTracking::find()
            ->where(['not', ['status_sales' => null]])
            ->andWhere(['<>', 'status_sales', ''])
            ->all();
        
        return $this->render('index', [
            'tipos' => $tipos,
        ]);
    }
    
    // Crear nuevo tipo de cita
    public function actionCreate()
    {
        $model = new SalesTracking();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tipo de cita creado exitosamente');
            return $this->redirect(['index']);
        }
        
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    
    // Editar tipo de cita
    public function actionUpdate($id)
    {
        $model = SalesTracking::findOne($id);
        
        if (!$model) {
            throw new NotFoundHttpException('Tipo de cita no encontrado');
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tipo de cita actualizado exitosamente');
            return $this->redirect(['index']);
        }
        
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    
    // Eliminar tipo de cita
    public function actionDelete($id)
    {
        $model = SalesTracking::findOne($id);
        if ($model) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Tipo de cita eliminado exitosamente');
        }
        return $this->redirect(['index']);
    }
}