<?php

namespace craftnet\console\controllers;

use Craft;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craftnet\base\LicenseInterface;
use craftnet\Module;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Handles tasks that apply to both Craft and plugin licenses.
 *
 * @property Module $module
 */
class LicensesController extends Controller
{
    /**
     * Sends reminders to people whose Craft/plugin license(s) will be expiring in the next 14-30 days.
     *
     * @return int
     */
    public function actionSendReminders(): int
    {
        $cmsLicenseManager = $this->module->getCmsLicenseManager();
        $pluginLicenseManager = $this->module->getPluginLicenseManager();

        // Find licenses that need reminders
        $this->stdout('Finding licenses that are due for reminders ... ', Console::FG_YELLOW);
        $licenses = array_merge(
            $cmsLicenseManager->getRemindableLicenses(),
            $pluginLicenseManager->getRemindableLicenses()
        );
        $this->stdout('done (' . count($licenses) . ' licenses found)' . PHP_EOL, Console::FG_YELLOW);

        if (empty($licenses)) {
            $this->stdout('Nothing to send.' . PHP_EOL . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout('Sending reminders ...' . PHP_EOL, Console::FG_YELLOW);

        // Group by owner ID and auto-renew status
        $licenses = ArrayHelper::index($licenses, null, 'ownerId');

        $mailer = Craft::$app->getMailer();

        foreach ($licenses as $ownerId => $ownerLicenses) {
            try {
                $user = User::find()->id($ownerId)->anyStatus()->one();

                // Lock in the renewal prices
                /** @var LicenseInterface[] $ownerLicenses */
                foreach ($ownerLicenses as $license) {
                    if ($license->getWillAutoRenew()) {
                        $newRenewalPrice = $license->getEdition()->getRenewal()->getPrice();
                        if ($license->getRenewalPrice() !== $newRenewalPrice) {
                            $license->setRenewalPrice($newRenewalPrice);
                        }
                    }
                }

                $ownerLicensesByType = ArrayHelper::index($ownerLicenses, null, function(LicenseInterface $license) {
                    return $license->getWillAutoRenew() ? 'auto' : 'manual';
                });

                $this->stdout("    - Emailing {$user->email} about " . count($ownerLicenses) . ' licenses ... ', Console::FG_YELLOW);

                $message = $mailer
                    ->composeFromKey(Module::MESSAGE_KEY_LICENSE_REMINDER, ['licenses' => $ownerLicensesByType])
                    ->setTo($user);

                if (!$message->send()) {
                    $this->stderr('error sending email' . PHP_EOL, Console::FG_RED);
                    continue;
                }

                $this->stdout('done' . PHP_EOL, Console::FG_GREEN);

                // Mark the licenses as reminded so we don't send this again for them until the next cycle
                foreach ($ownerLicenses as $license) {
                    $license->markAsReminded();
                }
            } catch (\Throwable $e) {
                // Don't let this stop us from sending other reminders
                $this->stdout('An error occurred: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);
                Craft::$app->getErrorHandler()->logException($e);
            }
        }

        $this->stdout('Done sending reminders.' . PHP_EOL . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
