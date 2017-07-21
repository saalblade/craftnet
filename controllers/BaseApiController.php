<?php

namespace craftcom\api\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\api\Module;
use JsonSchema\Validator;
use stdClass;
use yii\web\BadRequestHttpException;

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
}
