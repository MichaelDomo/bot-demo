<?php

namespace app\components\bot;

use Yii;
use yii\helpers\Json;

/**
 * Class Input
 * @package app\components\bot
 */
class PhpInput
{
    /**
     * Get the info from the bot response
     * @return string|object String or Object depending. Response from the bot api.
     */
    public static function getRequestData()
    {
        $result = Json::decode(file_get_contents('php://input'), false);
        Yii::error($result, 'clientRequest');
        return $result;
    }
}
