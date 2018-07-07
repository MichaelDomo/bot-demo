<?php

namespace app\components\bot\providers;

use app\components\bot\Bot;

/**
 * Class SkypeBot
 * @package app\components\bot\provider
 */
final class SkypeBot extends Bot
{
    const ACTION_ADD = 'add';
    protected $serviceUrl = 'https://smba.trafficmanager.net/apis';

    /**
     * Update conversation.
     */
    public function contactRelationUpdateEventHandler()
    {
        if ($this->requestData->action === self::ACTION_ADD) {
            $this->updateConversation();
            $this->doRequest();
        }
    }
}
