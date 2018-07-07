<?php

namespace app\components\bot\services;

use app\components\bot\entities\Button;
use app\components\bot\entities\Content;
use app\components\bot\entities\Conversation;
use app\components\bot\entities\From;
use app\components\bot\entities\Image;
use app\components\bot\entities\ReplyMessage;
use app\components\bot\entities\Card;
use app\models\FreelanceProjects;
use app\models\user\bot\UserBotConversation;
use Yii;
use yii\helpers\Url;

/**
 * Формирует сообщения.
 * @package app\components\bot
 */
class Messenger
{
    // TODO Надо другую картинку, в скайпе она обрезается
    const IMAGE_PATH = 'https://spylance.com/web/images/bot/';

    /** @var UserBotConversation */
    private $conversation;
    private $channelId;
    private $fromName;
    private $from;
    private $to;

    /**
     * Default reply message.
     * @return ReplyMessage
     */
    public function defaultReply()
    {
        return $this->prepareReplyMessage(Yii::t('bot', 'Something wrong. Type “help” to watch possible commands.'));
    }

    /**
     * @param \yii\db\ActiveRecord[]|\app\models\user\UserSettings[] $userSettings
     * @return ReplyMessage
     */
    public function settings($userSettings)
    {
        $reply = $this->prepareReplyMessage();
        $content = new Content(
            Yii::t('bot', 'Choose your saved settings of filter!'),
            [],
            [new Image(self::IMAGE_PATH . 'connect.png')]
        );
        foreach ($userSettings as $set) {
            $content->addButton(new Button($set->name, 'setsettings ' . $set->id, Button::TYPE_IM_BACK));
        }
        $reply->addCard(new Card($content));

        return $reply;
    }

    /**
     * @return ReplyMessage
     */
    public function locale()
    {
        $reply = $this->prepareReplyMessage(Yii::t('bot', 'Hi! I\'m Spylance bot.'));
        $reply->addCard(new Card(new Content(
            Yii::t('bot', 'Select language!'),
            [
                new Button(Yii::t('bot', 'English'), 'locale en', Button::TYPE_IM_BACK),
                new Button(Yii::t('bot', 'Russian'), 'locale ru', Button::TYPE_IM_BACK)
            ],
            [new Image(self::IMAGE_PATH . 'connect.png')]
        )));
        return $reply;
    }

    /**
     * @param string $message
     * @return ReplyMessage
     */
    public function welcome($message = '')
    {
        $reply = $this->prepareReplyMessage($message);
        $reply->addCard(new Card(new Content(
            Yii::t('bot', 'You need to authorize me!'),
            [new Button(Yii::t('bot', 'Connect'), 'connect', Button::TYPE_IM_BACK)],
            [new Image(self::IMAGE_PATH . 'connect.png')]
        )));
        return $reply;
    }

    /**
     * @param array|FreelanceProjects[] $items
     * @return ReplyMessage
     */
    public function projects(array $items = [])
    {
        $reply = $this->prepareReplyMessage();
        foreach ($items as $project) {
            $text = Yii::t('bot', 'Title') . ': ' . $project->title . "\n\r" .
            (
                $project->price ?
                    Yii::t('bot', 'Price') . ': ' . $project->getPrice() . ' ' . $project->getCurrency() : ''
            );

            $content = new Content(
                $text,
                [
                    new Button(
                        Yii::t('bot', 'Open'),
                        Url::to(['redirect/to', 'url' => $project->link], true),
                        Button::TYPE_OPEN_URL
                    ),
                    new Button(Yii::t('bot', 'Bookmark'), 'bookmark ' . $project->id, Button::TYPE_IM_BACK)
                ],
                [new Image($project->parserModel->getAbsImageUrl() ?: self::IMAGE_PATH . 'logo.png')]
            );
            $reply->addCard(new Card($content, Card::TYPE_HERO));
        }

        return $reply;
    }

    /**
     * @return ReplyMessage
     */
    public function start()
    {
        $reply = $this->prepareReplyMessage();
        $reply->addCard(new Card(new Content(
            '',
            [new Button(Yii::t('bot', 'Now you can start!'), 'start', Button::TYPE_IM_BACK)],
            [new Image(self::IMAGE_PATH . 'connect.png')]
        )));

        return $reply;
    }

    /**
     * @param string $message
     * @return ReplyMessage
     */
    public function back($message = '')
    {
        $reply = $this->prepareReplyMessage($message);
        $reply->addCard(new Card(new Content(
            $message ?: Yii::t('bot', 'I\'m back!'),
            [new Button(Yii::t('bot', 'Say hello!'), 'hi', Button::TYPE_IM_BACK)],
            [new Image(self::IMAGE_PATH . 'connect.png')]
        )));

        return $reply;
    }

    /**
     * @return ReplyMessage
     */
    public function code()
    {
        $reply = $this->prepareReplyMessage();
        $reply->addCard(new Card(new Content(
            '',
            [new Button(Yii::t('bot', 'Code link!'), Url::to('/user/bot', true), Button::TYPE_OPEN_URL)],
            [new Image(self::IMAGE_PATH . 'connect.png')]
        )));

        return $reply;
    }

    /**
     * @param string $message
     * @return ReplyMessage
     */
    public function reply($message)
    {
        return $this->prepareReplyMessage($message);
    }

    /**
     * Немного кастыльненько добавляем параметры из data. Устанавливаем язык приложения.
     * @param UserBotConversation $conversation
     */
    public function setConversation(UserBotConversation $conversation)
    {
        $this->conversation = $conversation;
        $this->from = $conversation->recipient_id;
        $this->to = $conversation->conversation_id;
        $this->channelId = $conversation->data['channelId'];
        $this->fromName = isset($conversation->data['recipient']['name']) ?
            $conversation->data['recipient']['name'] : null;
        if (null !== $conversation && null !== ($locale = $conversation->locale)) {
            Yii::$app->language = $locale;
        }
    }

    /**
     * @param $message
     * @param string $type
     * @return ReplyMessage
     */
    private function prepareReplyMessage($message = '', $type = ReplyMessage::TYPE_MESSAGE)
    {
        if (!$this->conversation) {
            throw new \DomainException(Yii::t('bot', 'Conversation must be set'));
        }

        return new ReplyMessage(
            $this->channelId,
            $message,
            new From($this->from, $this->fromName),
            new Conversation($this->to),
            $type
        );
    }
}
