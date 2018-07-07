<?php

namespace app\components\bot\repositories;

use app\models\user\UserBookmark;

/**
 * Class UserBookmarkRepository
 * @package app\components\bot\repositories
 */
class UserBookmarkRepository
{
    /**
     * @param UserBookmark $userBookmark
     * @throws \InvalidArgumentException
     */
    public function add(UserBookmark $userBookmark)
    {
        if (!$userBookmark->getIsNewRecord()) {
            throw new \InvalidArgumentException('Model not exists');
        }
        $userBookmark->insert(false);
    }

    /**
     * @param $userId
     * @param $projectId
     * @return UserBookmark|null
     */
    public function findByUserIdAndProjectId($userId, $projectId)
    {
        return UserBookmark::findOne([
            'user_id' => $userId,
            'project_id' => $projectId
        ]);
    }

    /**
     * @param integer $userId
     * @param integer|null $limit
     * @return UserBookmark[]
     */
    public function findByUserId($userId, $limit = null)
    {
        $query = UserBookmark::find()
            ->joinWith(['project'])
            ->andWhere([
                'user_id' => $userId
            ])
            ->orderBy(['id' => SORT_DESC]);
        if (null !== $limit) {
            $query->limit($limit);
        }

        return $query->all();
    }
}
