<?php

namespace app\components\bot\repositories;

use dektrium\user\models\Code;

/**
 * Class UserCodeRepository
 * @package app\components\bot\repositories
 */
class UserCodeRepository
{
    /**
     * @param Code $userCode
     * @throws \InvalidArgumentException
     */
    public function add(Code $userCode)
    {
        if (!$userCode->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $userCode->insert(false);
    }

    /**
     * @param Code $userCode
     * @throws \InvalidArgumentException
     */
    public function remove(Code $userCode)
    {
        if ($userCode->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $userCode->delete();
    }

    /**
     * @return int
     */
    public function removeExpiredCodes()
    {
        return Code::deleteAll(['<=', 'expired_at', time()]);
    }

    /**
     * @param integer $code
     * @return null|\yii\db\ActiveRecord|Code
     */
    public function findByCode($code)
    {
        return Code::find()
            ->andWhere(['>', 'expired_at', time()])
            ->andWhere(['code' => $code])
            ->one();
    }

    /**
     * @param integer $userId
     * @return null|\yii\db\ActiveRecord|Code
     */
    public function findByUserId($userId)
    {
        return Code::find()
            ->andWhere(['>', 'expired_at', time()])
            ->andWhere(['user_id' => $userId])
            ->one();
    }
}
