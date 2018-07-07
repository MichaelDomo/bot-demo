<?php

namespace app\components\bot\entities;

/**
 * Class Button
 * @package app\components\bot\entities
 */
class Button
{
    const TYPE_SIGN_IN = 'signin';
    const TYPE_OPEN_URL = 'openUrl';
    const TYPE_IM_BACK = 'imBack';
    const TYPE_POST_BACK = 'postBack';
    const TYPE_SHOW_IMAGE= 'showImage';

    public $type;
    public $title;
    public $value;

    /**
     * Button constructor.
     * @param $type
     * @param $title
     * @param $value
     */
    public function __construct($title, $value, $type)
    {
        $this->type = $type;
        $this->title = $title;
        $this->value = $value;
    }
}
