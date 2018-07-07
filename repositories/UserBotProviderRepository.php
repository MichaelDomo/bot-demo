<?php

namespace app\components\bot\repositories;

use app\models\user\bot\UserBotProvider;

/**
 * Class UserBotProviderRepository
 * @package app\components\bot\repositories
 */
class UserBotProviderRepository
{
    /**
     * @param UserBotProvider $userBotProvider
     * @throws \InvalidArgumentException
     */
    public function save(UserBotProvider $userBotProvider)
    {
        if ($userBotProvider->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $userBotProvider->update(false);
    }

    /**
     * @param UserBotProvider $userBotProvider
     * @param string $attribute
     */
    public function touch(UserBotProvider $userBotProvider, $attribute)
    {
        if ($userBotProvider->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $userBotProvider->touch($attribute);
    }

    /**
     * @param integer $userId
     * @param string $channelId
     * @return UserBotProvider|null|\yii\db\ActiveRecord
     */
    public function findByUserIdAndChannelId($userId, $channelId)
    {
        return UserBotProvider::find()
            ->joinWith(['settings', 'bot'])
            ->andWhere([
                'user_bot_settings.user_id' => $userId,
                'bot_provider.channel_id' => $channelId
            ])
            ->one();
    }
}
