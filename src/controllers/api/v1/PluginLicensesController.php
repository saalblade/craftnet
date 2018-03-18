<?php

namespace craftcom\controllers\api\v1;

use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\ValidationException;
use craftcom\helpers\KeyHelper;
use craftcom\plugins\Plugin;
use craftcom\plugins\PluginLicense;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\validators\EmailValidator;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Class PluginLicensesController
 */
class PluginLicensesController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Creates a new CMS license.
     *
     * @return Response
     * @throws UnauthorizedHttpException
     * @throws ValidationException
     * @throws Exception
     */
    public function actionCreate(): Response
    {
        $user = $this->getAuthUser();
        $payload = $this->getPayload('create-plugin-license-request');

        $plugin = Plugin::find()
            ->handle($payload->plugin)
            ->one();

        if ($plugin === null) {
            throw new ValidationException([
                [
                    'param' => 'plugin',
                    'message' => 'Invalid plugin handle: '.$payload->plugin,
                    'code' => self::ERROR_CODE_MISSING,
                ]
            ]);
        }

        if ($plugin->developerId != $user->id) {
            throw new UnauthorizedHttpException('Not Authorized');
        }

        try {
            $edition = $plugin->getEdition($payload->edition);
        } catch (InvalidArgumentException $e) {
            throw new ValidationException([
                [
                    'param' => 'edition',
                    'message' => $e->getMessage(),
                    'code' => self::ERROR_CODE_MISSING,
                ]
            ]);
        }

        // validation
        $errors = [];

        if ((new EmailValidator())->validate($payload->email, $error) === false) {
            $errors[] = [
                'param' => 'email',
                'message' => $error,
                'code' => self::ERROR_CODE_INVALID,
            ];
        }

        if (
            ($expirable = $payload->expirable ?? false) &&
            ($expiresOn = $payload->expiresOn ?? null) &&
            ($expiresOn = DateTimeHelper::toDateTime($expiresOn)) === false
        ) {
            $errors[] = [
                'param' => 'expiresOn',
                'message' => 'Invalid date value',
                'code' => self::ERROR_CODE_INVALID,
            ];
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // see if there's a Craft ID account for the email
        $ownerId = User::find()
            ->select(['elements.id'])
            ->email($payload->email)
            ->scalar();

        $license = new PluginLicense([
            'pluginId' => $plugin->id,
            'editionId' => $edition->id,
            'ownerId' => $ownerId ?: null,
            'plugin' => $plugin->handle,
            'edition' => $edition->handle,
            'expirable' => $expirable,
            'expired' => isset($expiresOn) ? $expiresOn->getTimestamp() < time() : false,
            'email' => $payload->email,
            'key' => KeyHelper::generatePluginKey(),
            'notes' => $payload->notes ?? null,
            'privateNotes' => $payload->privateNotes ?? null,
            'expiresOn' => $expiresOn ?? null,
        ]);

        if (!$this->module->getPluginLicenseManager()->saveLicense($license)) {
            throw new Exception('Could not create plugin license: '.implode(', ', $license->getErrorSummary(true)));
        }

        return $this->asJson([
            'license' => $license,
        ]);
    }
}
