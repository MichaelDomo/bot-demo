<?php

namespace app\components\bot;

use yii\helpers\ArrayHelper;
use app\components\bot\repositories\UserConversationRepository;
use app\components\bot\services\ConversationService;
use app\components\bot\services\CommandAnalyzer;
use app\components\bot\entities\ReplyMessage;
use app\components\bot\services\Messenger;
use app\models\user\bot\UserBotConversation;

/**
 * Собирательный класс, который делает всё.
 * @package app\components\bot
 */
abstract class Bot
{
    protected $http;
    protected $auth;
    protected $reply;
    protected $replyUrl;
    protected $messenger;
    protected $requestData;
    protected $serviceUrl;
    protected $conversation;
    protected $commandAnalyzer;
    protected $conversationService;
    protected $conversationRepository;

    /**
     * Bot constructor.
     * @param string $client
     * @param string $secret
     * @param object $requestData
     * @param Messenger $messenger
     * @param CommandAnalyzer $commandAnalyzer
     * @param ConversationService $conversationService
     * @param UserConversationRepository $conversationRepository
     */
    public function __construct(
        $client,
        $secret,
        $requestData,
        Messenger $messenger,
        CommandAnalyzer $commandAnalyzer,
        ConversationService $conversationService,
        UserConversationRepository $conversationRepository
    ) {
        $this->http = new Http();
        $this->auth = new Auth($client, $secret, $this->http);
        $this->messenger = $messenger;
        $this->requestData = $requestData;
        $this->commandAnalyzer = $commandAnalyzer;
        $this->conversationService = $conversationService;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @param \app\models\FreelanceProjects[] $items
     */
    public function notifyEventHandler($items)
    {
        $this->messenger->setConversation($this->getConversation());
        $this->setReply($this->messenger->projects($items));
        $this->doRequest();
    }

    /**
     * Send message to user.
     * Update conversation if need it.
     */
    public function messageEventHandler()
    {
        if (null === $this->getConversation()) {
            $this->updateConversation();
        } else {
            $this->setReply($this->commandAnalyzer->analyze(
                $this->requestData->text,
                $this->getConversation()
            ));
        }
        $this->doRequest();
    }

    /**
     * @param ReplyMessage $reply
     */
    protected function setReply(ReplyMessage $reply)
    {
        $this->reply = ArrayHelper::toArray($reply);
    }

    /**
     * @param UserBotConversation $conversation
     */
    public function setConversation(UserBotConversation $conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * Get current conversation.
     * @return UserBotConversation|null
     */
    protected function getConversation()
    {
        if (null === $this->conversation) {
            $this->conversation = $this->conversationRepository
                ->findByConversationAndRecipient(
                    $this->requestData->conversation->id,
                    $this->requestData->recipient->id
                );
        }

        return $this->conversation;
    }

    /**
     * Get reply Url from conversation.
     * @return string
     */
    protected function getReplyUrl()
    {
        if (null === $this->replyUrl) {
            $this->replyUrl = $this->serviceUrl . '/v3/conversations/' .
                $this->getConversation()->conversation_id . '/activities';
        }

        return $this->replyUrl;
    }

    /**
     * @throws \Exception
     */
    protected function doRequest()
    {
        try {
            $this->http->addAuthHeader(
                $this->getConversation()->token['token_type'],
                $this->getConversation()->token['access_token']
            );
            $result = $this->http->request($this->getReplyUrl(), $this->reply, true);
            if ('401' === $result->statusCode || '403' === $result->statusCode) {
                $token = $this->conversationService->updateToken(
                    $this->getConversation(),
                    $this->auth->getToken()
                );
                $this->http->clearHeaders();
                $this->http->addAuthHeader($token['token_type'], $token['access_token']);
                $this->http->request($this->getReplyUrl(), $this->reply, true);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update conversation
     */
    protected function updateConversation()
    {
        if (null === ($conversation = $this->getConversation())) {
            $conversation = $this->conversationService
                ->create(
                    $this->requestData->conversation->id,
                    $this->requestData->recipient->id,
                    $this->auth->getToken(),
                    $this->requestData
                );
            $this->messenger->setConversation($conversation);
            if (null === $conversation->locale) {
                $this->setReply($this->messenger->locale());
            } else {
                $this->setReply($this->messenger->welcome());
            }
        } else {
            $conversation = $this->conversationService
                ->update(
                    $conversation,
                    $this->requestData->conversation->id,
                    $this->requestData->recipient->id,
                    $this->auth->getToken(),
                    $this->requestData
                );
            $this->messenger->setConversation($conversation);
            $this->setReply($this->messenger->back());
        }
    }
}
