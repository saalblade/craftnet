<?php

namespace craftcom\developers;

use craft\elements\User;
use craftcom\plugins\Plugin;
use yii\base\Behavior;
use yii\base\InvalidArgumentException;

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
     * @return string
     */
    public function getDeveloperName(): string
    {
        return $this->owner->developerName ?: $this->owner->getName();
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
}
