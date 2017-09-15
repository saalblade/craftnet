<?php

namespace craftcom\api\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\api\Module;
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

    protected function pluginTransformer(Entry $entry)
    {
        // Developer name

        $developerName = $entry->getAuthor()->developerName;

        if (empty($developerName)) {
            $developerName = $entry->getAuthor()->getFullName();
        }


        // Icon url

        $iconUrl = null;
        $icon = $entry->icon->one();

        if($icon) {
            $iconUrl = $icon->getUrl();
        }


        // Screenshots

        $screenshots = [];

        foreach($entry->screenshots->all() as $screenshot) {
            $screenshots[] = $screenshot->getUrl();
        }


        // Categories

        $categories = [];

        foreach($entry->categories->all() as $category) {
            $categories[] = $category->id;
        }


        // Package

        try {
            $client = new Client();
            $response = $client->get('https://packagist.org/packages/'.$entry->getAuthor()->vendor.'/'.$entry->slug.'.json');
            $data = Json::decode($response->getBody()->getContents());
            $package = $data['package'];
        } catch(\Exception $e) {
            $package = null;
        }

        return [
            'id' => $entry->id,
            'slug' => $entry->slug,
            'title' => $entry->title,
            'name' => $entry->title,
            'shortDescription' => $entry->shortDescription,
            'description' => $entry->description,
            'iconUrl' => $iconUrl,
            'price' => $entry->price,
            'licensePrice' => $entry->price,
            'updatePrice' => $entry->updatePrice,
            'developerId' => $entry->getAuthor()->id,
            'developerName' => $developerName,
            'developerUrl' => $entry->getAuthor()->developerUrl,
            'developerVendor' => $entry->getAuthor()->vendor,
            'githubRepoUrl' => $entry->githubRepoUrl,
            'screenshots' => $screenshots,
            'categories' => $categories,
            'package' => $package,
        ];
    }
}
