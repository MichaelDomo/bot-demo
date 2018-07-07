<?php

namespace app\components\bot\providers;

use app\components\bot\Bot;

/**
 * Class FacebookBot
 * @package app\components\bot\provider
 */
final class FacebookBot extends Bot
{
    protected $serviceUrl = 'https://facebook.botframework.com';

    /**
     * Update conversation.
     */
    public function conversationUpdateEventHandler()
    {
        $this->updateConversation();
        $this->doRequest();
    }
}
