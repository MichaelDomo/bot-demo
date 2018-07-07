<?php

namespace app\components\bot;

/**
 * Class BotService
 * @package app\components\bot
 */
class BotFactory
{
    /**
     * @param string $client
     * @param string $secret
     * @param string $channelId
     * @param null|object $requestData
     * @return Bot|object
     */
    public static function buildChanel($client, $secret, $channelId, $requestData = null)
    {
        $botClassName = __NAMESPACE__ . "\\providers\\" . ucfirst($channelId) . 'Bot';
        if (!class_exists($botClassName)) {
            throw new \DomainException("Ops! Not exist bot for channelId '$channelId' ($botClassName)");
        }

        return \Yii::createObject($botClassName, [$client, $secret, $requestData]);
    }
}
