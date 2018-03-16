<?php

namespace craftcom\developers;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craftcom\plugins\Plugin;
use yii\base\Behavior;

/**
 * The Developer behavior extends users with plugin developer-related features.
 *
 * @property FundsManager $fundsManager
 * @property User $owner
 * @property Plugin[] $plugins
 */
class Developer extends Behavior
{
    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $stripeAccessToken;

    /**
     * @var string|null
     */
    public $stripeAccount;

    /**
     * @var string|null
     */
    public $payPalEmail;

    /**
     * @var Plugin[]|null
     */
    private $_plugins;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Element::EVENT_AFTER_SAVE => [$this, 'afterSave'],
        ];
    }

    /**
     * @return string
     */
    public function getDeveloperName(): string
    {
        return $this->owner->getFieldValue('developerName') ?: $this->owner->getName();
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins(): array
    {
        if ($this->_plugins !== null) {
            return $this->_plugins;
        }

        return $this->_plugins = Plugin::find()
            ->developerId($this->owner->id)
            ->status(null)
            ->all();
    }

    /**
     * @return FundsManager
     */
    public function getFundsManager(): FundsManager
    {
        return new FundsManager($this->owner);
    }

    /**
     * Handles post-user-save stuff
     */
    public function afterSave()
    {
        $isDeveloper = $this->owner->isInGroup('developers');
        $request = Craft::$app->getRequest();
        $currentUser = Craft::$app->getUser()->getIdentity();

        // If it's a front-end site POST request and they're not currently a developer, check to see if they've opted into developer features.
        if (
            $currentUser &&
            $currentUser->id == $this->owner->id &&
            $request->getIsSiteRequest() &&
            $request->getIsPost() &&
            $request->getBodyParam('fields.enablePluginDeveloperFeatures') &&
            !$isDeveloper
        ) {
            // Get any existing group IDs.
            $userGroupsService = Craft::$app->getUserGroups();
            $existingGroups = $userGroupsService->getGroupsByUserId($currentUser->id);
            $groupIds = [];

            foreach ($existingGroups as $existingGroup) {
                $groupIds[] = $existingGroup->id;
            }

            // Add the developer group.
            $groupIds[] = $userGroupsService->getGroupByHandle('developers')->id;

            Craft::$app->getUsers()->assignUserToGroups($currentUser->id, $groupIds);
            $isDeveloper = true;
        }

        $db = Craft::$app->getDb();

        if ($isDeveloper && $db->tableExists('craftcom_developers')) {
            $db->createCommand()
                ->upsert('craftcom_developers', [
                    'id' => $this->owner->id,
                ], [
                    'country' => $this->owner->country,
                    'stripeAccessToken' => $this->owner->stripeAccessToken,
                    'stripeAccount' => $this->owner->stripeAccount,
                    'payPalEmail' => $this->owner->payPalEmail,
                ], [], false)
                ->execute();
        }
    }
}
