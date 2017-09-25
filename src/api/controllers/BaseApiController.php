<?php

namespace craftcom\api\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\api\Module;
use craftcom\plugins\Plugin;
use GuzzleHttp\Client;
use JsonSchema\Validator;
use stdClass;
use yii\web\BadRequestHttpException;
use craft\elements\Entry;

/**
 * Class BaseController
 *
 * @package craftcom\api\controllers
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
    protected function getRequestBody(string $schema = null): stdClass
    {
        $body = Json::decode(Craft::$app->getRequest()->getRawBody(), false);

        if ($schema !== null) {
            $validator = new Validator();
            $path = Module::getInstance()->getBasePath()."/json-schemas/$schema.json";
            $validator->validate($body, (object)['$ref' => 'file://'.$path]);

            if (!$validator->isValid()) {
                throw new BadRequestHttpException('Invalid request body.');
            }
        }

        return $body;
    }

    protected function pluginTransformer(Plugin $plugin)
    {
        // Developer name

        $developerName = $plugin->getDeveloper()->developerName;

        if (empty($developerName)) {
            $developerName = $plugin->getDeveloper()->getFullName();
        }


        // Icon url

        $iconUrl = null;
        $icon = $plugin->icon;

        if($icon) {
            $iconUrl = $icon->getUrl();
        }


        // Screenshots

        $screenshots = [];

        foreach($plugin->screenshots as $screenshot) {
            $screenshots[] = $screenshot->getUrl();
        }


        // Categories

        $categories = [];

        foreach($plugin->categories as $category) {
            $categories[] = $category->id;
        }


        // Package

        try {
            $client = new Client();
            $response = $client->get('https://packagist.org/packages/'.$plugin->getDeveloper()->vendor.'/'.$plugin->slug.'.json');
            $data = Json::decode($response->getBody()->getContents());
            $package = $data['package'];
        } catch(\Exception $e) {
            $package = null;
        }

        return [
            'id' => $plugin->id,
            'status' => $plugin->status,
            'iconId' => $plugin->iconId,
            'iconUrl' => $plugin->icon->getUrl(),
            'packageName' => $plugin->packageName,
            'handle' => $plugin->handle,
            'name' => $plugin->name,
            'shortDescription' => $plugin->shortDescription,
            'longDescription' => $plugin->longDescription,
            'documentationUrl' => $plugin->documentationUrl,
            'changelogUrl' => $plugin->changelogUrl,
            'repository' => $plugin->repository,
            'license' => $plugin->license,
            'price' => $plugin->price,
            'renewalPrice' => $plugin->renewalPrice,

            // 'iconUrl' => $iconUrl,
            'developerId' => $plugin->getDeveloper()->id,
            'developerName' => $developerName,
            'developerUrl' => $plugin->getDeveloper()->developerUrl,
            'developerVendor' => $plugin->getDeveloper()->vendor,

            'screenshots' => $screenshots,
            'categories' => $categories,
            'package' => $package,
        ];
    }
}
