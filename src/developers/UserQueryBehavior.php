<?php

namespace craftcom\developers;

use craft\elements\db\ElementQuery;
use craft\elements\db\UserQuery;
use yii\base\Behavior;

/**
 * @property UserQuery $owner
 */
class UserQueryBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ElementQuery::EVENT_AFTER_PREPARE => 'afterPrepare',
        ];
    }

    /**
     * Prepares the user query.
     */
    public function afterPrepare()
    {
        $this->owner->query->addSelect([
            'developers.country',
            'developers.stripeAccessToken',
            'developers.stripeAccount',
            'developers.payPalEmail',
        ]);

        $this->owner->query->leftJoin('craftcom_developers developers', '[[developers.id]] = [[users.id]]');
        $this->owner->subQuery->leftJoin('craftcom_developers developers', '[[developers.id]] = [[users.id]]');
    }
}
