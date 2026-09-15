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
        'urlManager' => [
            'enablePrettyUrl' => true,  // 🔥 Activado para URLs amigables
            'showScriptName' => false,   // 🔥 Ocultar index.php
            'rules' => [
                '' => 'site/login',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'site/register',
                'dashboard' => 'dashboard/index',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                'lead/view-modal/<id:\d+>' => 'lead/view-modal',
                'lead/view-modal' => 'lead/view-modal',
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