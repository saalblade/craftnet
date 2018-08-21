<?php

namespace craftnet\controllers\plugins;

use Craft;
use craft\web\Controller;
use craftnet\plugins\Plugin;

/**
 * Class IndexController
 */
class IndexController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $plugins = Plugin::find()
            ->with([
                'icon',
                'developer',
            ])
            ->all();

        return $this->renderTemplate('plugins/index', [
            'plugins' => $plugins,
        ]);
    }
}
