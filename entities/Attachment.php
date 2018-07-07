<?php

namespace app\components\bot\entities;

/**
 * Class Attachment
 * @package app\components\bot\entities
 */
class Attachment
{
    const TYPE_PNG = 'image/png';

    public $contentType;
    public $contentUrl;
    public $name;

    /**
     * Attachment constructor.
     * @param $contentUrl
     * @param $name
     * @param string $contentType
     */
    public function __construct($contentUrl, $name, $contentType = self::TYPE_PNG)
    {
        $this->contentUrl = $contentUrl;
        $this->contentType = $contentType;
        $this->name = $name;
    }
}
