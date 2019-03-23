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
            'countries' => Craft::$app->getApi()->getCountries(),
        ]);
    }
}
