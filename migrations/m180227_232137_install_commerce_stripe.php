<?php

namespace craft\contentmigrations;

use Craft;
use craft\commerce\gateways\Dummy;
use craft\commerce\Plugin as Commerce;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;
use craft\db\Migration;
use yii\base\Exception;

/**
 * m180227_232137_install_commerce_stripe migration.
 */
class m180227_232137_install_commerce_stripe extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Install the plugin
        $this->dropTableIfExists('stripe_customers');
        Craft::$app->getPlugins()->installPlugin('commerce-stripe');

        // Archive the Dummy gateway
        $gatewaysService = Commerce::getInstance()->getGateways();
        /** @var Dummy $gateway */
        $gateway = $gatewaysService->getGatewayByHandle('dummy');
        $gatewaysService->archiveGatewayById($gateway->id);

        // Create the Stripe gateway
        $gateway = new StripeGateway([
            'name' => 'Stripe',
            'handle' => 'stripe',
            'frontendEnabled' => true,
            'isArchived' => false,
        ]);
        $gatewaysService->saveGateway($gateway);

        // Update .env with the gateway ID
        try {
            Craft::$app->getConfig()->setDotEnvVar('STRIPE_GATEWAY_ID', $gateway->id);
        } catch (Exception $e) {
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180227_232137_install_commerce_stripe cannot be reverted.\n";
        return false;
    }
}
