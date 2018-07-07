<?php

namespace app\components\bot\entities;

/**
 * Class SignInCard
 * @package app\components\bot\entities
 */
class Card
{
    const TYPE_SIGN_IN = 'application/vnd.microsoft.card.signin';
    const TYPE_HERO = 'application/vnd.microsoft.card.hero';
    const TYPE_RECEIPT = 'application/vnd.microsoft.card.receipt';
    const TYPE_THUMBNAIL = 'application/vnd.microsoft.card.thumbnail';

    public $contentType;
    public $content;

    /**
     * SignInCard constructor.
     * @param Content $content
     * @param string $contentType
     */
    public function __construct(Content $content, $contentType = self::TYPE_HERO)
    {
        $this->content = $content;
        $this->contentType = $contentType;
    }
}
