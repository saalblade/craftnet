<?php

namespace craftnet\partners;

use Craft;
use craft\base\Element;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use DateTime;
use yii\db\Query;
use yii\helpers\ArrayHelper;
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
            'msaLink' => 'MSA Link',
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
     * @var string URL for master service agreement
     */
    public $msaLink;

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
                'msaLink',
                'capabilities',
                'locations',
            ],
            'required',
            'on' => self::SCENARIO_LIVE
        ];

        $rules[] = ['primaryContactEmail', 'email'];
        $rules[] = ['minimumBudget', 'number'];
        $rules[] = ['msaLink',  'url'];

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
            'msaLink',
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

        // Locations

        $db->createCommand()
            ->delete('craftnet_partnerlocations', ['partnerId' => $this->id])
            ->execute();

        foreach($this->_locations as $location) {
            $data = array_merge($location->attributes, ['partnerId' => $this->id]);
            unset($data['id']);

            $db->createCommand()
                ->insert(
                    'craftnet_partnerlocations',
                    $data
                )
                ->execute();
        }

        // Projects

        $savedIds = [];
        foreach($this->_projects as $project) {
            $project->partnerId = $this->id;

            if (!$project->id) {
                $data = $project->getAttributes(null, ['id', 'dateCreated', 'dateUpdated', 'uid']);
                $db->createCommand()
                    ->insert('craftnet_partnerprojects', $data)
                    ->execute();

                $savedIds[] = (int) $db->getLastInsertID();
            } else {
                $data = $project->getAttributes(null, ['dateCreated', 'dateUpdated', 'uid']);
                $db->createCommand()
                    ->update('craftnet_partnerprojects', $data, 'id=:id', [':id' => $data['id']], true)
                    ->execute();

                $savedIds[] = $project->id;
            }
        }

        if (count($savedIds) !== 0) {
            $db->createCommand()
                ->delete(
                    'craftnet_partnerprojects',
                    [
                        'AND',
                        ['partnerId' => $this->id],
                        ['not in', 'id', $savedIds],
                    ]
                )
                ->execute();
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
        $this->_capabilities = PartnersHelper::normalizeCapabilities($capabilities);
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
     * Sets the `location` attribute to PartnerLocationModels
     * from the given data array.
     * @param array $locations
     */
    public function setLocations(array $locations)
    {
        $this->_locations = PartnersHelper::normalizeLocations($locations);
    }

    /**
     * @param array $locations
     */
    public function setLocationsFromPost($locations = [])
    {
        $this->setLocations(PartnersHelper::normalizePostArray($locations));
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
            $result = (new PartnerProjectsQuery())
                ->partner($this->id)
                ->all();

            $this->setProjects($result);
        }

        return $this->_projects;
    }

    /**
     * Sets the `location` attribute to PartnerProjectModels
     * from the given data array.
     * @param array $projects
     */
    public function setProjects(array $projects)
    {
        $this->_projects = PartnersHelper::normalizeProjects($projects, $this);
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

    public static function eagerLoadingMap(array $sourceElements, string $handle)
    {
        switch ($handle) {
            case 'editions':
                $query = (new Query())
                    ->select(['id as source', 'partnerId as target'])
                    ->from(['craftnet_partnerlocations'])
                    ->where(['id' => ArrayHelper::getColumn($sourceElements, 'id')]);
                return ['elementType' => PluginEdition::class, 'map' => $query->all()];

            default:
                return parent::eagerLoadingMap($sourceElements, $handle);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->businessName;
    }
}
