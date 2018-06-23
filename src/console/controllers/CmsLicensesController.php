<?php

namespace craftnet\console\controllers;

use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craftnet\cms\CmsLicense;
use craftnet\helpers\KeyHelper;
use craftnet\Module;
use yii\console\Controller;
use yii\helpers\Console;
use yii\validators\EmailValidator;

/**
 * Manages Craft licenses.
 *
 * @property Module $module
 */
class CmsLicensesController extends Controller
{
    /**
     * Claims licenses for the user with the given username or email.
     */
    public function actionCreate(): int
    {
        $license = new CmsLicense();
        $edition = null;

        $license->editionHandle = $this->select('Edition:', [
            'solo' => 'Solo',
            'pro' => 'Pro',
        ]);

        $license->email = $this->prompt('Owner email:', [
            'required' => true,
            'validator' => function(string $email, string &$error = null) {
                return (new EmailValidator())->validate($email, $error);
            }
        ]);

        if ($license->expirable = $this->confirm('Expirable?')) {
            $license->expiresOn = DateTimeHelper::toDateTime($this->prompt('Expiration date:', [
                'required' => true,
                'validator' => function(string $input) {
                    return DateTimeHelper::toDateTime($input) !== false;
                },
                'default' => (new \DateTime())->modify('+1 year')->format(\DateTime::ATOM),
            ]));
            $license->autoRenew = $this->confirm('Auto-renew?');
        }

        $license->notes = $this->prompt('Owner-facing notes:') ?: null;
        $license->privateNotes = $this->prompt('Private notes:') ?: null;

        $license->key = KeyHelper::generateCmsKey();
        $license->ownerId = User::find()->select(['elements.id'])->email($license->email)->scalar() ?: null;
        $license->expired = $license->expiresOn !== null ? $license->expiresOn->getTimestamp() < time() : false;

        if (!$this->module->getCmsLicenseManager()->saveLicense($license)) {
            $this->stderr('Could not save license: '.implode(', ', $license->getFirstErrors().PHP_EOL), Console::FG_RED);
            return 1;
        }

        $this->stdout('License saved: '.PHP_EOL.chunk_split($license->key, 50).PHP_EOL, Console::FG_GREEN);

        if ($this->confirm('Create a history record for the license?', true)) {
            $note = $this->prompt('Note: ', [
                'required' => true,
                'default' => "created by {$license->email}"
            ]);
            $this->module->getCmsLicenseManager()->addHistory($license->id, $note);
        }

        return 0;
    }
}
