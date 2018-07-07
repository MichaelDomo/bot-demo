<?php

namespace app\components\bot\providers;

use app\components\bot\Bot;

/**
 * Class WebchatBot
 * @package app\components\bot\provider
 */
final class WebchatBot extends Bot
{
    protected $serviceUrl = 'https://webchat.botframework.com';

    /**
     * Update conversation.
     */
    public function conversationUpdateEventHandler()
    {
        $this->updateConversation();
        $this->doRequest();
    }
}
