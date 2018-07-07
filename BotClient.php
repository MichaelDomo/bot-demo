<?php

namespace app\components\bot;

use yii\base\Component;

/**
 * Here goes your methods to execute actions when the bot received
 * any event (a new message, a new contact, a new image, etc.)
 * Class BotClient
 *
 * @package app\components
 *
 * @property string|object $postRaw
 */
class BotClient extends Component
{
    public $client;
    public $secret;
    /** @var Bot */
    private $bot;

    /**
     * Init request
     */
    public function initRequest()
    {
        $requestData = PhpInput::getRequestData();
        $this->bot = BotFactory::buildChanel(
            $this->client,
            $this->secret,
            $requestData->channelId,
            $requestData
        );
        $this->fireEventHandler($requestData->type);
    }

    /**
     * Manage the event firing the right method
     *
     * @param string $type Method to fire
     * @throws \DomainException
     */
    private function fireEventHandler($type)
    {
        $methodName = $type . 'EventHandler';
        if (method_exists($this->bot, $methodName)) {
            $this->bot->$methodName();

            return;
        }
        throw new \DomainException('Method not found!');
    }
}
