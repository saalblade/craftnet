<?php

namespace craftnet\controllers\plugins;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use yii\web\Response;

/**
 * Class PluginStoreController
 */
class PluginStoreController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Returns developer details.
     *
     * @return Response
     */
    public function actionDeveloper()
    {
        $developerId = Craft::$app->getRequest()->getParam('developerId');
        $developer = Craft::$app->getApi()->getDeveloper($developerId);

        return $this->asJson($developer);
    }

    /**
     * Returns plugin details.
     *
     * @return Response
     */
    public function actionPluginDetails()
    {
        $pluginId = Craft::$app->getRequest()->getParam('pluginId');
        $pluginDetails = Craft::$app->getApi()->getPluginDetails($pluginId);

        return $this->asJson($pluginDetails);
    }

    /**
     * Returns the Plugin Store’s data.
     *
     * @return Response
     */
    public function actionPluginStoreData()
    {
        $pluginStoreData = Craft::$app->getApi()->getPluginStoreData();

        $seo = [];


        // Index
        $indexEntry = Entry::find()->site('plugins')->section('index')->one();

        $seo['index'] = [
            'title' => $indexEntry->title,
            'description' => $indexEntry->description,
        ];

        $pluginStoreData['seo'] = $seo;

        return $this->asJson($pluginStoreData);
    }
}
