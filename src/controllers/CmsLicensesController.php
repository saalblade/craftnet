<?php

namespace craftnet\controllers;

use Craft;
use craft\db\Paginator;
use craft\db\Query;
use craft\elements\User;
use craft\web\Controller;
use craft\web\twig\variables\Paginate;
use craftnet\cms\CmsLicense;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class CmsLicensesController extends Controller
{
    /**
     * Licenses index
     */
    public function actionIndex(): Response
    {
        $query = (new Query())
            ->from(['l' => 'craftnet_cmslicenses'])
            ->orderBy(['dateCreated' => SORT_DESC]);

        $request = Craft::$app->getRequest();

        if ($edition = $request->getQueryParam('edition', 'pro')) {
            $query->andWhere(['editionHandle' => $edition]);
        }

        if ($search = $request->getQueryParam('search')) {
            $query->andWhere([
                'or',
                ['like', 'domain', $search],
                ['like', 'key', $search . '%', false],
                ['like', 'email', $search]
            ]);
        }

        $filters = (array)$request->getQueryParam('filters', []);
        $indexedFilters = array_flip($filters);

        if (isset($indexedFilters['expired'])) {
            $query->andWhere(['expired' => true]);
        }

        if (isset($indexedFilters['domain'])) {
            $query->andWhere(['not', ['domain' => null]]);
        }

        $paginator = new Paginator($query, [
            'currentPage' => Craft::$app->request->getPageNum(),
        ]);

        $licenses = [];
        foreach ($paginator->getPageResults() as $result) {
            $licenses[] = new CmsLicense($result);
        }

        $owners = User::find()
            ->id(array_filter(ArrayHelper::getColumn($licenses, 'ownerId', false)))
            ->indexBy('id')
            ->all();

        return $this->renderTemplate('craftnet/cmslicenses/_index', [
            'edition' => $edition,
            'search' => $search,
            'showFilters' => (bool)$request->getQueryParam('show-filters'),
            'filters' => $filters,
            'licenses' => $licenses,
            'owners' => $owners,
            'pageInfo' => Paginate::create($paginator)
        ]);
    }
}
