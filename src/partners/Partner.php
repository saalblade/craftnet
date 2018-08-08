<?php

namespace craftnet\partners;

use Craft;
use craft\base\Element;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
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
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

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
    private $_capabilities;

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
                'msaLink'
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
            'id',
            'ownerId',
            'businessName',
            'primaryContactName',
            'primaryContactEmail',
            'primaryContactPhone',
            'businessSummary',
            'minimumBudget',
            'msaLink',
        ]);

        $db = Craft::$app->getDb();

        if ($isNew) {
            $db->createCommand()
                ->insert('craftnet_partners', $partnerData)
                ->execute();
        } else {
            $db->createCommand()
                ->update('craftnet_partners', $partnerData)
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
    }

    /**
     * @return null|string
     */
    public function getCpEditUrl()
    {
        $slug = Inflector::slug($this->businessName);

        return "partners/{$this->id}-{$slug}";
    }

    /**
     * Capabilities are a query result array for capabilities
     * related to this Partner. The `title` value can be missing
     * when populated from only ids during a post request.
     *
     * ```
     * [
     *     ['id' => 1, 'title' => 'Commerce']
     *     ...
     * ]
     * ```
     * @return array
     */
    public function getCapabilities()
    {
        if (!isset($this->_capabilities)) {
            $this->_capabilities = (new PartnerCapabilitiesQuery())
                ->partner($this)
                ->asIndexedTitles()
                ->all();
        }

        return $this->_capabilities;
    }

    /**
     * @param array $ids A list of capability ids.
     */
    public function setCapabilitiesByIds($ids)
    {
        $this->_capabilities = [];

        $allCapabilities = (new PartnerCapabilitiesQuery())->asIndexedTitles()->all();

        foreach ($ids as $id) {
            if (!array_key_exists($id, $allCapabilities)) {
                continue;
            }

            $this->_capabilities[] = [
                'id' => (int) $id,
                'title' => $allCapabilities[$id]
            ];
        }
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
