<?php

namespace craftnet\partners;

use Craft;
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
     * @var string|string[]|null Name of the business
     */
    public $businessName;

    /**
     * @var string|string[]|null Primary contact full name
     */
    public $primaryContactName;

    /**
     * @var string|string[]|null Primary contact email address
     */
    public $primaryContactEmail;

    /**
     * @var string|string[]|null Primary contact phone number
     */
    public $primaryContactPhone;

    /**
     * @var string|string[]|null Short description of the business
     */
    public $businessSummary;

    /**
     * @var int|int[]|null
     */
    public $agencySize;

    /**
     * @var bool
     */
    public $hasFullTimeDev;

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
     * @var bool
     */
    public $isRegisteredBusiness;

    /**
     * @var int Master Service Agreement PDF asset id
     */
    public $msaAssetId;

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

    /**
     * Sets the [[primaryContactName]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function primaryContactName($value)
    {
        $this->primaryContactName = $value;
        return $this;
    }

    /**
     * Sets the [[primaryContactEmail]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function primaryContactEmail($value)
    {
        $this->primaryContactEmail = $value;
        return $this;
    }

    /**
     * Sets the [[primaryContactPhone]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function primaryContactPhone($value)
    {
        $this->primaryContactPhone = $value;
        return $this;
    }

    /**
     * Sets the [[businessSummary]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function businessSummary($value)
    {
        $this->businessSummary = $value;
        return $this;
    }

    /**
     * Sets the [[msaAssetId]] property.
     *
     * @param int $value The Asset id
     *
     * @return static self reference
     */
    public function msaAssetId($value)
    {
        $this->msaAssetId = $value;

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
            'craftnet_partners.businessSummary',
            'craftnet_partners.agencySize',
            'craftnet_partners.hasFullTimeDev',
            'craftnet_partners.isCraftVerified',
            'craftnet_partners.isCommerceVerified',
            'craftnet_partners.isEnterpriseVerified',
            'craftnet_partners.isRegisteredBusiness',
            'craftnet_partners.msaAssetId',
        ]);

        $andColumns = [
            'ownerId',
            'businessName',
            'primaryContactName',
            'primaryContactEmail',
            'primaryContactPhone',
            'businessSummary',
            'isCraftVerified',
            'isCommerceVerified',
            'isEnterpriseVerified',
            'msaAssetId',
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
