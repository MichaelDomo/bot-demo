<?php

namespace app\components\bot\services;

use app\components\bot\entities\ReplyMessage;
use app\models\user\bot\UserBotConversation;
use Yii;

/**
 * Анализирует команды пользователя
 * @package app\components\bot
 */
class CommandAnalyzer
{
    /** @var UserBotConversation */
    private $conversation;
    private $text;
    private $command;
    private $message;
    private $messenger;
    private $conversationService;

    /**
     * BotClient constructor.
     * @param ConversationService $conversationService
     * @param Messenger $messenger
     */
    public function __construct(
        ConversationService $conversationService,
        Messenger $messenger
    ) {
        $this->conversationService = $conversationService;
        $this->messenger = $messenger;
    }

    /**
     * @param string $text
     * @param UserBotConversation $conversation
     * @return ReplyMessage
     */
    public function analyze($text, UserBotConversation $conversation)
    {
        $this->text = $text;
        $this->conversation = $conversation;
        $this->messenger->setConversation($conversation);
        $this->initCommand();

        return $this->fireEventHandler();
    }

    /**
     *
     */
    public function initCommand()
    {
        $message = trim($this->text);
        $result = explode(' ', $message);
        if (is_array($result)) {
            $this->command = $result[0];
            $this->message = isset($result[1]) ? $result[1] : '';
        }
    }

    /**
     * @return ReplyMessage
     */
    private function fireEventHandler()
    {
        $methodName = $this->command . 'EventHandler';
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return $this->analyzeMessage();
    }

    /**
     * @return ReplyMessage
     */
    public function setsettingsEventHandler()
    {
        if ($this->conversation->status === UserBotConversation::STATUS_ACTIVE) {
            $result = $this->conversationService->updateUserSettings(
                $this->conversation,
                $this->message
            );

            if (true === $result) {
                return $this->messenger->reply(Yii::t('bot', 'Settings updated'));
            }

            return $this->messenger->reply(Yii::t('bot', $result));
        }

        return $this->messenger->reply('SetSettings');
    }

    /**
     * @return ReplyMessage
     */
    public function setEventHandler()
    {
        if ($this->conversation->status === UserBotConversation::STATUS_ACTIVE) {
            return $this->messenger->settings($this->conversation->user->settingsList);
        }

        return $this->messenger->reply(
            Yii::t('bot', 'Please type your email or login in SpyLance.')
        );
    }

    /**
     * @return ReplyMessage
     */
    private function connectEventHandler()
    {
        if ($this->conversation->status === UserBotConversation::STATUS_NOT_ACTIVE) {
            $this->conversationService->updateStatus(
                $this->conversation,
                UserBotConversation::STATUS_TRY_TO_CONNECT
            );

            return $this->messenger->reply(
                Yii::t('bot', 'Enter your email or username.')
            );
        }

        return $this->messenger->reply(
            Yii::t('bot', 'Please type your email or login in SpyLance.')
        );
    }

    /**
     * @return ReplyMessage
     */
    private function analyzeMessage()
    {
        if (UserBotConversation::STATUS_TRY_TO_CONNECT === $this->conversation->status) {
            if (null === $this->conversation->user_id) {
                $userCode = $this->conversationService->createCode(
                    $this->conversation,
                    $this->text
                );
                if (false === $userCode) {
                    return $this->messenger->reply(Yii::t('bot', 'Wrong email or username.'));
                }

                return $this->messenger->code();
            }
            $result = $this->conversationService->bindUser(
                $this->conversation,
                $this->text
            );
            if (false === $result) {
                return $this->messenger->reply(Yii::t(
                    'bot',
                    'Wrong code or your code is expired! Print "code" to get another verification code.'
                ));
            }

            return $this->messenger->start();
        }

        return $this->messenger->defaultReply();
    }

    /**
     * @return ReplyMessage
     */
    private function codeEventHandler()
    {
        if (UserBotConversation::STATUS_TRY_TO_CONNECT === $this->conversation->status &&
            null !== $this->conversation->user_id
        ) {
            $this->conversationService->getCode($this->conversation);

            return $this->messenger->code();
        }

        return $this->messenger->defaultReply();
    }

    /**
     * @return ReplyMessage
     */
    private function startEventHandler()
    {
        $result = $this->conversationService->bindBotProviderToConversation($this->conversation);
        if (false === $result) {
            return $this->messenger->reply(
                Yii::t(
                    'bot',
                    'You must add channel and turn on notifications before start. You have to go to bot settings at spylance.com'
                )
            );
        }

        return $this->messenger->reply(
            Yii::t(
                'bot',
                'Well done! You can say \'Stop\' in any time. Type “help” to watch possible commands.'
            )
        );
    }

    /**
     * @return ReplyMessage
     */
    private function stopEventHandler()
    {
        $result = $this->conversationService->unbindBotProviderFromConversation($this->conversation);
        if (false === $result) {
            return $this->messenger->reply(Yii::t('bot', 'You can not unbind what does not exist.'));
        }

        return $this->messenger->reply(
            Yii::t(
                'bot',
                'Well done! You can say \'Start\' in any time. Type “help” to watch possible commands.'
            )
        );
    }

    /**
     * @return ReplyMessage
     */
    private function localeEventHandler()
    {
        if (isset($this->locales()[$this->message])) {
            $this->conversationService->changeLocale(
                $this->conversation,
                $this->locales()[$this->message]
            );
            Yii::$app->language = $this->locales()[$this->message];
            if ($this->conversation->status === UserBotConversation::STATUS_NOT_ACTIVE) {
                return $this->messenger->welcome();
            }

            return $this->messenger->reply(Yii::t('bot', 'Language changed to - {language}.', [
                'language' => $this->message
            ]));
        }

        return $this->messenger->reply(Yii::t('bot', 'Undefined language!'));
    }

    /**
     * @return ReplyMessage
     */
    private function helpEventHandler()
    {
        $message = '';
        foreach ($this->commands() as $command => $description) {
            $message .= $command . ' - ' . $description . "\n\r";
        }

        return $this->messenger->reply($message);
    }

    /**
     * @return ReplyMessage
     */
    private function hiEventHandler()
    {
        return $this->messenger->reply(Yii::t('bot', 'Hi! I\'m Spylance bot.'));
    }

    /**
     * @return ReplyMessage
     */
    private function bookmarkEventHandler()
    {
        $projectId = (integer) $this->message;
        if (!empty($projectId)) {
            $result = $this->conversationService->addBookmark(
                $this->conversation->user->getId(),
                $projectId
            );
            if ($result) {
                return $this->messenger->reply(Yii::t('bot', 'Bookmark added.'));
            }

            return $this->messenger->reply(Yii::t('bot', 'Bookmark already added.'));
        }

        return $this->messenger->defaultReply();
    }

    /**
     * @return ReplyMessage
     */
    public function logoutEventHandler()
    {
        $this->conversationService->unbindUser($this->conversation);
        return $this->messenger->welcome();
    }

    /**
     * @return array
     */
    public function commands()
    {
        return [
            'start' => Yii::t('bot', 'Start project / vacancies notifications'),
            'stop' => Yii::t('bot', 'Stop project / vacancies notifications'),
            'set' => Yii::t('bot', 'Show settings lists'),
            'locale' => Yii::t('bot', 'You can change language if it need, example: \'locale ru\' or \'locale en\'.'),
            'logout' => Yii::t('bot', 'Logout'),
        ];
    }

    /**
     * @return array
     */
    private function locales()
    {
        return [
            'ru' => 'ru-RU',
            'en' => 'en-US'
        ];
    }
}
