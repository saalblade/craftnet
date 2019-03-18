<?php

namespace craftnet\controllers\id;

use Craft;
use craft\elements\Category;
use yii\web\Response;

/**
 * Class CraftIdController
 */
class CraftIdController extends BaseController
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Get Craft ID data.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $this->requirePostRequest();

        return $this->asJson([
            'categories' => $this->getPluginCategories(),
            'countries' => Craft::$app->getApi()->getCountries(),
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @return array
     */
    private function getPluginCategories(): array
    {
        $ret = [];

        $categories = Category::find()
            ->group('pluginCategories')
            ->all();

        foreach ($categories as $category) {
            $ret[] = [
                'id' => $category->id,
                'title' => $category->title,
            ];
        }

        return $ret;
    }
}
