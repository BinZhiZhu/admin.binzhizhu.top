<?php

namespace app\controllers;

use app\models\DevUsers;
use yii\web\Controller;
use Exception;
use Yii;

class LoginController extends Controller
{

    public $layout = false;

    public function actionIndex()
    {
        Yii::$app->view->title = 'Admin管理系统';
        //   $host = Yii::$app->request->getAbsoluteUrl();//绝对路径
        return $this->render('index');
    }

    /**
     *  用户登录
     * @return object
     * @throws Exception
     */
    public function actionLogin()
    {

        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        $username = trim($username);
        $password = trim($password);

        $hash_password = Yii::$app->security->generatePasswordHash($password);//加密

        $justifyPwd = Yii::$app->security->validatePassword($password, $hash_password);//校验


        $user = DevUsers::findOne([
            'username' => $username,
//            'password' => $password,
//            'salt'=>$hash_password,
        ]);

        //有该用户且通过密码校验
        if ($user && $justifyPwd) {
            DevUsers::updateAll([
                'lastvisit_ip' => Yii::$app->request->getUserIP(),
                'lastvisit_time' => time(),
                'login_count' => $user->login_count + 1,//登录次数+1
            ]);
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => \yii\web\Response::FORMAT_JSON,
                'data' => [
                    'message' => '登录成功',
                    'code' => 100,
                ]
            ]);
        }

        //有该用户但是密码没有通过验证
        if ($user && !$justifyPwd) {
            return Yii::createObject([
                'class' => 'yii\web\Response',
                'format' => \yii\web\Response::FORMAT_JSON,
                'data' => [
                    'message' => '密码错误',
                    'code' => -101,
                ]
            ]);
        }

        //认为是第一次登录
        $user_data = [
            'username' => $username,
            'password' => $password,
            'salt' => '',
            'hash_pwd' => $hash_password,
            'status' => 1,
            'register_ip' => Yii::$app->request->getUserIP(),
            'register_time' => time(),
            'lastvisit_time' => time(),
            'lastvisit_ip' => Yii::$app->request->getUserIP(),
        ];

        $user = new DevUsers();
        $user->attributes = $user_data;
        $user->save(false);

        return Yii::createObject([
            'class' => 'yii\web\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'data' => [
                'message' => '登录成功',
                'code' => 101,
            ]
        ]);
    }

}