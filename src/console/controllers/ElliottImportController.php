<?php

namespace craftnet\console\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Customer;
use craft\commerce\models\OrderStatus;
use craft\commerce\models\TaxCategory;
use craft\commerce\Plugin as Commerce;
use craft\db\Connection;
use craft\db\Query;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craftnet\cms\CmsEdition;
use craftnet\composer\Package;
use craftnet\Module;
use craftnet\plugins\Plugin;
use craftnet\plugins\PluginLicense;
use GuzzleHttp\Client;
use yii\base\Exception;
use yii\caching\FileCache;
use yii\console\Controller;
use yii\db\Expression;
use yii\di\Instance;
use yii\helpers\Console;

/**
 * Imports licensing & purchase data from Elliott
 *
 * @property Module $module
 */
class ElliottImportController extends Controller
{
    /**
     * @var int How many items should be imported at once
     */
    public $perPage = 100;

    /**
     * @var int|bool How many pages to import
     */
    public $totalPages;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'all';

    /**
     * @var Connection
     */
    protected $db = 'db';

    /**
     * @var FileCache
     */
    protected $cache;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int[]|null
     */
    private $_userIds;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db);

        $generalConfig = Craft::$app->getConfig()->getGeneral();
        $this->cache = new FileCache([
            'cachePath' => Craft::$app->getPath()->getRuntimePath().DIRECTORY_SEPARATOR.'elliott_import_cache',
            'fileMode' => $generalConfig->defaultFileMode,
            'dirMode' => $generalConfig->defaultDirMode,
            'defaultDuration' => $generalConfig->cacheDuration,
        ]);

        $this->client = Craft::createGuzzleClient([
            'base_uri' => rtrim(getenv('ELLIOTT_BASE_URL'), '/'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'perPage';
        $options[] = 'totalPages';
        return $options;
    }

    /**
     * Imports all the things.
     */
    public function actionAll()
    {
        $startTime = microtime(true);
        if ($this->runAction('cms-licenses') == 0) {
            $this->stdout(PHP_EOL);
            if ($this->runAction('commerce-licenses') == 0) {
                $this->stdout(PHP_EOL);
                if ($this->runAction('orders') == 0) {
                    $this->stdout(PHP_EOL);
                    $this->runAction('discounts');
                }
            }
        }

        $this->stdout(PHP_EOL);
        $this->stdout('All done (time: '.$this->formatTime(microtime(true) - $startTime).')'.PHP_EOL.PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Imports Craft licenses.
     */
    public function actionCmsLicenses()
    {
        $this->stdout('Importing Craft licenses'.PHP_EOL);
        $startTime = microtime(true);
        $manager = $this->module->getCmsLicenseManager();

        $editionIds = CmsEdition::find()
            ->select(['handle', 'elements.id'])
            ->pairs();

        $result = $this->import('cmsLicenses', function(array $item) use ($manager, $editionIds) {
            $data = [
                'editionId' => $editionIds[$item['edition']],
                'ownerId' => $this->userId($item['email']),
                'expirable' => $item['edition'] === 'personal',
                'expired' => false,
                'autoRenew' => false,
                'edition' => $item['edition'],
                'email' => $item['email'],
                'domain' => $item['domain'] ? $manager->normalizeDomain($item['domain']) : null,
                'key' => $manager->normalizeKey($item['licenseKey']),
                'privateNotes' => $item['notes'],
                'lastVersion' => $item['lastVersion'],
                'lastAllowedVersion' => $item['lastVersion'],
                'lastActivityOn' => $item['lastActivity'],
                'dateCreated' => $item['dateCreated'],
            ];
            $keySample = substr($item['licenseKey'], 0, 10);

            if ($item['active']) {
                $this->stdout("    > Saving Craft license {$keySample} ... ");
                $this->db->createCommand()
                    ->insert('craftnet_cmslicenses', $data)
                    ->execute();

                $note = "created by {$item['email']}";
                if ($data['domain']) {
                    $note .= " for domain {$data['domain']}";
                }
                $manager->addHistory($this->db->getLastInsertID('craftnet_cmslicenses'), $note, $item['dateCreated']);
            } else {
                $this->stdout("    > Saving Craft license {$keySample} ");
                $this->stdout('(inactive)', Console::FG_YELLOW);
                $this->stdout(' ... ');

                $this->db->createCommand()
                    ->insert('craftnet_inactivecmslicenses', [
                        'key' => $item['licenseKey'],
                        'data' => Json::encode($data),
                    ], false)
                    ->execute();
            }

            $this->stdout('done'.PHP_EOL);
        });

        $this->stdout('done (time: '.$this->formatTime(microtime(true) - $startTime).')'.PHP_EOL);

        return $result;
    }

    /**
     * Imports Commerce licenses.
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function actionCommerceLicenses()
    {
        $this->stdout('Importing Commerce licenses'.PHP_EOL);
        $startTime = microtime(true);

        $cmsLicenseManager = $this->module->getCmsLicenseManager();
        $pluginLicenseManager = $this->module->getPluginLicenseManager();

        $plugin = $this->getCommerce();
        $edition = $plugin->getEdition('standard');

        $result = $this->import('commerceLicenses', function(array $item) use (
            $cmsLicenseManager,
            $pluginLicenseManager,
            $plugin,
            $edition
        ) {
            if (!empty($item['cmsLicenseKey'])) {
                $cmsLicense = $cmsLicenseManager->getLicenseByKey($item['cmsLicenseKey']);
            }

            $license = new PluginLicense([
                'pluginId' => $plugin->id,
                'editionId' => $edition->id,
                'cmsLicenseId' => $cmsLicense->id ?? null,
                'ownerId' => $this->userId($item['email']),
                'plugin' => $plugin->handle,
                'edition' => $edition->handle,
                'expirable' => false,
                'expired' => false,
                'autoRenew' => false,
                'email' => $item['email'],
                'key' => $pluginLicenseManager->normalizeKey($item['licenseKey']),
                'privateNotes' => $item['notes'],
                'lastActivityOn' => $item['dateUpdated'],
                'dateCreated' => $item['dateCreated'],
            ]);

            $this->stdout("    > Saving Commerce license {$item['licenseKey']} ... ");
            $pluginLicenseManager->saveLicense($license, false);
            $pluginLicenseManager->addHistory($license->id, "created by {$license->email}", $item['dateCreated']);
            $this->stdout('done'.PHP_EOL);
        });

        $this->stdout('done (time: '.$this->formatTime(microtime(true) - $startTime).')'.PHP_EOL);

        return $result;
    }

    /**
     * Imports orders.
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function actionOrders()
    {
        $this->stdout('Importing orders'.PHP_EOL);
        $startTime = microtime(true);

        $plugin = $this->getCommerce();
        $edition = $plugin->getEdition('standard');
        $commerce = Commerce::getInstance();
        $fieldLayoutId = $commerce->getOrderSettings()->getOrderSettingByHandle('order')->fieldLayoutId;
        $siteId = Craft::$app->getSites()->getPrimarySite()->id;

        $orderStatusesService = $commerce->getOrderStatuses();
        $orderStatusIds = [];
        foreach (['charged', 'refunded', 'disputed'] as $i => $handle) {
            if (($orderStatus = $orderStatusesService->getOrderStatusByHandle($handle)) === null) {
                $orderStatus = new OrderStatus([
                    'name' => ucfirst($handle),
                    'handle' => $handle,
                    'color' => ['green', 'blue', 'red'][$i],
                    'sortOrder' => $i + 1,
                    'default' => $i === 0,
                ]);
                if (!$orderStatusesService->saveOrderStatus($orderStatus, [])) {
                    throw new Exception('Could not save order status: '.implode(', ', $orderStatus->getErrorSummary(true)));
                }
            }
            $orderStatusIds[$handle] = $orderStatus->id;
        }
        unset($handle);

        $countryIds = (new Query())
            ->select(['iso', 'id'])
            ->from(['commerce_countries'])
            ->pairs();

        $stateIds = (new Query())
            ->select([new Expression('[[countryId]] || \'.\' || [[abbreviation]]'), 'id'])
            ->from(['commerce_states'])
            ->pairs();

        // create Commerce customers for all of the existing Craft IDs
        $users = (new Query())
            ->select(['u.id as id', 'lower([[email]]) as email', 'c.id as customerId'])
            ->from(['users u'])
            ->leftJoin('commerce_customers c', '[[c.userId]] = [[u.id]]')
            ->all();
        $customersService = $commerce->getCustomers();

        foreach ($users as &$user) {
            if ($user['customerId'] === null) {
                $customer = new Customer([
                    'userId' => $user['id']
                ]);
                if (!$customersService->saveCustomer($customer)) {
                    throw new Exception('Could not save customer: '.implode(', ', $customer->getErrorSummary(true)));
                }
                $user['customerId'] = $customer->id;
            }
        }
        unset($user);

        $customerIds = ArrayHelper::map($users, 'email', 'customerId');

        // get the staff user IDs
        $staffIds = User::find()
            ->select(['username', 'elements.id'])
            ->group('staff')
            ->status(null)
            ->asArray()
            ->pairs();

        $purchasableIds = [
            'cms-pro' => CmsEdition::find()->handle('pro')->one()->id,
            'cms-client' => CmsEdition::find()->handle('client')->one()->id,
            'commerce' => $edition->id,
        ];
        // treat Client > Pro upgrades as Pro purchases too
        $purchasableIds['cms-pro-upgrade'] = $purchasableIds['cms-pro'];

        $taxCategoriesService = $commerce->getTaxCategories();
        if (($taxCategory = $taxCategoriesService->getTaxCategoryByHandle('digital')) === null) {
            $taxCategory = new TaxCategory([
                'name' => 'Digital',
                'handle' => 'digital',
                'default' => true,
            ]);
            if (!$taxCategoriesService->saveTaxCategory($taxCategory)) {
                throw new Exception('Could not save tax category: '.implode(', ', $taxCategory->getErrorSummary(true)));
            }
        }
        $taxCategoryId = $taxCategory->id;

        $shippingCategoryId = $commerce->getShippingCategories()->getDefaultShippingCategory()->id;

        $cmsLicenseManager = $this->module->getCmsLicenseManager();
        $pluginLicenseManager = $this->module->getPluginLicenseManager();

        $result = $this->import('orders', function(array $item) use (
            $fieldLayoutId,
            $siteId,
            $orderStatusIds,
            $countryIds,
            $stateIds,
            $customerIds,
            $staffIds,
            $purchasableIds,
            $taxCategoryId,
            $shippingCategoryId,
            $cmsLicenseManager,
            $pluginLicenseManager
        ) {
            if (empty($item['lineItems'])) {
                $this->stdout("    > Skipping order {$item['number']} (no line items)".PHP_EOL, Console::FG_YELLOW);
                return;
            }

            $this->stdout("    > Saving order {$item['number']} ... ");

            // element
            $this->db->createCommand()
                ->insert('elements', [
                    'fieldLayoutId' => $fieldLayoutId,
                    'type' => Order::class,
                    'enabled' => true,
                    'archived' => false,
                    'dateCreated' => $item['dateCreated'],
                    'dateUpdated' => $item['dateUpdated'],
                ])
                ->execute();
            $orderId = $this->db->getLastInsertID('elements');
            $this->db->createCommand()
                ->insert('elements_sites', [
                    'elementId' => $orderId,
                    'siteId' => $siteId,
                    'enabled' => true,
                    'dateCreated' => $item['dateCreated'],
                    'dateUpdated' => $item['dateUpdated'],
                ])
                ->execute();
            $this->db->createCommand()
                ->insert('content', [
                    'elementId' => $orderId,
                    'siteId' => $siteId,
                    'dateCreated' => $item['dateCreated'],
                    'dateUpdated' => $item['dateUpdated'],
                ])
                ->execute();

            // billing address
            $hasBillingAddress = false;
            $ba = $item['billingAddress'];
            foreach ($ba as $baKey => $baValue) {
                if (
                    ($baKey !== 'id' && $baKey !== 'country' && $baValue !== null) ||
                    ($baKey === 'country' & $baValue !== '??')
                ) {
                    $hasBillingAddress = true;
                    break;
                }
            }

            if ($hasBillingAddress) {
                $countryId = $ba['country'] && $ba['country'] !== '??' ? $countryIds[$ba['country']] : null;
                $stateId = $countryId && $ba['state'] && $ba['state'] !== '??' ? $stateIds["{$countryId}.{$ba['state']}"] : null;

                $this->db->createCommand()
                    ->insert('commerce_addresses', [
                        'countryId' => $countryId,
                        'stateId' => $stateId,
                        'isStoreLocation' => false,
                        'attention' => $ba['attention'],
                        'title' => $ba['attention'],
                        'firstName' => (string)$ba['firstName'],
                        'lastName' => (string)$ba['lastName'],
                        'address1' => $ba['address1'],
                        'address2' => $ba['address2'],
                        'city' => $ba['city'],
                        'zipCode' => $ba['zipCode'],
                        'phone' => $ba['phone'],
                        'businessName' => $ba['businessName'],
                        'businessTaxId' => $ba['businessTaxId'],
                        'stateName' => $ba['stateName'],
                        'dateCreated' => $item['dateCreated'],
                        'dateUpdated' => $item['dateUpdated'],
                    ])
                    ->execute();
                $billingAddressId = $this->db->getLastInsertID('commerce_addresses');
            } else {
                $billingAddressId = null;
            }

            // customer
            $email = mb_strtolower($item['email']);
            if (isset($customerIds[$email])) {
                $customerId = $customerIds[$email];
            } else {
                $this->db->createCommand()
                    ->insert('commerce_customers', [
                        'dateCreated' => $item['dateCreated'],
                        'dateUpdated' => $item['dateUpdated'],
                    ])
                    ->execute();
                $customerId = $this->db->getLastInsertID('commerce_customers');
            }

            // order
            $this->db->createCommand()
                ->insert('commerce_orders', [
                    'id' => $orderId,
                    'billingAddressId' => $billingAddressId,
                    'gatewayId' => getenv('STRIPE_GATEWAY_ID'),
                    'customerId' => $customerId,
                    'orderStatusId' => isset($item['orderStatus']) ? $orderStatusIds[$item['orderStatus']] : null,
                    'number' => $item['number'],
                    'couponCode' => $item['couponCode'],
                    'itemTotal' => $item['itemTotal'],
                    'totalPrice' => $item['totalPrice'],
                    'totalPaid' => $item['totalPaid'],
                    'email' => $item['email'],
                    'isCompleted' => $item['isCompleted'],
                    'dateOrdered' => $item['dateOrdered'],
                    'datePaid' => $item['datePaid'],
                    'currency' => 'USD',
                    'paymentCurrency' => 'USD',
                    'lastIp' => $item['lastIp'],
                    'message' => $item['message'],
                    'dateCreated' => $item['dateCreated'],
                    'dateUpdated' => $item['dateUpdated'],
                ])
                ->execute();

            // transactions
            if (!empty($item['transactions'])) {
                $this->addTransactions($staffIds, $orderId, $item['transactions']);
            }

            // line items
            $cmsLineItemId = null;
            $commerceLineItemId = null;

            foreach ($item['lineItems'] as $lineItem) {
                $this->db->createCommand()
                    ->insert('commerce_lineitems', [
                        'orderId' => $orderId,
                        'purchasableId' => $purchasableIds[$lineItem['purchasable']] ?? null,
                        'taxCategoryId' => $taxCategoryId,
                        'shippingCategoryId' => $shippingCategoryId,
                        'options' => '[]',
                        'optionsSignature' => 'd751713988987e9331980363e24189ce',
                        'price' => $lineItem['price'],
                        'saleAmount' => 0,
                        'salePrice' => $lineItem['salePrice'],
                        'weight' => 0,
                        'height' => 0,
                        'length' => 0,
                        'width' => 0,
                        'subtotal' => $lineItem['price'],
                        'total' => $lineItem['total'],
                        'qty' => $lineItem['qty'],
                        'note' => $lineItem['note'],
                        'snapshot' => $lineItem['snapshot'],
                        'dateCreated' => $item['dateCreated'],
                        'dateUpdated' => $item['dateUpdated'],
                    ])
                    ->execute();
                $lineItemId = $this->db->getLastInsertID('commerce_lineitems');

                if ($lineItem['purchasable'] === 'commerce') {
                    $commerceLineItemId = $lineItemId;
                } else {
                    $cmsLineItemId = $lineItemId;
                }

                if ($lineItem['discount']) {
                    $this->db->createCommand()
                        ->insert('commerce_orderadjustments', [
                            'orderId' => $orderId,
                            'lineItemId' => $lineItemId,
                            'type' => 'discount',
                            'name' => 'Discount',
                            'amount' => $lineItem['discount'],
                            'sourceSnapshot' => '[]',
                            'dateCreated' => $item['dateCreated'],
                            'dateUpdated' => $item['dateUpdated'],
                        ])
                        ->execute();
                }
            }

            // history
            if (!empty($item['history'])) {
                foreach ($item['history'] as $history) {
                    $this->db->createCommand()
                        ->insert('commerce_orderhistories', [
                            'orderId' => $orderId,
                            'customerId' => $customerId,
                            'prevStatusId' => isset($history['prevStatus']) ? $orderStatusIds[$history['prevStatus']] : null,
                            'newStatusId' => isset($history['newStatus']) ? $orderStatusIds[$history['newStatus']] : null,
                            'message' => $history['message'],
                            'dateCreated' => $history['dateCreated'],
                            'dateUpdated' => $history['dateUpdated'],
                        ])
                        ->execute();
                }
            }

            // Craft licenses
            if (!empty($item['cmsLicenses'])) {
                if ($cmsLineItemId === null) {
                    throw new Exception("Could not associate Craft license with order {$item['number']} (no line item).");
                }

                foreach ($item['cmsLicenses'] as $key) {
                    $license = $cmsLicenseManager->getLicenseByKey($key);
                    $this->db->createCommand()
                        ->insert('craftnet_cmslicenses_lineitems', [
                            'licenseId' => $license->id,
                            'lineItemId' => $cmsLineItemId,
                        ], false)
                        ->execute();

                    // update the license's email and ownerId
                    if ($newEmail = (strcasecmp($license->email, $item['email']) !== 0)) {
                        $license->email = $item['email'];
                        $license->ownerId = $this->userId($item['email']);
                        $cmsLicenseManager->saveLicense($license);
                    }

                    // update the license history
                    $note = "upgraded to {$license->edition}";
                    if ($newEmail) {
                        $note .= " and reassigned to {$license->email}";
                    }
                    $cmsLicenseManager->addHistory($license->id, "{$note} per order {$item['number']}");
                }
            }

            // Commerce licenses
            if (!empty($item['commerceLicenses'])) {
                if ($commerceLineItemId === null) {
                    throw new Exception("Could not associate Commerce license with order {$item['number']} (no line item).");
                }

                foreach ($item['commerceLicenses'] as $key) {
                    $license = $pluginLicenseManager->getLicenseByKey('commerce', $key);
                    $this->db->createCommand()
                        ->insert('craftnet_pluginlicenses_lineitems', [
                            'licenseId' => $license->id,
                            'lineItemId' => $commerceLineItemId,
                        ], false)
                        ->execute();

                    // update the license's email and ownerId
                    if ($newEmail = (strcasecmp($license->email, $item['email']) !== 0)) {
                        $license->email = $item['email'];
                        $license->ownerId = $this->userId($item['email']);
                        $pluginLicenseManager->saveLicense($license);
                    }

                    // update the license history
                    $note = "upgraded to {$license->edition}";
                    if ($newEmail) {
                        $note .= " and reassigned to {$license->email}";
                    }
                    $pluginLicenseManager->addHistory($license->id, "{$note} per order {$item['number']}");
                }
            }

            $this->stdout('done'.PHP_EOL);
        });

        $this->stdout('done (time: '.$this->formatTime(microtime(true) - $startTime).')'.PHP_EOL);

        return $result;
    }

    /**
     * Imports discounts.
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function actionDiscounts()
    {
        $this->stdout('Importing discounts'.PHP_EOL);
        $startTime = microtime(true);

        $editionIds = CmsEdition::find()
            ->select(['handle', 'elements.id'])
            ->andWhere(['craftnet_cmseditions.handle' => ['client', 'pro']])
            ->pairs();

        $result = $this->import('discounts', function(array $item) use ($editionIds) {
            $this->stdout("    > Saving discount \"{$item['name']}\" ({$item['code']}) ... ");

            $this->db->createCommand()
                ->insert('commerce_discounts', [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'code' => $item['code'],
                    'perUserLimit' => 0,
                    'perEmailLimit' => 0,
                    'totalUseLimit' => $item['totalUseLimit'],
                    'totalUses' => $item['totalUses'],
                    'dateFrom' => null,
                    'dateTo' => null,
                    'purchaseTotal' => 0,
                    'purchaseQty' => 0,
                    'maxPurchaseQty' => 0,
                    'baseDiscount' => 0,
                    'perItemDiscount' => 0,
                    'percentDiscount' => $item['percentDiscount'],
                    'percentageOffSubject' => 'original',
                    'excludeOnSale' => false,
                    'freeShipping' => false,
                    'allGroups' => true,
                    'allPurchasables' => true,
                    'allCategories' => true,
                    'enabled' => true,
                    'stopProcessing' => false,
                    'sortOrder' => 999,
                    'dateCreated' => $item['dateCreated'],
                    'dateUpdated' => $item['dateUpdated'],
                ])
                ->execute();
            $discountId = $this->db->getLastInsertID('commerce_discounts');

            if (stripos($item['name'], 'craft pro') !== false) {
                $purchasableIds = [$editionIds['pro']];
            } else if (stripos($item['name'], 'craft client') !== false) {
                $purchasableIds = [$editionIds['client']];
            } else {
                $purchasableIds = [$editionIds['pro'], $editionIds['client']];
            }

            foreach ($purchasableIds as $purchasableId) {
                $this->db->createCommand()
                    ->insert('commerce_discount_purchasables', [
                        'discountId' => $discountId,
                        'purchasableId' => $purchasableId,
                        'dateCreated' => $item['dateCreated'],
                        'dateUpdated' => $item['dateUpdated'],
                    ])
                    ->execute();
            }

            $this->stdout('done'.PHP_EOL);
        });

        $this->stdout('done (time: '.$this->formatTime(microtime(true) - $startTime).')'.PHP_EOL);

        return $result;
    }

    // Protected Methods
    // =========================================================================

    protected function formatTime(float $seconds): string
    {
        $formatted = '';
        $minutes = floor($seconds / 60);
        $seconds -= ($minutes * 60);
        $hours = floor($minutes / 60);
        $minutes %= 60;
        $formatted .= sprintf('%d:%02d:%06.3f', $hours, $minutes, $seconds);
        return $formatted;
    }

    /**
     * @return Plugin
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    protected function getCommerce(): Plugin
    {
        $plugin = Plugin::find()
            ->handle('commerce')
            ->status(null)
            ->one();

        if ($plugin !== null) {
            return $plugin;
        }

        $packageManager = $this->module->getPackageManager();
        try {
            $package = $packageManager->getPackage('craftcms/commerce');
        } catch (Exception $e) {
            $this->stdout('    > Adding Commerce package ... ', Console::FG_YELLOW);
            $package = new Package([
                'name' => 'craftcms/commerce',
                'type' => 'craft-plugin',
                'repository' => 'https://github.com/craftcms/commerce',
                'managed' => true,
            ]);
            $packageManager->savePackage($package);
            $this->stdout('done'.PHP_EOL, Console::FG_YELLOW);
        }

        $plugin = new Plugin([
            'developerId' => User::find()->username('pixelandtonic')->one()->id,
            'packageId' => $package->id,
            'packageName' => $package->name,
            'repository' => $package->repository,
            'name' => 'Craft Commerce',
            'handle' => 'commerce',
            'price' => 999,
            'renewalPrice' => 199,
            'license' => 'craft',
            'documentationUrl' => 'https://craftcommerce.com/docs',
            'changelogPath' => 'CHANGELOG.md',
        ]);

        $this->stdout('    > Adding Commerce plugin ... ', Console::FG_YELLOW);
        if (!Craft::$app->getElements()->saveElement($plugin)) {
            throw new Exception('Could not save Commerce plugin: '.implode(',', $plugin->getErrorSummary(true)));
        }
        $this->stdout('done'.PHP_EOL, Console::FG_YELLOW);

        return $plugin;
    }

    protected function addTransactions(array $staffIds, int $orderId, array $transactions, int $parentId = null)
    {
        foreach ($transactions as $transaction) {
            $this->db->createCommand()
                ->insert('commerce_transactions', [
                    'parentId' => $parentId,
                    'gatewayId' => getenv('STRIPE_GATEWAY_ID'),
                    'userId' => $transaction['user'] ? $staffIds[$transaction['user']] : null,
                    'hash' => $transaction['hash'],
                    'type' => $transaction['type'],
                    'amount' => $transaction['amount'],
                    'paymentAmount' => $transaction['paymentAmount'],
                    'currency' => 'USD',
                    'paymentCurrency' => 'USD',
                    'paymentRate' => 1,
                    'status' => $transaction['status'],
                    'reference' => $transaction['reference'],
                    'code' => $transaction['code'],
                    'message' => $transaction['message'],
                    'response' => $transaction['response'],
                    'orderId' => $orderId,
                    'dateCreated' => $transaction['dateCreated'],
                    'dateUpdated' => $transaction['dateUpdated'],
                ])
                ->execute();

            if (isset($transaction['nested'])) {
                $transactionId = $this->db->getLastInsertID('commerce_transactions');
                $this->addTransactions($staffIds, $orderId, $transaction['nested'], $transactionId);
            }
        }
    }

    protected function import(string $uri, callable $callback): int
    {
        $fetched = 0;

        if ($imported = $this->cache->get($uri.'-imported') ?: 0) {
            $startPage = floor($imported / $this->perPage) + 1;
            $skip = $imported % $this->perPage;
        } else {
            $startPage = 1;
            $skip = 0;
        }

        do {
            $page = $startPage + $fetched;

            $this->stdout("    > Fetching {$uri} p.{$page} ... ");
            $response = $this->get($uri, $page, $this->perPage);
            $this->stdout('done ('.count($response['data']).' items)'.PHP_EOL);

            if ($skip) {
                $this->stdout("    > Skipping first {$skip} items".PHP_EOL, Console::FG_YELLOW);
                array_splice($response['data'], 0, $skip);
                $skip = 0;
            }

            $transaction = $this->db->beginTransaction();
            $success = true;

            try {
                foreach ($response['data'] as $item) {
                    if ($callback($item) === false) {
                        $success = false;
                        break;
                    }

                    $imported++;
                }
            } catch (\Throwable $e) {
                $this->stderr("error: {$e->getMessage()}".PHP_EOL, Console::FG_RED);
                $success = false;
            }

            if (!$success) {
                $transaction->rollBack();
                $this->stderr('    > Aborting import due to error.'.PHP_EOL, Console::FG_YELLOW);
                return 1;
            }

            $transaction->commit();
            $this->cache->set($uri.'-imported', $imported);
            $fetched++;
        } while (
            (!$this->totalPages || $fetched < $this->totalPages) &&
            $page < $response['pagination']['totalPages']
        );

        return 0;
    }

    protected function get(string $uri, int $page, int $perPage): array
    {
        $response = $this->client->get('/actions/ops/export/'.$uri, [
            'query' => [
                'key' => getenv('ELLIOTT_KEY'),
                'page' => $page,
                'perPage' => $perPage,
            ]
        ]);

        return Json::decode((string)$response->getBody());
    }

    /**
     * @param string $email
     * @return int|null
     */
    protected function userId(string $email)
    {
        if ($this->_userIds === null) {
            $this->_userIds = User::find()
                ->select([new Expression('lower([[email]])'), 'elements.id'])
                ->asArray()
                ->pairs();
        }

        return $this->_userIds[strtolower($email)] ?? null;
    }
}
