<?php

namespace craftcom\controllers\id;

use Craft;
use craft\db\Query;
use craft\helpers\Json;
use craft\web\Controller;
use craftcom\Module;
use craftcom\plugins\Plugin;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\Bitbucket;

/**
 * Class BaseController
 *
 * @property array $apps
 */
abstract class BaseController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    // Protected Methods
    // =========================================================================

    /**
     * @param Plugin $plugin
     *
     * @return array
     */
    protected function pluginTransformer(Plugin $plugin): array
    {
        // Developer name
        $developerName = $plugin->getDeveloper()->developerName;

        if (empty($developerName)) {
            $developerName = $plugin->getDeveloper()->getFullName();
        }

        // Icon url
        $iconUrl = null;
        $icon = $plugin->icon;

        if ($icon) {
            $iconUrl = $icon->getUrl();
        }

        // Screenshots
        $screenshotUrls = [];
        $screenshotIds = [];

        foreach ($plugin->screenshots as $screenshot) {
            $screenshotUrls[] = $screenshot->getUrl();
            $screenshotIds[] = $screenshot->getId();
        }

        // Categories
        $categoryIds = [];

        foreach ($plugin->categories as $category) {
            $categoryIds[] = $category->id;
        }

        // Package
        try {
            $client = new Client();
            $response = $client->get('https://packagist.org/packages/'.$plugin->getDeveloper()->vendor.'/'.$plugin->slug.'.json');
            $data = Json::decode($response->getBody()->getContents());
            $package = $data['package'];
        } catch (\Exception $e) {
            $package = null;
        }

        return [
            'id' => $plugin->id,
            'enabled' => $plugin->enabled,
            'pendingApproval' => $plugin->pendingApproval,
            'status' => $plugin->status,
            'iconId' => $plugin->iconId,
            'iconUrl' => $iconUrl,
            'packageName' => $plugin->packageName,
            'handle' => $plugin->handle,
            'name' => $plugin->name,
            'shortDescription' => $plugin->shortDescription,
            'longDescription' => $plugin->longDescription,
            'documentationUrl' => $plugin->documentationUrl,
            'changelogUrl' => $plugin->changelogUrl,
            'repository' => $plugin->repository,
            'license' => $plugin->license,
            'price' => $plugin->price,
            'renewalPrice' => $plugin->renewalPrice,

            // 'iconUrl' => $iconUrl,
            'developerId' => $plugin->getDeveloper()->id,
            'developerName' => $developerName,
            'developerUrl' => $plugin->getDeveloper()->developerUrl,

            'screenshotUrls' => $screenshotUrls,
            'screenshotIds' => $screenshotIds,
            'categoryIds' => $categoryIds,
            'package' => $package,
        ];
    }
}
