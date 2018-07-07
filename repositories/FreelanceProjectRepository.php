<?php

namespace app\components\bot\repositories;

use app\models\FreelanceProjects;

/**
 * Class FreelanceProjectRepository
 * @package app\components\bot\repositories
 */
class FreelanceProjectRepository
{
    /**
     * @return mixed
     */
    public function findLastId()
    {
        return FreelanceProjects::find()
            ->max('id');
    }
}
