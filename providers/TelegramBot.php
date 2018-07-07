<?php

namespace app\components\bot\providers;

use app\components\bot\Bot;

/**
 * Class TelegramBot
 * @package app\components\bot\provider
 */
final class TelegramBot extends Bot
{
    protected $serviceUrl = 'https://telegram.botframework.com';
}
