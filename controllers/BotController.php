<?php

namespace app\components\bot\controllers;

use Yii;
use yii\base\Controller;

/**
 * Class BotController
 * @package app\bot\controllers
 */
class BotController extends Controller
{
    /**
     * Bot action
     */
    public function actionEndpoint()
    {
        /** @var \app\components\bot\BotClient $bot */
        $bot = Yii::$app->get('botClient');
        $bot->initRequest();
    }
}
