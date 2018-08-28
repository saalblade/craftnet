<?php

namespace craftnet\partners;


use craft\base\Component;

class PartnersHelper extends Component
{
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
    public static function normalizeCapabilities($capabilities)
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
     * Accepts an array of location data arrays or PartnerLocationModel instances
     * and returns an array of PartnerLocationModel instances.
     *
     * @param array $locations
     * @param Partner $partner
     * @return PartnerLocationModel[]
     */
    public static function normalizeLocations(array $locations, $partner): array
    {
        $locations = array_map(function($location) use ($partner) {
            if (!$location instanceof PartnerLocationModel) {
                $location = new PartnerLocationModel($location);
            }

            $location->partnerId = $partner->id;

            return $location;
        }, $locations);

        return $locations;
    }

    /**
     * Accepts an array of project data arrays or PartnerProjectModel instances
     * and returns an array of PartnerProjectModel instances.
     *
     * @param array $projects
     * @param Partner $partner
     * @return PartnerProjectModel[]
     */
    public static function normalizeProjects(array $projects, $partner): array
    {
        $projects = array_map(function($project) use ($partner) {
            if (!$project instanceof PartnerProjectModel) {
                $project = new PartnerProjectModel($project);
            }

            $project->partnerId = $partner->id;

            return $project;
        }, $projects);

        return $projects;
    }
}
