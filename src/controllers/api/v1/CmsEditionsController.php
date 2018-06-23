<?php

namespace craftnet\controllers\api\v1;

use craftnet\cms\CmsEdition;
use craftnet\controllers\api\BaseApiController;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Class CmsEditionsController
 */
class CmsEditionsController extends BaseApiController
{
    // Properties
    // =========================================================================

    public $defaultAction = 'get';

    // Public Methods
    // =========================================================================

    /**
     * Retrieves CMS editions.
     *
     * @return Response
     */
    public function actionGet(): Response
    {
        $editions = CmsEdition::find()
            ->orderBy('price')
            ->all();

        return $this->asJson([
            'editions' => ArrayHelper::toArray($editions),
        ]);
    }
}
