<?php

namespace craftcom\controllers\api;

use Craft;
use craft\db\Connection;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\Module;
use craftcom\plugins\Plugin;
use JsonSchema\Validator;
use stdClass;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

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
     * @var
     */
    private $_logRequestId;

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @return mixed
     */
    public function getLogRequestId()
    {
        return $this->_logRequestId;
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = [])
    {
        /** @var Connection $logDb */
        $logDb = Craft::$app->get('logDb', false);

        // This should only exist on production.
        if ($logDb) {
            $insertRequest = [];

            $insertRequest['verb'] = Craft::$app->getRequest()->getMethod();
            $insertRequest['ip'] = Craft::$app->getRequest()->getRemoteIP();
            $insertRequest['url'] = (Craft::$app->getRequest()->getIsSecureConnection() ? 'https://' : 'http://').Craft::$app->getRequest()->getRemoteHost().Craft::$app->getRequest()->getUrl();
            $insertRequest['route'] = $this->getRoute();
            $insertRequest['dateCreated'] = Db::prepareDateForDb(new \DateTime());

            try {
                $rawBody = Craft::$app->getRequest()->getRawBody();

                // See if it's valid JSON.
                Json::decode($rawBody);
                $insertRequest['bodyJson'] = $rawBody;
            } catch (InvalidParamException $e) {
                // There was a problem JSON decoding.
                $insertRequest['bodyText'] = $rawBody;
            }

            $logDb->createCommand()->insert('request', $insertRequest, false)->execute();
            $this->_logRequestId = $logDb->getLastInsertID('request');
        }

        try {
            return parent::runAction($id, $params);
        }
        catch (\Exception $e) {
            if ($logDb) {
                $insertError = [];

                $statusCode = $e instanceof HttpException && $e->statusCode ? $e->statusCode : 500;

                $insertError['requestId'] = $this->getLogRequestId();
                $insertError['message'] = $e->getMessage();
                $insertError['httpStatus'] = $statusCode;
                $insertError['stackTrace'] = $e->getTraceAsString();
                $insertError['dateCreated'] = Db::prepareDateForDb(new \DateTime());

                $logDb->createCommand()->insert('errors', $insertError, false)->execute();
            }

            return $this->asErrorJson($e->getMessage())->setStatusCode($statusCode);
        }
    }

    /**
     * Returns the JSON-decoded request body.
     *
     * @param string|null $schema JSON schema to validate the body with (optional)
     *
     * @return stdClass|array
     * @throws BadRequestHttpException if the data doesn't validate
     */
    protected function getPayload(string $schema = null)
    {
        $body = Json::decode(Craft::$app->getRequest()->getRawBody(), false);

        if ($schema !== null) {
            $validator = new Validator();
            $path = Craft::getAlias("@root/json-schemas/{$schema}.json");
            $validator->validate($body, (object)['$ref' => 'file://'.$path]);

            if (!$validator->isValid()) {
                Craft::warning("Invalid API request payload (validated against {$schema}):\n".print_r($validator->getErrors(), true), __METHOD__);
                throw new BadRequestHttpException('Invalid request body.');
            }
        }

        return $body;
    }

    /**
     * @param Plugin $plugin
     * @param bool   $fullDetails
     * @param bool   $includePrices
     *
     * @return array
     */
    protected function transformPlugin(Plugin $plugin, bool $fullDetails = true, bool $includePrices = true): array
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
            'price' => $includePrices ? $plugin->price : null,
            'renewalPrice' => $includePrices ? $plugin->renewalPrice : null,
            'currency' => 'USD',
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

            // todo: remove this when $includePricens goes away
            $longDescription = $plugin->longDescription;
            if (!$includePrices && $plugin->price) {
                $price = Craft::$app->getFormatter()->asCurrency($plugin->price, 'USD');
                $longDescription = "_This plugin will cost {$price} once Craft 3 GA is released._\n\n{$longDescription}";
            }

            $data['lastUpdate'] = $plugin->dateUpdated->format(\DateTime::ATOM);
            $data['activeInstalls'] = 0;
            $data['compatibility'] = 'Craft 3';
            $data['status'] = $plugin->status;
            $data['iconId'] = $plugin->iconId;
            $data['longDescription'] = $longDescription;
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
