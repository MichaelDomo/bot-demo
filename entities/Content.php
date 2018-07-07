<?php

namespace app\components\bot\entities;

/**
 * Class Content
 * @package app\components\bot\entities
 */
class Content
{
    public $text;
    public $buttons;
    public $images;
    public $items;

    /**
     * Content constructor.
     * @param $text
     * @param array $buttons
     * @param array $images
     * @param array $items
     */
    public function __construct($text, array $buttons = [], array $images = [], array $items = [])
    {
        $this->text = $text;
        $this->buttons = $buttons;
        $this->images = $images;
        $this->items = $items;
    }

    /**
     * @param Button $button
     */
    public function addButton(Button $button)
    {
        $this->buttons[] = $button;
    }

    /**
     * @param Image $image
     */
    public function addImage(Image $image)
    {
        $this->images[] = $image;
    }

    /**
     * @param Item $item
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;
    }
}
