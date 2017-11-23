<?php

namespace craftcom\controllers\api;

use Craft;
use craft\helpers\ArrayHelper;
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
                Craft::warning("Invalid API request payload (validated against {$schema}):\n".print_r($validator->getErrors(), true));
                throw new BadRequestHttpException('Invalid request body.');
            }
        }

        return $body;
    }

    /**
     * @param Plugin $plugin
     * @param bool   $fullDetails
     *
     * @return array
     */
    protected function transformPlugin(Plugin $plugin, $fullDetails = true): array
    {
        $icon = $plugin->getIcon();
        $developer = $plugin->getDeveloper();

        // Return data
        $data = [
            'id' => $plugin->id,
            'iconUrl' => $icon ? $icon->getUrl().'?'.$icon->dateModified->getTimestamp() : null,
            'handle' => $plugin->handle,
            'name' => $plugin->name,
            'shortDescription' => $plugin->shortDescription,
            'price' => $plugin->price,
            'renewalPrice' => $plugin->renewalPrice,
            'developerId' => $developer->id,
            'developerName' => $developer->getDeveloperName(),
            'categoryIds' => ArrayHelper::getColumn($plugin->getCategories(), 'id'),
            'version' => $plugin->latestVersion,
            'packageName' => $plugin->packageName,
        ];

        if ($fullDetails) {
            // Screenshots
            $screenshotUrls = [];
            $screenshotIds = [];

            foreach ($plugin->getScreenshots() as $screenshot) {
                $screenshotUrls[] = $screenshot->getUrl().'?'.$screenshot->dateModified->getTimestamp();
                $screenshotIds[] = $screenshot->getId();
            }

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
            $data['developerUrl'] = $developer->developerUrl;
            $data['screenshotUrls'] = $screenshotUrls;
            $data['screenshotIds'] = $screenshotIds;
        }

        return $data;
    }
}
