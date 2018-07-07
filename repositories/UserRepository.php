<?php

namespace app\components\bot\repositories;

use dektrium\user\models\User;

/**
 * Class UserRepository
 * @package app\components\bot\repositories
 */
class UserRepository
{
    /**
     * @param User $user
     * @throws \InvalidArgumentException
     */
    public function save(User $user)
    {
        if ($user->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $user->update(false);
    }

    /**
     * @param string $identity
     * @return User|\yii\db\ActiveRecord
     */
    public function findByUsernameOrEmail($identity)
    {
        return User::find()
            ->andWhere(['is not', 'confirmed_at', null])
            ->andWhere(['blocked_at' => null])
            ->andWhere([
                'or',
                ['username' => $identity],
                ['email' => $identity]
            ])
            ->one();
    }
}
