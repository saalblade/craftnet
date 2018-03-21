<?php

namespace craftnet\console\controllers;

use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craftnet\helpers\KeyHelper;
use craftnet\Module;
use craftnet\plugins\Plugin;
use craftnet\plugins\PluginEdition;
use craftnet\plugins\PluginLicense;
use yii\console\Controller;
use yii\helpers\Console;
use yii\validators\EmailValidator;

/**
 * Manages Craft licenses.
 *
 * @property Module $module
 */
class PluginLicensesController extends Controller
{
    /**
     * Claims licenses for the user with the given username or email.
     */
    public function actionCreate(): int
    {
        $license = new PluginLicense();

        $plugin = null;
        $edition = null;

        $license->plugin = $this->prompt('Plugin:', [
            'required' => true,
            'validator' => function(string $input, string &$error = null) {
                if (Plugin::find()->handle($input)->one() === null) {
                    $error = 'No plugin exists with that handle.';
                    return false;
                }
                return true;
            }
        ]);

        /** @var Plugin $plugin */
        $plugin = Plugin::find()->handle($license->plugin)->one();

        $license->edition = $this->prompt('Edition:', [
            'required' => true,
            'validator' => function(string $input, string &$error = null) use ($plugin) {
                if (PluginEdition::find()->pluginId($plugin->id)->handle($input)->one() === null) {
                    $validEditions = PluginEdition::find()->pluginId($plugin->id)->select(['craftnet_plugineditions.handle'])->column();
                    $error = 'Invalid edition handle. Valid options are: '.implode(', ', $validEditions);
                    return false;
                }
                return true;
            },
            'default' => PluginEdition::find()->pluginId($plugin->id)->one()->handle,
        ]);

        /** @var PluginEdition $edition */
        $edition = PluginEdition::find()->pluginId($plugin->id)->handle($license->edition)->one();

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
        }

        $license->notes = $this->prompt('Owner-facing notes:') ?: null;
        $license->privateNotes = $this->prompt('Private notes:') ?: null;

        $license->pluginId = $plugin->id;
        $license->editionId = $edition->id;
        $license->ownerId = User::find()->select(['elements.id'])->email($license->email)->scalar() ?: null;
        $license->expired = $license->expiresOn !== null ? $license->expiresOn->getTimestamp() < time() : false;
        $license->key = KeyHelper::generatePluginKey();

        if (!$this->module->getPluginLicenseManager()->saveLicense($license)) {
            $this->stderr('Could not save license: '.implode(', ', $license->getFirstErrors().PHP_EOL), Console::FG_RED);
            return 1;
        }

        $this->stdout('License saved: '.$license->key.PHP_EOL, Console::FG_GREEN);
        return 0;
    }
}
