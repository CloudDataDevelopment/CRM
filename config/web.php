<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'site/login',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'forceCopy' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'abcdef',
            'enableCsrfValidation' => true,
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => [
                'lifetime' => 1800, // 30 minutos
                'httponly' => true,
            ],
            'timeout' => 1800, // 30 minutos
            'useCookies' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'authTimeout' => 1800, // 30 minutos
            'loginUrl' => ['site/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        // ============================================
        // 🔥 URL MANAGER CORREGIDO
        // Reglas específicas PRIMERO, genéricas al final
        // ============================================
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

                // ============================================
                // 🔥 RUTAS ESTÁTICAS (sin parámetros)
                // ============================================
                '' => 'site/login',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                'dashboard' => 'dashboard/index',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: LEAD
                // ============================================
                'lead/view-modal/<id:\d+>' => 'lead/view-modal',
                'lead/view-modal' => 'lead/view-modal',
                'lead/details/<id:\d+>' => 'lead/details',
                'lead/update/<id:\d+>' => 'lead/update',
                'lead/view/<id:\d+>' => 'lead/view',
                'lead/delete/<id:\d+>' => 'lead/delete',
                'lead/restore/<id:\d+>' => 'lead/restore',
                'lead/update-status/<id:\d+>/<status:\w+>' => 'lead/update-status',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: QUOTE
                // ============================================
                'quote/view-modal/<id:\d+>' => 'quote/view-modal',
                'quote/update-modal/<id:\d+>' => 'quote/update-modal',
                'quote/details/<id:\d+>' => 'quote/details',
                'quote/view/<id:\d+>' => 'quote/view',
                'quote/update/<id:\d+>' => 'quote/update',
                'quote/payments/<id:\d+>' => 'quote/payments',
                'quote/delete/<id:\d+>' => 'quote/delete',
                'quote/restore/<id:\d+>' => 'quote/restore',
                'quote/create/<leadId:\d+>' => 'quote/create',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: SALES-TRACKING
                // ============================================
                'sales-tracking/details/<id:\d+>' => 'sales-tracking/details',
                'sales-tracking/view-modal/<id:\d+>' => 'sales-tracking/view-modal',
                'sales-tracking/update-modal/<id:\d+>' => 'sales-tracking/update-modal',
                'sales-tracking/view/<id:\d+>' => 'sales-tracking/view',
                'sales-tracking/update/<id:\d+>' => 'sales-tracking/update',
                'sales-tracking/delete/<id:\d+>' => 'sales-tracking/delete',
                'sales-tracking/restore/<id:\d+>' => 'sales-tracking/restore',
                'sales-tracking/create/<leadId:\d+>' => 'sales-tracking/create',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: CONTACTS
                // ============================================
                'contacts/view-modal/<id:\d+>' => 'contacts/view-modal',
                'contacts/update-modal/<id:\d+>' => 'contacts/update-modal',
                'contacts/view/<id:\d+>' => 'contacts/view',
                'contacts/update/<id:\d+>' => 'contacts/update',
                'contacts/delete/<id:\d+>' => 'contacts/delete',
                'contacts/restore/<id:\d+>' => 'contacts/restore',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: REPORT
                // ============================================
                'report/view-modal/<id:\d+>' => 'report/view-modal',
                'report/update-modal/<id:\d+>' => 'report/update-modal',
                'report/view/<id:\d+>' => 'report/view',
                'report/update/<id:\d+>' => 'report/update',
                'report/delete/<id:\d+>' => 'report/delete',
                'report/restore/<id:\d+>' => 'report/restore',
                'report/create/<leadId:\d+>' => 'report/create',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: RESERVATION
                // ============================================
                'reservation/view/<id:\d+>' => 'reservation/view',
                'reservation/update/<id:\d+>' => 'reservation/update',
                'reservation/delete/<id:\d+>' => 'reservation/delete',
                'reservation/create/<leadId:\d+>' => 'reservation/create',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: TASK
                // ============================================
                'task/view/<id:\d+>' => 'task/view',
                'task/update/<id:\d+>' => 'task/update',
                'task/delete/<id:\d+>' => 'task/delete',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: EMPRESA
                // ============================================
                'empresa/view/<id:\d+>' => 'empresa/view',
                'empresa/update/<id:\d+>' => 'empresa/update',
                'empresa/delete/<id:\d+>' => 'empresa/delete',
                'empresa/delete-logo/<id:\d+>' => 'empresa/delete-logo',
                'empresa/cambiar/<id:\d+>' => 'empresa/cambiar',
                'empresa/dashboard/<id:\d+>' => 'empresa/dashboard',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: USER-MANAGEMENT
                // ============================================
                'user-management/view/<id:\d+>' => 'user-management/view',
                'user-management/delete/<id:\d+>' => 'user-management/delete',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: MARKETING
                // ============================================
                'marketing/view/<id:\d+>' => 'marketing/view',
                'marketing/update/<id:\d+>' => 'marketing/update',
                'marketing/delete/<id:\d+>' => 'marketing/delete',

                // ============================================
                // 🔥 RUTAS ESPECÍFICAS: CALENDAR
                // ============================================
                'calendar/day-detail/<day:\d{4}-\d{2}-\d{2}>' => 'calendar/day-detail',

                // ============================================
                // 🔥 RUTAS GENÉRICAS (AL FINAL)
                // ============================================

                // Con id (más específica)
                '<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => '<controller>/<action>',

                // Sin id (con query string)
                '<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,

    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'allow' => true,
                'actions' => ['login', 'register', 'error'],
                'roles' => ['?'],
            ],
            [
                'allow' => true,
                'actions' => ['error'],
                'roles' => ['?', '@'],
            ],
            [
                'allow' => true,
                'roles' => ['@'],
            ],
            [
                'allow' => false,
            ],
        ],
        'denyCallback' => function ($rule, $action) {
            return Yii::$app->getResponse()->redirect(['site/login']);
        },
    ],
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;