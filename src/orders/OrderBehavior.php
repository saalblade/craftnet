<?php

namespace craftnet\orders;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\records\Transaction as TransactionRecord;
use craft\elements\User;
use craft\helpers\Template;
use craft\web\View;
use craftnet\base\PluginPurchasable;
use craftnet\cms\CmsLicense;
use craftnet\developers\UserBehavior;
use craftnet\Module;
use craftnet\plugins\PluginLicense;
use yii\base\Behavior;
use yii\helpers\Markdown;

/**
 * @property Order $owner
 */
class OrderBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        // todo: we should probably be listening for a transaction event here
        return [
            Order::EVENT_AFTER_COMPLETE_ORDER => [$this, 'afterComplete'],
        ];
    }

    /**
     * Returns any Craft licenses that were purchased by this order.
     *
     * @return CmsLicense[]
     */
    public function getCmsLicenses(): array
    {
        return Module::getInstance()->getCmsLicenseManager()->getLicensesByOrder($this->owner->id);
    }

    /**
     * Returns any plugin licenses that were purchased by this order.
     *
     * @return PluginLicense[]
     */
    public function getPluginLicenses(): array
    {
        return Module::getInstance()->getPluginLicenseManager()->getLicensesByOrder($this->owner->id);
    }

    /**
     * Handles post-order-complete stuff.
     */
    public function afterComplete()
    {
        if (!$this->owner->getIsPaid()) {
            return;
        }

        $this->_updateDeveloperFunds();
        $this->_sendReceipt();
    }

    /**
     * Updates developers' accounts and attempts to transfer $$ to them after an order has completed.
     */
    private function _updateDeveloperFunds()
    {
        // See if any plugin licenses were purchased/renewed
        /** @var User[]|UserBehavior[] $developers */
        $developers = [];
        $developerTotals = [];
        foreach ($this->owner->getLineItems() as $lineItem) {
            $purchasable = $lineItem->getPurchasable();
            if ($purchasable instanceof PluginPurchasable) {
                $plugin = $purchasable->getPlugin();
                $developerId = $plugin->developerId;
                if (!isset($developers[$developerId])) {
                    $developers[$developerId] = $plugin->getDeveloper();
                    $developerTotals[$developerId] = $lineItem->total;
                } else {
                    $developerTotals[$developerId] += $lineItem->total;
                }
            }
        }

        if (empty($developers)) {
            return;
        }

        // find the first successful transaction on the order
        // todo: if we change the event that triggers this, then we will need to be more careful about which transaction we're looking for
        $transaction = null;
        foreach ($this->owner->getTransactions() as $t) {
            if ($t->status === TransactionRecord::STATUS_SUCCESS) {
                $transaction = $t;
                break;
            }
        }
        if (!$transaction) {
            return;
        }

        // Try transferring funds to them
        foreach ($developers as $developerId => $developer) {
            // ignore if this is us
            if ($developer->username === 'pixelandtonic') {
                continue;
            }

            // figure out our 20% fee (up to 2 decimals)
            $total = $developerTotals[$developerId];
            $fee = floor($total * 20) / 100;
            $developer->getFundsManager()->processOrder($this->owner->number, $transaction->reference, $total, $fee);
        }
    }

    /**
     * Sends the customer a receipt email after an order has completed.
     */
    private function _sendReceipt()
    {
        // render the PDF
        $pdf = (new PdfRenderer())->render($this->owner);

        $view = Craft::$app->getView();
        $templateMode = $view->getTemplateMode();
        $twig = $view->getTwig();

        // render the text body
        $view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        $view->setTemplatesPath(__DIR__.'/receipt/templates');
        $twig->setDefaultEscaperStrategy(false);
        $textBody = $view->renderTemplate('email.txt', [
            'order' => $this->owner,
        ]);

        // render the HTML body
        $view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $twig->setDefaultEscaperStrategy();
        $htmlBody = $view->renderTemplate('_special/email', [
            'order' => $this->owner,
            'body' => Template::raw(Markdown::process($textBody)),
        ]);

        $view->setTemplateMode($templateMode);

        $mailer = Craft::$app->getMailer();
        $mailer->compose()
            ->setFrom($mailer->from)
            ->setTo($this->owner->getEmail())
            ->setSubject('Your receipt from Pixel & Tonic')
            ->setTextBody($textBody)
            ->setHtmlBody($htmlBody)
            ->attachContent($pdf, [
                'fileName' => 'Order-'.strtoupper($this->owner->getShortNumber()).'.pdf',
                'contentType' => 'application/pdf',
            ])
            ->send();
    }
}
