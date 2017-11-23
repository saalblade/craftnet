<?php

namespace craftcom\behaviors;

use craft\elements\User;
use craftcom\plugins\Plugin;
use yii\base\Behavior;

/**
 * The Developer behavior extends users with plugin developer-related features.
 *
 * @property Plugin[] $plugins
 * @property User $owner
 */
class Developer extends Behavior
{
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
}
