<?php

namespace craftnet\partners;


use craft\db\Query;
use Exception;

class PartnerProjectScreenshotsQuery extends Query
{
    private $_projectIds;

    public function project($projects): Query
    {
        $projects = (array)$projects;
        $this->_projectIds = [];

        foreach ($projects as $project) {
            $this->_projectIds[] = is_numeric($project) ? $project : $project->id;
        }

        return $this;
    }

    /**
     * @param \yii\db\QueryBuilder $builder
     * @return $this|Query
     */
    public function prepare($builder)
    {
        if (!isset($this->_projectIds)) {
            throw new Exception('Project id(s) must be set before executing PartnerProjectScreenshotsQuery');
        }

        $this->select('a.*, projectId')
            ->from('assets a')
            ->leftJoin('craftnet_partnerprojectscreenshots pps', '[[pps.assetId]] = a.id')
            ->where(['in', 'pps.projectId', $this->_projectIds])
            ->orderBy('pps.sortOrder');

        return $this;
    }

}
