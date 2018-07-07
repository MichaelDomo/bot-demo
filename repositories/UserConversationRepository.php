<?php

namespace app\components\bot\repositories;

use app\models\user\bot\UserBotConversation;
use app\models\user\bot\UserBotProvider;

/**
 * Class UserConversationRepository
 * @package app\components\bot\repositories
 */
class UserConversationRepository
{
    /**
     * @param UserBotConversation $conversation
     * @throws \InvalidArgumentException
     */
    public function add(UserBotConversation $conversation)
    {
        if (!$conversation->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $conversation->insert(false);
    }

    /**
     * @param UserBotConversation $conversation
     * @throws \InvalidArgumentException
     */
    public function save(UserBotConversation $conversation)
    {
        if ($conversation->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $conversation->update(false);
    }

    /**
     * Сложная выборка, но это для того чтобы не ныдо было фильтровать в коде.
     * @return \yii\db\ActiveRecord[]|null|UserBotConversation[]
     */
    public function findAllActive()
    {
        return UserBotConversation::find()
            ->joinWith([
                'user',
                'user.currentSettings',
                'provider',
                'provider.bot',
                'provider.settings'
            ])
            ->andWhere(['not', ['user_bot_conversation.user_id' => null]])
            ->andWhere(['not', ['user_bot_conversation.user_bot_provider_id' => null]])
            ->andWhere(['user_bot_conversation.status' => UserBotConversation::STATUS_ACTIVE])
            ->andWhere(['user_bot_provider.status' => UserBotProvider::STATUS_ENABLED])
            ->andWhere(['<', '(user_bot_provider.updated_at + user_bot_provider.period)', time()])
            ->all();
    }

    /**
     * @param string $conversationId
     * @param string $recipientId
     * @return array|null|UserBotConversation
     */
    public function findByConversationAndRecipient($conversationId, $recipientId)
    {
        return UserBotConversation::find()
            ->andWhere([
                'conversation_id' => $conversationId,
                'recipient_id' => $recipientId
            ])
            ->one();
    }
}
