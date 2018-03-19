<?php

namespace craft\contentmigrations;

use Craft;
use craft\commerce\gateways\Dummy;
use craft\commerce\Plugin as Commerce;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;
use craft\db\Migration;
use yii\base\Exception;

/**
 * m180318_222142_install_commerce migration.
 */
class m180318_222142_install_commerce extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Install Commerce
        $pluginsService = Craft::$app->getPlugins();
        $pluginsService->installPlugin('commerce');
        $commerce = Commerce::getInstance();

        // Delete the default product type
        $productTypesService = $commerce->productTypes;
        $productType = $productTypesService->getProductTypeByHandle('clothing');
        if ($productType) {
            $productTypesService->deleteProductTypeById($productType->id);
        }

        // Install the Stripe plugin
        $this->dropTableIfExists('stripe_customers');
        $pluginsService->installPlugin('commerce-stripe');

        // Set the order PDF
        $settings = $commerce->getSettings();
        $settings->orderPdfPath = '1';
        $settings->orderPdfFilenameFormat = 'Order-{shortNumber|upper}';
        $pluginsService->savePluginSettings($commerce, $settings->toArray());

        // Archive the Dummy gateway
        $gatewaysService = $commerce->getGateways();
        /** @var Dummy $gateway */
        $gateway = $gatewaysService->getGatewayByHandle('dummy');
        $gatewaysService->archiveGatewayById($gateway->id);

        // Create the Stripe gateway
        $gateway = new StripeGateway([
            'name' => 'Stripe',
            'handle' => 'stripe',
            'isFrontendEnabled' => true,
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
        echo "m180318_222142_install_commerce cannot be reverted.\n";
        return false;
    }
}
