<?php

namespace app\components\bot\repositories;

use app\models\user\bot\UserBotSettings;

/**
 * Class UserBotSettings
 * @package app\components\bot\repositories
 */
class UserBotSettingsRepository
{
    /**
     * @param UserBotSettings $userBotSettings
     * @throws \InvalidArgumentException
     */
    public function save(UserBotSettings $userBotSettings)
    {
        if ($userBotSettings->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $userBotSettings->update(false);
    }
}
