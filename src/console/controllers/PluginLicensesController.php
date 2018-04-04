<?php

namespace craftnet\console\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craftnet\errors\LicenseNotFoundException;
use craftnet\helpers\KeyHelper;
use craftnet\Module;
use craftnet\plugins\Plugin;
use craftnet\plugins\PluginEdition;
use craftnet\plugins\PluginLicense;
use yii\base\InvalidArgumentException;
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

        $license->pluginHandle = $this->prompt('Plugin:', [
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
        $plugin = Plugin::find()->handle($license->pluginHandle)->one();

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

        $cmsLicenseKey = $this->prompt('Craft license key (optional):', [
            'validator' => function(string $input, string &$error = null) {
                try {
                    $this->module->getCmsLicenseManager()->getLicenseByKey($input);
                    return true;
                } catch (LicenseNotFoundException $e) {
                    $error = $e->getMessage();
                    return false;
                }
            }
        ]);

        if ($cmsLicenseKey) {
            $license->cmsLicenseId = $this->module->getCmsLicenseManager()->getLicenseByKey($cmsLicenseKey)->id;
        }

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

        $license->key = $this->prompt('License key (optional):', [
            'validator' => function(string $input, string &$error = null) {
                try {
                    $this->module->getPluginLicenseManager()->normalizeKey($input);
                    return true;
                } catch (InvalidArgumentException $e) {
                    $error = $e->getMessage();
                    return false;
                }
            }
        ]) ?: KeyHelper::generatePluginKey();

        $license->pluginId = $plugin->id;
        $license->editionId = $edition->id;
        $license->ownerId = User::find()->select(['elements.id'])->email($license->email)->scalar() ?: null;
        $license->expired = $license->expiresOn !== null ? $license->expiresOn->getTimestamp() < time() : false;

        if (!$this->module->getPluginLicenseManager()->saveLicense($license)) {
            $this->stderr('Could not save license: '.implode(', ', $license->getFirstErrors().PHP_EOL), Console::FG_RED);
            return 1;
        }

        $this->stdout('License saved: '.$license->key.PHP_EOL, Console::FG_GREEN);

        if ($this->confirm('Associate the license with an order?')) {
            $orderNumber = $this->prompt('Order number:', [
                'required' => true,
                'validator' => function(string $input) {
                    return Order::find()->number($input)->exists();
                }
            ]);
            $order = Order::find()->number($orderNumber)->one();
            /** @var LineItem[] $lineItems */
            $lineItems = [];
            $lineItemOptions = [];
            foreach ($order->getLineItems() as $i => $lineItem) {
                $key = (string)($i + 1);
                $lineItems[$key] = $lineItem;
                $lineItemOptions[$key] = $lineItem->getDescription();
            }
            $key = $this->select('Which line item?', $lineItemOptions);
            $lineItem = $lineItems[$key];
            Craft::$app->getDb()->createCommand()
                ->insert('craftnet_pluginlicenses_lineitems', [
                    'licenseId' => $license->id,
                    'lineItemId' => $lineItem->id,
                ], false)
                ->execute();
        }

        if ($this->confirm('Create a history record for the license?')) {
            $note = $this->prompt('Note: ', [
                'required' => true,
                'default' => "created by {$license->email}".(isset($order) ? " per order {$order->number}" : '')
            ]);
            $this->module->getPluginLicenseManager()->addHistory($license->id, $note);
        }

        return 0;
    }
}
