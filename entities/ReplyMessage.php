<?php

namespace app\components\bot\entities;

/**
 * Class ReplyMessage
 * @package app\components\bot\entities
 */
class ReplyMessage
{
    const TYPE_MESSAGE = 'message';

    public $type;
    public $text;
    public $from;
    public $conversation;
    public $attachments;
    public $channelId;

    /**
     * ReplyMessage constructor.
     * @param $channelId
     * @param $text
     * @param From $from
     * @param Conversation $conversation
     * @param string $type
     * @param array $attachments
     */
    public function __construct($channelId, $text, From $from, Conversation $conversation, $type = self::TYPE_MESSAGE, array $attachments = [])
    {
        $this->channelId = $channelId;
        $this->type = $type;
        $this->text = $text;
        $this->from = $from;
        $this->conversation = $conversation;
        $this->attachments = $attachments;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @param Card $attachment
     */
    public function addCard(Card $attachment)
    {
        $this->attachments[] = $attachment;
    }
}
