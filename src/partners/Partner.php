<?php

namespace craftnet\partners;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\elements\actions\SetStatus;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use yii\helpers\Inflector;

/**
 * Class Partner
 *
 * @property $slug string
 * @package craftnet\partners
 */
class Partner extends Element
{
    // Constants
    // =========================================================================

    const STATUS_DRAFTING = 'statusDrafting';
    const STATUS_PENDING_APPROVAL = 'statusPendingApproval';
    const STATUS_APPROVED = 'statusApproved';
    const STATUS_REJECTED = 'statusRejected';

    // Static
    // =========================================================================

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return 'Partner';
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ENABLED => Craft::t('app', 'Enabled'),
            self::STATUS_DISABLED => Craft::t('app', 'Disabled'),
            self::STATUS_ARCHIVED => Craft::t('app', 'Archived'),
        ];
    }

    /**
     * @return PartnerQuery
     */
    public static function find(): ElementQueryInterface
    {
        $partnerQuery = new PartnerQuery(static::class);
        return $partnerQuery;
    }

    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => 'All Partners',
                'criteria' => ['status' => null],
            ]
        ];

        return $sources;
    }

    protected static function defineActions(string $source = null): array
    {
        return [
            SetStatus::class,
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return [
            'businessName',
            'primaryContactName',
            'primaryContactEmail',
            'primaryContactPhone',
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'businessName' => 'Business Name',
            'ownerId' => 'Owner',
            'primaryContactName' => 'Primary Name',
            'primaryContactEmail' => 'Primary Email',
            'primaryContactPhone' => 'Primary Phone',
            'minimumBudget' => 'Minimum Budget',
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'businessName',
            'ownerId',
        ];
    }

    // Properties
    // =========================================================================

    /**
     * @var bool Whether the element is enabled
     */
    public $enabled = false;

    /**
     * @var int The owner’s user ID
     */
    public $ownerId;

    /**
     * @var bool Partner profile is pending approval
     */
    public $pendingApproval;

    /**
     * @var string
     */
    public $primaryContactName;

    /**
     * @var string
     */
    public $primaryContactEmail;

    /**
     * @var string
     */
    public $primaryContactPhone;

    /**
     * @var string
     */
    public $businessSummary;

    /**
     * @var int Minimum budget in USD
     */
    public $minimumBudget;

    /**
     * @var int Master Service Agreement PDF asset id
     */
    public $msaAssetId;

    /**
     * @var array
     */
    private $_capabilities = null;

    /**
     * @var array
     */
    private $_locations = null;

    /**
     * @var array
     */
    private $_projects = null;

    /**
     * @var Asset Master Service Agreement asset model
     */
    public $_msa;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [
            [
                'businessName',
                'ownerId'
            ],
            'required',
        ];

        $rules[] = [
            [
                'primaryContactName',
                'primaryContactEmail',
                'primaryContactPhone',
                'businessSummary',
                'minimumBudget',
                'msaAssetId',
                'capabilities',
                'locations',
            ],
            'required',
            'on' => self::SCENARIO_LIVE
        ];

        $rules[] = ['primaryContactEmail', 'email'];
        $rules[] = ['minimumBudget', 'number'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        $partnerData = $this->getAttributes([
            'ownerId',
            'businessName',
            'primaryContactName',
            'primaryContactEmail',
            'primaryContactPhone',
            'businessSummary',
            'minimumBudget',
            'msaAssetId',
        ]);

        if ($isNew) {
            $partnerData['id'] = $this->id;
        }

        $db = Craft::$app->getDb();

        if ($isNew) {
            $db->createCommand()
                ->insert('craftnet_partners', $partnerData)
                ->execute();
        } else {
            $db->createCommand()
                ->update('craftnet_partners', $partnerData, ['id' => $this->id])
                ->execute();
        }

        // Capabilities

        $db->createCommand()
            ->delete('craftnet_partners_partnercapabilities', ['partnerId' => $this->id])
            ->execute();

        if (count($this->_capabilities) > 0) {
            $partnerId = $this->id;

            $rows = array_map(function($capability) use ($partnerId) {
                return [$partnerId, $capability['id']];
            }, $this->_capabilities);

            $db->createCommand()
                ->batchInsert(
                    'craftnet_partners_partnercapabilities',
                    ['partnerId', 'partnercapabilitiesId'],
                    $rows,
                    false
                )
                ->execute();
        }

        $this->_saveOneToManyRelations($this->_locations, 'craftnet_partnerlocations');
        $this->_saveOneToManyRelations($this->_projects, 'craftnet_partnerprojects', true, ['screenshots']);

        foreach ($this->_projects as $project) {
            $this->_saveProjectScreenshots($project);
        }
    }

    /**
     * Validate locations.
     * @inheritdoc
     */
    public function afterValidate()
    {
        foreach ($this->_locations as $location) {
            // Same scenarios on Partner and ParnerLocationModel:
            // SCENARIO_DEFAULT, SCENARIO_LIVE
            $location->setScenario($this->getScenario());
            $isValid = $location->validate();

            if (!$isValid && !$this->hasErrors('locations')) {
                $this->addError('locations', 'Please fix location errors');
            }
        }

        foreach ($this->_projects as $project) {
            // Same scenarios on Partner and ParnerLocationModel:
            // SCENARIO_DEFAULT, SCENARIO_LIVE
            $project->setScenario($this->getScenario());
            $isValid = $project->validate();

            if (!$isValid && !$this->hasErrors('projects')) {
                $this->addError('projects', 'Please fix projects errors');
            }
        }

        return parent::afterValidate();
    }

    /**
     * @return null|string
     */
    public function getCpEditUrl()
    {
        $slug = Inflector::slug($this->businessName);

        return UrlHelper::cpUrl("partners/{$this->id}-{$slug}");
    }

    /**
     * Capabilities related to this Partner as {id: title}
     * ```
     * [
     *   1 => 'Commerce',
     *   4 => 'Contract Work',
     * ]
     * ```
     *
     * @return array
     */
    public function getCapabilities()
    {
        // New Partner instance
        if ($this->id === null) {
            $this->_capabilities = [];
        }

        // Existing Partner instance without capabilities set yet
        if ($this->_capabilities === null) {
            $this->_capabilities = (new PartnerCapabilitiesQuery())
                ->partner($this)
                ->asIndexedTitles()
                ->all();
        }

        return $this->_capabilities;
    }

    /**
     * @param array $capabilities An array of ids, or associative array of `id => title`
     */
    public function setCapabilities($capabilities)
    {
        $this->_capabilities = PartnerService::getInstance()->normalizeCapabilities($capabilities);
    }

    /**
     * @return array
     */
    public function getLocations()
    {
        // New Partner instance
        if ($this->id === null) {
            $this->_locations = [];
        }

        // Existing Partner instance without locations set yet
        if ($this->_locations === null) {
            $result = (new PartnerLocationsQuery())
                ->partner($this->id)
                ->all();

            $this->setLocations($result);
        }

        return $this->_locations;
    }

    /**
     * Sets the `locations` attribute to a list of PartnerLocation
     * instances given an array of models or data arrays suitable for
     * PartnerLocation instantiation.
     * @param array $locations
     */
    public function setLocations(array $locations)
    {
        $this->_locations = PartnerService::getInstance()->normalizeLocations($locations, $this);
    }

    /**
     * @param array $locations
     */
    public function setLocationsFromPost($locations = [])
    {
        foreach ($locations as $id => &$location) {
            if (substr($id, 0, 3) !== 'new') {
                $location['id'] = $id;
            }
        }

        $this->setLocations($locations);
    }

    /**
     * Not worried about eager loading atm because this is only for
     * Control Panel and Craft ID dashboard.
     * @return Asset|null
     */
    public function getMsa()
    {
        if ($this->msaAssetId) {
            return Asset::findOne($this->msaAssetId);
        }

        return null;
    }

    /**
     * @param array|string $ids
     */
    public function setMsaAssetIdFromPost($ids)
    {
        $this->msaAssetId = $ids ? $ids[0] : null;
    }

    /**
     * @return array
     */
    public function getProjects()
    {
        // New Partner instance
        if ($this->id === null) {
            $this->_projects = [];
        }

        // Existing Partner instance without projects set yet
        if ($this->_projects === null) {
            $projects = (new PartnerProjectsQuery())
                ->partner($this->id)
                ->all();

            $this->setProjects($projects, true);
        }

        return $this->_projects;
    }

    /**
     * Sets the `projects` attribute to a list of PartnerProject
     * instances given an array of models or data arrays suitable for
     * PartnerProject instantiation.
     * @param array $projects
     * @param bool $eagerLoad
     */
    public function setProjects(array $projects, $eagerLoad = false)
    {
        $this->_projects = PartnerService::getInstance()->normalizeProjects($projects, $this, $eagerLoad);
    }

    /**
     * @param array $projects
     */
    public function setProjectsFromPost($projects = [])
    {
        foreach ($projects as $id => &$project) {
            if (substr($id, 0, 3) !== 'new') {
                $project['id'] = $id;
            }
        }

        $this->setProjects($projects);
    }

    /**
     * @return \craft\elements\User|null
     */
    public function getOwner()
    {
        return Craft::$app->getUsers()->getUserById($this->ownerId);
    }

    /**
     * Generic method to save one-to-many relations like projects and locations.
     * @param Model[] $models
     * @param string $table
     * @param bool $prune Prune rows not belonging to `$models`
     * @param array $without Attributes to exclude
     */
    private function _saveOneToManyRelations($models, $table, $prune = true, $without = [])
    {
        $db = Craft::$app->getDb();
        $savedIds = [];
        $without = array_unique(array_merge($without, ['dateCreated', 'dateUpdated', 'uid']));

        foreach($models as &$model) {
            $model->partnerId = $this->id;

            if (!$model->id) {
                $data = $model->getAttributes(null, array_merge($without, ['id']));
                $db->createCommand()
                    ->insert($table, $data)
                    ->execute();

                $model->id = (int) $db->getLastInsertID();
                $savedIds[] = $model->id;
            } else {
                $data = $model->getAttributes(null, $without);
                $db->createCommand()
                    ->update($table, $data, 'id=:id', [':id' => $data['id']], true)
                    ->execute();

                $savedIds[] = $model->id;
            }
        }

        if ($prune) {
            $condition = ['AND', ['partnerId' => $this->id]];

            if (count($savedIds) !== 0) {
                $condition[] = ['not in', 'id', $savedIds];
            }

            $db->createCommand()
                ->delete($table, $condition)
                ->execute();
        }
    }

    /**
     * @param PartnerProject $project
     * @throws \yii\db\Exception
     */
    private function _saveProjectScreenshots($project)
    {
        $db = Craft::$app->getDb();
        $table = 'craftnet_partnerprojectscreenshots';

        $db->createCommand()
            ->delete($table, ['projectId' => $project->id])
            ->execute();

        if (count($project->screenshots) === 0) {
            return;
        }

        $columns = ['projectId', 'assetId', 'sortOrder'];
        $rows = [];

        foreach ($project->getScreenshotIds() as $key => $assetId) {
            $rows[] = [$project->id, $assetId, $key];
        }

        $db->createCommand()
            ->batchInsert($table, $columns, $rows)
            ->execute();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->businessName;
    }
}
