<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craftnet\controllers\api\BaseApiController;
use craftnet\errors\LicenseNotFoundException;
use craftnet\errors\ValidationException;
use craftnet\helpers\KeyHelper;
use craftnet\plugins\Plugin;
use craftnet\plugins\PluginLicense;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\validators\EmailValidator;
use yii\web\NotFoundHttpException;
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
     * Lists licenses for a plugin developer’s plugins.
     *
     * @param int $page
     * @param int $perPage
     * @return Response
     * @throws UnauthorizedHttpException
     */
    public function actionList(int $page = 1, int $perPage = 100): Response
    {
        if (($user = Craft::$app->getUser()->getIdentity(false)) === null) {
            throw new UnauthorizedHttpException('Not Authorized');
        }

        list($offset, $limit) = $this->page2offset($page, $perPage);
        $licenses = $this->module->getPluginLicenseManager()->getLicensesByDeveloper($user->id, $offset, $limit, $total);
        return $this->asJson([
            'total' => $total,
            'totalPages' => ceil($total / $limit),
            'licenses' => ArrayHelper::toArray($licenses),
        ]);
    }

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
        if (($user = Craft::$app->getUser()->getIdentity(false)) === null) {
            throw new UnauthorizedHttpException('Not Authorized');
        }

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

        $expirable = $payload->expirable ?? false;
        $expiresOn = $expirable ? (new \DateTime())->modify('+1 year') : null;

        if (
            $expirable &&
            isset($payload->expiresOn) &&
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
            'pluginHandle' => $plugin->handle,
            'edition' => $edition->handle,
            'expirable' => $expirable,
            'expired' => isset($expiresOn) ? $expiresOn->getTimestamp() < time() : false,
            'email' => $payload->email,
            'key' => KeyHelper::generatePluginKey(),
            'notes' => $payload->notes ?? null,
            'privateNotes' => $payload->privateNotes ?? null,
            'expiresOn' => $expiresOn,
        ]);

        $manager = $this->module->getPluginLicenseManager();

        if (!$manager->saveLicense($license)) {
            throw new Exception('Could not create plugin license: '.implode(', ', $license->getErrorSummary(true)));
        }

        $manager->addHistory($license->id, "created for {$license->email} by {$user->email}");

        return $this->asJson([
            'license' => $license,
        ]);
    }

    /**
     * Returns a CMS license.
     *
     * @param string $key
     * @return Response
     * @throws UnauthorizedHttpException
     */
    public function actionGet(string $key): Response
    {
        if (($user = Craft::$app->getUser()->getIdentity(false)) === null) {
            throw new UnauthorizedHttpException('Not Authorized');
        }

        try {
            $license = $this->module->getPluginLicenseManager()->getLicenseByKey($key);
        } catch (LicenseNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        if ($license->getPlugin()->developerId != $user->id) {
            throw new UnauthorizedHttpException('Not Authorized');
        }

        return $this->asJson([
            'license' => $license->toArray(),
        ]);
    }
}
