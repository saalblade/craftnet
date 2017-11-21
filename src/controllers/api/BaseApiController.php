<?php

namespace craftcom\controllers\api;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\Module;
use craftcom\plugins\Plugin;
use JsonSchema\Validator;
use stdClass;
use yii\web\BadRequestHttpException;

/**
 * Class BaseController
 *
 * @package craftcom\controllers\api
 *
 * @property Module $module
 */
abstract class BaseApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * Returns the JSON-decoded request body.
     *
     * @param string|null $schema JSON schema to validate the body with (optional)
     *
     * @return stdClass
     * @throws BadRequestHttpException if the data doesn't validate
     */
    protected function getPayload(string $schema = null): stdClass
    {
        $body = Json::decode(Craft::$app->getRequest()->getRawBody(), false);

        if ($schema !== null) {
            $validator = new Validator();
            $path = Craft::getAlias("@root/json-schemas/{$schema}.json");
            $validator->validate($body, (object)['$ref' => 'file://'.$path]);

            if (!$validator->isValid()) {
                throw new BadRequestHttpException('Invalid request body.');
            }
        }

        return $body;
    }

    /**
     * @param Plugin $plugin
     * @param bool   $snippetOnly
     *
     * @return array
     */
    protected function transformPlugin(Plugin $plugin, $snippetOnly = false): array
    {
        // Developer name
        $developerName = $plugin->getDeveloper()->developerName;

        if (empty($developerName)) {
            $developerName = $plugin->getDeveloper()->getFullName();
        }

        // Icon url
        $iconUrl = null;
        $icon = $plugin->icon;

        if ($icon) {
            $iconUrl = $icon->getUrl();
        }

        // Screenshots
        $screenshotUrls = [];
        $screenshotIds = [];

        foreach ($plugin->getScreenshots() as $screenshot) {
            $screenshotUrls[] = $screenshot->getUrl();
            $screenshotIds[] = $screenshot->getId();
        }

        // Categories
        $categoryIds = [];

        foreach ($plugin->getCategories() as $category) {
            $categoryIds[] = $category->id;
        }

        // Return data
        $data = [
            'id' => $plugin->id,
            'iconUrl' => $iconUrl,
            'handle' => $plugin->handle,
            'name' => $plugin->name,
            'shortDescription' => $plugin->shortDescription,
            'price' => $plugin->price,
            'renewalPrice' => $plugin->renewalPrice,
            'developerId' => $plugin->getDeveloper()->id,
            'developerName' => $developerName,
            'categoryIds' => $categoryIds,
            'version' => $plugin->latestVersion,
            'packageName' => $plugin->packageName,
        ];

        if (!$snippetOnly) {
            $data['lastUpdate'] = $plugin->dateUpdated->format(\DateTime::ATOM);
            $data['activeInstalls'] = 0;
            $data['compatibility'] = 'Craft 3';
            $data['status'] = $plugin->status;
            $data['iconId'] = $plugin->iconId;
            $data['longDescription'] = $plugin->longDescription;
            $data['documentationUrl'] = $plugin->documentationUrl;
            $data['changelogPath'] = $plugin->changelogPath;
            $data['repository'] = $plugin->repository;
            $data['license'] = $plugin->license;
            $data['developerUrl'] = $plugin->getDeveloper()->developerUrl;
            $data['screenshotUrls'] = $screenshotUrls;
            $data['screenshotIds'] = $screenshotIds;
        }

        return $data;
    }
}
