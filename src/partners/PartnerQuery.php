<?php

namespace craftnet\partners;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

/**
 * @method Partner[]|array all($db = null)
 * @method Partner|array|null one($db = null)
 * @method Partner|array|null nth(int $n, Connection $db = null)
 */
class PartnerQuery extends ElementQuery
{
    /**
     * @var string|string[]|null Id of the managing user
     */
    public $ownerId;

    /**
     * @var int|int[]|null
     */
    public $agencySize;

    /**
     * @var bool
     */
    public $isCraftVerified;

    /**
     * @var bool
     */
    public $isCommerceVerified;

    /**
     * @var bool
     */
    public $isEnterpriseVerified;

    /**
     * @var string
     */
    public $region;

    /**
     * Sets the [[ownerId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function ownerId($value)
    {
        $this->ownerId = $value;
        return $this;
    }

    /**
     * Sets the [[businessName]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function businessName($value)
    {
        $this->businessName = $value;
        return $this;
    }

    public function region($value)
    {
        $this->region = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('craftnet_partners');

        $this->query->select([
            'craftnet_partners.ownerId',
            'craftnet_partners.businessName',
            'craftnet_partners.primaryContactName',
            'craftnet_partners.primaryContactEmail',
            'craftnet_partners.primaryContactPhone',
            'craftnet_partners.fullBio',
            'craftnet_partners.shortBio',
            'craftnet_partners.agencySize',
            'craftnet_partners.hasFullTimeDev',
            'craftnet_partners.isCraftVerified',
            'craftnet_partners.isCommerceVerified',
            'craftnet_partners.isEnterpriseVerified',
            'craftnet_partners.verificationStartDate',
            'craftnet_partners.isRegisteredBusiness',
            'craftnet_partners.region',
            'craftnet_partners.expertise',
        ]);

        $andColumns = [
            'ownerId',
            'agencySize',
            'isCraftVerified',
            'isCommerceVerified',
            'isEnterpriseVerified',
            'region',
        ];

        foreach($andColumns as $column) {
            if (isset($this->{$column})) {
                $this->subQuery->andWhere(Db::parseParam('craftnet_partners.' . $column, $this->{$column}));
            }
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
//        if ($status === Plugin::STATUS_PENDING) {
//            return ['elements.enabled' => false, 'craftnet_plugins.pendingApproval' => true];
//        }

        return parent::statusCondition($status);
    }
}
