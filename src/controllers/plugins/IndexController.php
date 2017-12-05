<?php

namespace craftcom\controllers\plugins;

use Craft;
use craft\web\Controller;
use craftcom\plugins\Plugin;

/**
 * Class IndexController
 *
 * @package craftcom\controllers\plugins
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
        $criteria = [];
        $query = Plugin::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        $plugins = $query->all();

        return $this->renderTemplate('plugins/index', [
            'plugins' => $plugins,
        ]);
    }
}
