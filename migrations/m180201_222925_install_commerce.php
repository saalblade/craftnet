<?php

namespace craft\contentmigrations;

use Craft;
use craft\commerce\Plugin;
use craft\db\Migration;

/**
 * m180201_222925_install_commerce migration.
 */
class m180201_222925_install_commerce extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Craft::$app->getPlugins()->installPlugin('commerce');

        // Delete the default product type
        $productTypesService = Plugin::getInstance()->productTypes;
        $productType = $productTypesService->getProductTypeByHandle('clothing');
        if ($productType) {
            $productTypesService->deleteProductTypeById($productType->id);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180201_222925_install_commerce cannot be reverted.\n";
        return false;
    }
}
