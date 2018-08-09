<?php

namespace craftnet\console\controllers;

use craft\db\Query;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craftnet\cms\CmsLicense;
use craftnet\errors\LicenseNotFoundException;
use craftnet\helpers\KeyHelper;
use craftnet\Module;
use yii\base\InvalidArgumentException;
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
     * Creates a new Craft license.
     */
    public function actionCreate(): int
    {
        $license = new CmsLicense();
        $edition = null;

        $license->editionHandle = $this->editionHandlePrompt('solo');
        $license->email = $this->emailPrompt();

        if ($license->expirable = $this->confirm('Expirable?')) {
            $license->expiresOn = $this->expiresOnPrompt();
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

    /**
     * Updates an existing Craft license.
     *
     * @param string $key The license key (or first few characters of it)
     */
    public function actionUpdate(string $key)
    {
        $manager = $this->module->getCmsLicenseManager();

        try {
            $key = $manager->normalizeKey($key);
        } catch (InvalidArgumentException $e) {
            $licenses = (new Query())
                ->select(['key', 'domain'])
                ->from(['craftnet_cmslicenses'])
                ->where(['like', 'key', $key . '%', false])
                ->all();

            if (empty($licenses)) {
                $this->stderr('No Craft licenses exist with a key that starts with "' . $key . '".' . PHP_EOL, Console::FG_RED);
                return 1;
            }

            $options = [];
            $keys = [];
            foreach ($licenses as $i => $license) {
                $index = (string)($i + 1);
                $options[$index] = $license['key'] . ($license['domain'] ? "({$license['domain']})" : '');
                $keys[$index] = $license['key'];
            }
            $choice = $this->select('Which license key?', $options);
            $key = $keys[$choice];
        }

        try {
            $license = $manager->getLicenseByKey($key);
        } catch (LicenseNotFoundException $e) {
            $this->stderr($e->getMessage() . PHP_EOL, Console::FG_RED);
            return 1;
        }

        $edition = null;

        $license->editionHandle = $this->editionHandlePrompt($license->editionHandle);
        $license->editionId = null;
        $license->email = $this->emailPrompt($license->email);

        if ($license->expirable = $this->confirm('Expirable?', $license->expirable)) {
            $license->expiresOn = $this->expiresOnPrompt($license->expiresOn);
            $license->autoRenew = $this->confirm('Auto-renew?', $license->autoRenew);
        }

        $license->notes = $this->prompt('Owner-facing notes:', [
            'default' => $license->notes,
        ]) ?: null;
        $license->privateNotes = $this->prompt('Private notes:', [
            'default' => $license->privateNotes,
        ]) ?: null;

        $license->expired = $license->expiresOn !== null ? $license->expiresOn->getTimestamp() < time() : false;

        if (!$this->module->getCmsLicenseManager()->saveLicense($license)) {
            $this->stderr('Could not save license: '.implode(', ', $license->getFirstErrors().PHP_EOL), Console::FG_RED);
            return 1;
        }

        $this->stdout('License saved: '.PHP_EOL.chunk_split($license->key, 50).PHP_EOL, Console::FG_GREEN);

        if ($this->confirm('Create a history record for the license?', true)) {
            $note = $this->prompt('Note: ', [
                'required' => true,
            ]);
            $this->module->getCmsLicenseManager()->addHistory($license->id, $note);
        }

        return 0;
    }

    protected function editionHandlePrompt(string $default = null): string
    {
        return $this->prompt('Edition ("solo" or "pro"):', [
            'required' => true,
            'default' => $default,
            'validator' => function(string $edition, string &$error = null) {
                if (!in_array($edition, ['solo', 'pro'], true)) {
                    $error = 'Must be either "solo" or "pro".';
                    return false;
                }
                return true;
            }
        ]);
    }

    protected function emailPrompt(string $default = null): string
    {
        return $this->prompt('Owner email:', [
            'required' => true,
            'default' => $default,
            'validator' => function(string $email, string &$error = null) {
                return (new EmailValidator())->validate($email, $error);
            }
        ]);
    }

    protected function expiresOnPrompt(\DateTime $default = null): \DateTime
    {
        if ($default === null) {
            $default = (new \DateTime())->modify('+1 year');
        }

        return DateTimeHelper::toDateTime($this->prompt('Expiration date:', [
            'required' => true,
            'validator' => function(string $input) {
                return DateTimeHelper::toDateTime($input) !== false;
            },
            'default' => $default->format(\DateTime::ATOM),
        ]));
    }
}
