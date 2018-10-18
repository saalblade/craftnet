<?php

namespace craftnet\partners;

use craft\db\Query;
use craft\elements\Asset;
use yii\helpers\ArrayHelper;

class PartnerService
{
    private static $_instance;

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance= new self();
        }

        return self::$_instance;
    }

    /**
     * @param array|PartnerProject[] $projects
     */
    public function eagerLoadProjectScreenshots(&$projects)
    {
        if (!$projects) {
            return;
        }

        $screenshots = (new PartnerProjectScreenshotsQuery())
            ->project($projects)
            ->all();

        if (!$screenshots) {
            return;
        }

        $assetsByProjectId = [];

        foreach ($screenshots as $screenshot) {
            $projectId = $screenshot['projectId'];
            unset($screenshot['projectId']);
            $assetsByProjectId[$projectId][] = new Asset($screenshot);
        }

        foreach ($projects as &$project) {
            $project->screenshots = $assetsByProjectId[$project->id] ?: [];
        }
    }

    /**
     * Capabilities are represented by simple associative arrays rather than
     * by models. This accepts an array of numeric Capability IDs or valid
     * Capability arrays and returns an array of all capabilites as arrays
     * from a query:
     * ```
     * [
     *     ['id' => 1, 'title' => 'Commerce'],
     *     ['id' => 3, 'title' => 'Custom Development'],
     * ]
     * ```
     *
     * @param mixed $capabilities An array, but might be an empty string when from POST
     * @return array
     */
    public function normalizeCapabilities($capabilities)
    {
        $normalized = [];

        if (empty($capabilities)) {
            return $normalized;
        }

        $allCapabilities = (new PartnerCapabilitiesQuery())->asIndexedTitles()->all();

        $normalized = array_map(function($capability) use ($allCapabilities) {
            $id = is_numeric($capability) ? $capability : ($capability['id'] ?? null);

            if ($id === null || !array_key_exists((int) $id, $allCapabilities)) {
                return null;
            }

            return [
                'id' => (int) $id,
                'title' => $allCapabilities[$id]
            ];
        }, $capabilities);

        return array_filter($normalized);
    }

    /**
     * Accepts an array of location data arrays or PartnerLocation instances
     * and returns an array of PartnerLocation instances.
     *
     * @param array $locations
     * @param Partner $partner
     * @return PartnerLocation[]
     */
    public function normalizeLocations(array $locations, $partner): array
    {
        $locations = array_map(function($location) use ($partner) {
            if (!$location instanceof PartnerLocation) {
                $location = new PartnerLocation($location);
            }

            $location->partnerId = $partner->id;

            return $location;
        }, $locations);

        return $locations;
    }

    /**
     * Accepts an array of project data arrays or PartnerProject instances
     * and returns an array of PartnerProject instances.
     *
     * @param array $projects
     * @param Partner $partner
     * @param bool $eagerLoad
     * @return PartnerProject[]
     */
    public function normalizeProjects(array $projects, $partner, $eagerLoad = false): array
    {
        if (count($projects) === 0) {
            return $projects;
        }

        $ids = [];

        foreach ($projects as &$project) {
            if (!$project instanceof PartnerProject) {
                $project = new PartnerProject($project);
            }

            $project->partnerId = $partner->id;
            $ids[] = $project->id;
        }

        if ($eagerLoad) {
            $this->eagerLoadProjectScreenshots($projects);
        }

        return $projects;
    }


    /**
     * Partner volumes are created from migrations so the reliable way to get
     * folder ids is from the volume handles. Returns an array of root folder
     * ids indexed by volume handles.
     *
     * @return array
     */
    public function getVolumeFolderIds()
    {
        static $folderIds;

        if (!isset($folderIds)) {
            $rows = (new Query())
                ->select('f.id, v.handle')
                    ->from('volumes v')
                    ->rightJoin('volumefolders f', '[[v.id]] = [[f.volumeId]]')
                    ->where(['handle' => ['partnerDocuments', 'partnerImages']])
                    ->andWhere(['f.parentId' => null]) // root folders only
                    ->all();

            $folderIds = ArrayHelper::map($rows, 'handle', 'id');
        }

        return $folderIds;
    }

    /**
     * This volume is created by a content migration so we'll depend on the
     * volume handle to get the volumefolder id. There is only one folder
     * for this volume so a scalar query works.
     *
     * @return int Folder id or `false` if not found
     * @throws \Exception
     */
    public function getPartnerScreenshotsFolderId()
    {
        static $folderId;

        if (!isset($folderId)) {
            $folderId = (new Query())
                ->select('f.id')
                ->from('volumes v')
                ->rightJoin('volumefolders f', '[[v.id]] = [[f.volumeId]]')
                ->where(['handle' => 'partnerImages'])
                ->scalar();

            if (!$folderId) {
                throw new \Exception('Parter Screenshots volume folder does not exist. Need to run migrations?');
            }
        }

        return $folderId;
    }
}
