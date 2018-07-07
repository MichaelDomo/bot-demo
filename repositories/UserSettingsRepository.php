<?php

namespace app\components\bot\repositories;

use app\models\user\UserSettings;

/**
 * Class UserSettingsRepository
 * @package app\components\bot\repositories
 */
class UserSettingsRepository
{
    /**
     * @param integer $id
     * @param integer $userId
     * @return bool
     */
    public function existsByIdAndUserSettings($id, $userId)
    {
        return UserSettings::find()
            ->andWhere([
                'id' => $id,
                'user_id' => $userId
            ])
            ->exists();
    }
}
