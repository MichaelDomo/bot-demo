<?php

namespace app\components\bot\controllers;

use yii\web\Controller;

/**
 * Class BotController
 * @package app\bot\controllers
 */
class RedirectController extends Controller
{
    /**
     * Redirect action
     * @param $url
     * @return \yii\web\Response
     */
    public function actionTo($url)
    {
        return $this->redirect($url);
    }
}
