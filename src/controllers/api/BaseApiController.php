<?php

namespace craftcom\controllers\api;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\HtmlPurifier;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\cms\CmsLicense;
use craftcom\errors\ValidationException;
use craftcom\Module;
use craftcom\plugins\Plugin;
use craftcom\plugins\PluginLicense;
use JsonSchema\Validator;
use stdClass;
use yii\base\Model;
use yii\base\UserException;
use yii\helpers\Markdown;
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
    const ERROR_CODE_INVALID = 'invalid';
    const ERROR_CODE_MISSING = 'missing';
    const ERROR_CODE_EXISTS = 'already_exists';

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * The API request ID, if there is one.
     *
     * @var int|null
     */
    public $requestId;

    /**
     * The Craft license associated with this request.
     *
     * @var CmsLicense[]
     */
    public $cmsLicenses = [];

    /**
     * The plugin licenses associated with this request.
     *
     * @var PluginLicense[]
     */
    public $pluginLicenses = [];

    /**
     * @var array
     */
    private $_logRequestKeys = [];

    /**
     * @return array
     */
    public function getLogRequestKeys(): array
    {
        return $this->_logRequestKeys;
    }

    /**
     * @param $key
     * @param null $pluginHandle
     */
    public function addLogRequestKey($key, $pluginHandle = null)
    {
        if (!$pluginHandle) {
            $pluginHandle = 'craft';
        }

        $this->_logRequestKeys[$pluginHandle] = $key;
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = [])
    {
        $request = Craft::$app->getRequest();
        $headers = $request->getHeaders();

//        // get the system info
//        $systemVersions = [];
//        $systemEditions = [];
//        if (($systemHeader = $headers->get('X-Craft-System')) !== null) {
//            foreach (explode(',', $systemHeader) as $installed) {
//                list($name, $info) = explode(':', $installed);
//                list($version, $edition) = array_pad(explode(';', $info), 2, null);
//            }
//        }


        $e = null;
        $responseCode = 200;

        try {
            $e = null;
            if (($cmsLicenseKey = $headers->get('X-Craft-License')) !== null) {
                try {
                    $this->cmsLicenses[] = $this->module->getCmsLicenseManager()->getLicenseByKey($cmsLicenseKey);
                } catch (\Throwable $e) {
                    // keep going for now
                }
            }

            if (($pluginLicenseKeys = $headers->get('X-Craft-Plugin-Licenses')) !== null) {
                $pluginLicenseManager = $this->module->getPluginLicenseManager();
                foreach (explode(',', $pluginLicenseKeys) as $pluginLicenseInfo) {
                    list($pluginHandle, $pluginLicenseKey) = explode(':', $pluginLicenseInfo);
                    try {
                        $this->pluginLicenses[] = $pluginLicenseManager->getLicenseByKey($pluginHandle, $pluginLicenseKey);
                    } catch (\Throwable $e) {
                        // keep going for now
                    }
                }
            }

            // any exceptions getting the licenses?
            if ($e !== null) {
                throw new BadRequestHttpException('Bad Request', 0, $e);
            }

            $response = parent::runAction($id, $params);
        } catch (\Throwable $e) {
            // log it and keep going
            Craft::$app->getErrorHandler()->logException($e);
            $responseCode = $e instanceof HttpException && $e->statusCode ? $e->statusCode : 500;
        }

        // log the request
        $db = Craft::$app->getDb();

        $db->createCommand()
            ->insert('apilog.requests', [
                'verb' => $request->getMethod(),
                'uri' => $request->getUrl(),
                'ip' => $request->getUserIP(),
                'action' => $this->getUniqueId().'/'.$id,
                'body' => $request->getRawBody(),
                'system' => $headers->get('X-Craft-System'),
                'platform' => $headers->get('X-Craft-Platform'),
                'host' => $headers->get('X-Craft-Host'),
                'userEmail' => $headers->get('X-Craft-User-Email'),
                'userIp' => $headers->get('X-Craft-User-Ip'),
                'timestamp' => Db::prepareDateForDb(new \DateTime()),
                'responseCode' => $responseCode,
            ], false)
            ->execute();

        // get the request ID
        $this->requestId = $db->getLastInsertID('apilog.requests');

        // log any licenses associated with the request
        foreach ($this->cmsLicenses as $cmsLicense) {
            $db->createCommand()
                ->insert('apilog.request_cmslicenses', [
                    'requestId' => $this->requestId,
                    'licenseId' => $cmsLicense->id,
                ], false)
                ->execute();
        }
        foreach ($this->pluginLicenses as $pluginLicense) {
            $db->createCommand()
                ->insert('apilog.request_pluginlicenses', [
                    'requestId' => $this->requestId,
                    'licenseId' => $pluginLicense->id,
                ], false)
                ->execute();
        }

        // if there was an exception, log it and return the error response
        if ($e !== null) {
            $db->createCommand()
                ->insert('apilog.request_errors', [
                    'requestId' => $this->requestId,
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'stackTrace' => $e->getTraceAsString(),
                ], false)
                ->execute();

            // assemble and return the response
            $data = [
                'message' => $e instanceof UserException && $e->getMessage() ? $e->getMessage() : 'Server Error',
            ];
            if ($e instanceof ValidationException) {
                $data['errors'] = $e->errors;
            }

            return $this->asJson($data)
                ->setStatusCode($responseCode);
        }

        return $response;
    }

    /**
     * Returns the JSON-decoded request body.
     *
     * @param string|null $schema JSON schema to validate the body with (optional)
     *
     * @return stdClass|array
     * @throws ValidationException if the data doesn't validate
     */
    protected function getPayload(string $schema = null)
    {
        $body = Json::decode(Craft::$app->getRequest()->getRawBody(), false);

        if ($schema !== null) {
            $validator = new Validator();
            $path = Craft::getAlias("@root/json-schemas/{$schema}.json");
            $validator->validate($body, (object)['$ref' => 'file://'.$path]);

            if (!$validator->isValid()) {
                $errors = [];
                foreach ($validator->getErrors() as $error) {
                    $errors[] = [
                        'param' => $error['property'],
                        'message' => $error['message'],
                        'code' => self::ERROR_CODE_INVALID,
                    ];
                }
                throw new ValidationException($errors);
            }
        }

        return $body;
    }

    /**
     * Returns an array of validation errors for a ValdiationException based on a model's validation errors
     *
     * @param Model $model
     * @param string|null $paramPrefix
     * @return array
     */
    protected function modelErrors(Model $model, string $paramPrefix = null): array
    {
        $errors = [];

        foreach ($model->getErrors() as $attr => $attrErrors) {
            foreach ($attrErrors as $error) {
                $errors[] = [
                    'param' => ($paramPrefix !== null ? $paramPrefix.'.' : '').$attr,
                    'message' => $error,
                    'code' => self::ERROR_CODE_INVALID,
                ];
            }
        }

        return $errors;
    }

    /**
     * @param Plugin $plugin
     * @param bool $fullDetails
     * @param bool $includePrices
     *
     * @return array
     */
    protected function transformPlugin(Plugin $plugin, bool $fullDetails = true, bool $includePrices = true): array
    {
        $icon = $plugin->getIcon();
        $developer = $plugin->getDeveloper();

        $latestRelease = Module::getInstance()->getPackageManager()->getRelease($plugin->packageName, $plugin->latestVersion);

        // Return data
        $data = [
            'id' => $plugin->id,
            'iconUrl' => $icon ? $icon->getUrl().'?'.$icon->dateModified->getTimestamp() : null,
            'handle' => $plugin->handle,
            'name' => strip_tags($plugin->name),
            'shortDescription' => $plugin->shortDescription,
            'price' => $includePrices ? $plugin->price : null,
            'renewalPrice' => $includePrices ? $plugin->renewalPrice : null,
            'currency' => 'USD',
            'developerId' => $developer->id,
            'developerName' => strip_tags($developer->getDeveloperName()),
            'categoryIds' => ArrayHelper::getColumn($plugin->getCategories(), 'id'),
            'keywords' => ($plugin->keywords ? array_map('trim', explode(',', $plugin->keywords)) : []),
            'version' => $plugin->latestVersion,
            'activeInstalls' => $plugin->activeInstalls,
            'packageName' => $plugin->packageName,
            'lastUpdate' => $latestRelease->time ?? $plugin->dateUpdated->format(\DateTime::ATOM),
        ];

        if ($fullDetails) {
            // Screenshots
            $screenshotUrls = [];
            $screenshotIds = [];

            foreach ($plugin->getScreenshots() as $screenshot) {
                $screenshotUrls[] = $screenshot->getUrl().'?'.$screenshot->dateModified->getTimestamp();
                $screenshotIds[] = $screenshot->getId();
            }

            // todo: remove this when $includePrices goes away
            $longDescription = $plugin->longDescription;
            if (!$includePrices && $plugin->price) {
                $price = Craft::$app->getFormatter()->asCurrency($plugin->price, 'USD');
                $longDescription = "_This plugin will cost {$price} once Craft 3 GA is released._\n\n{$longDescription}";
            }

            $longDescription = Markdown::process($longDescription, 'gfm');
            $longDescription = HtmlPurifier::process($longDescription);

            $data['compatibility'] = 'Craft 3';
            $data['status'] = $plugin->status;
            $data['iconId'] = $plugin->iconId;
            $data['longDescription'] = $longDescription;
            $data['documentationUrl'] = $plugin->documentationUrl;
            $data['changelogUrl'] = $plugin->getPackage()->getVcs()->getChangelogUrl();
            $data['repository'] = $plugin->repository;
            $data['license'] = $plugin->license;
            $data['developerUrl'] = $developer->developerUrl;
            $data['screenshotUrls'] = $screenshotUrls;
            $data['screenshotIds'] = $screenshotIds;
        }

        return $data;
    }
}
