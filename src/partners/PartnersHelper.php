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
     * @return PartnerLocationModel[]
     */
    public static function normalizeLocations(array $locations): array
    {
        $locations = array_map(function($location) {
            return $location instanceof PartnerLocationModel ? $location : new PartnerLocationModel($location);
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

    /**
     * Example: POST `locations` look like:
     * ```
     * [
     *     'title' => ['first', 'second', 'third'],
     *     'addressLine1' => ['street one', 'street two', 'street three'],
     *     ...
     *  ]
     * ```
     * This normalizes the array to:
     * ```
     * [
     *     0 => ['title' => 'first', 'addressLine1' => 'street one']
     *     1 => ['title' => 'second', 'addressLine1' => 'street two']
     * ]
     * ```
     * @param array $postArray
     * @return array
     */
    public static function normalizePostArray($postArray = [])
    {
        $normalized = [];

        foreach ($postArray as $field => $values) {
            $i = -1;
            foreach ($values as $value) {
                $normalized[++$i][$field] = $value;
            }
        }

        return $normalized;
    }
}
