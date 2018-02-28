<?php

namespace craftcom\cms;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

/**
 * @method CmsRenewal[]|array all($db = null)
 * @method CmsRenewal|array|null one($db = null)
 * @method CmsRenewal|array|null nth(int $n, Connection $db = null)
 */
class CmsRenewalQuery extends ElementQuery
{
    /**
     * @var int|int[]|null The CMS edition IDs(s) that the resulting renewals must be associated with.
     */
    public $editionId;

    /**
     * Sets the [[editionId]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function editionId($value)
    {
        $this->editionId = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('craftcom_cmsrenewals');

        $this->query->select([
            'craftcom_cmsrenewals.editionId',
        ]);

        if ($this->editionId) {
            $this->subQuery->andWhere(Db::parseParam('craftcom_cmsrenewals.editionId', $this->editionId));
        }

        return parent::beforePrepare();
    }
}
