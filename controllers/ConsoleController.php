<?php

namespace app\components\bot\controllers;

use Yii;
use yii\console\Controller;

/**
 * Class BotController
 * @package app\bot\controllers
 */
class ConsoleController extends Controller
{
    /**
     * Bot action
     */
    public function actionEndpoint()
    {
        /** @var \app\components\bot\BotServer $bot */
        $bot = Yii::$app->get('botServer');
        $bot->initRequests();
    }
}
