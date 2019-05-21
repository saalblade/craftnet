<?php
/**
 * Front module for Craft CMS 3.x
 *
 * Front integration
 *
 * @link      https://craftcms.com
 * @copyright Copyright (c) 2019 Luke Holder
 */

namespace modules\frontmodule;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\i18n\PhpMessageSource;
use craft\web\UrlManager;
use craft\web\View;
use modules\frontmodule\assetbundles\frontmodule\FrontModuleAsset;
use yii\base\Event;
use yii\base\Module;

/**
 * Class FrontModule
 *
 * @author    Luke Holder
 * @package   FrontModule
 * @since     1
 *
 */
class FrontModule extends Module
{
    // Static Properties
    // =========================================================================

    /**
     * @var FrontModule
     */
    public static $instance;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/frontmodule', $this->getBasePath());
        $this->controllerNamespace = 'modules\frontmodule\controllers';

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$id]) && !isset($i18n->translations[$id . '*'])) {
            $i18n->translations[$id] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@modules/frontmodule/translations',
                'forceTranslation' => true,
                'allowOverrides' => true,
            ];
        }

        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

        // Set this as the global instance of this module class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                // need to add at the start of the routes array due to wildcard route to vue app at the end
                $event->rules = array_merge(['front' => 'front-module/front/index'], $event->rules);
                $event->rules = array_merge(['front/get-license-info' => 'front-module/front/get-license-info'], $event->rules);
            }
        );

        Craft::info(
            Craft::t(
                'front-module',
                '{name} module loaded',
                ['name' => 'Front']
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
}
