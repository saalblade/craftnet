<?php

namespace craftcom\cp\controllers;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\Controller;
use craftcom\Module;
use craftcom\plugins\Plugin;
use Github\Api\Repo;
use Github\Client;
use Github\Exception\RuntimeException;
use yii\base\Exception;
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @property Module $module
 */
class PluginsController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->requireAdmin();
        return true;
    }

    public function actionLoadDetails(string $repository): Response
    {
        $this->requireAcceptsJson();
        $parsed = parse_url($repository);
        $uriParts = isset($parsed['path']) ? explode('/', trim($parsed['path'], '/'), 4) : [];
        list($owner, $repo, $route, $ref) = array_pad($uriParts, 4, null);

        // Make sure this looks like a GitHub repo
        if (
            !isset($parsed['host']) ||
            $parsed['host'] !== 'github.com' ||
            $owner === null ||
            $repo === null ||
            ($route !== null && ($route !== 'tree' || $ref === null))
        ) {
            return $this->asErrorJson("{$repository} is not a valid GitHub repository URL");
        }

        $client = new Client();
        /** @var Repo $api */
        $api = $client->api('repo');

        // Get the composer.json contents
        try {
            $response = $api->contents()->show($owner, $repo, 'composer.json', $ref);
            $config = Json::decode(base64_decode($response['content']));
        } catch (\Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }

        // Get the title and handle
        $handle = $config['extra']['handle'] ?? null;
        if (strtolower($handle) !== $handle) {
            $handle = preg_replace('/\-{2,}/', '-', Inflector::camel2id($handle));
        }
        $name = $config['extra']['name'] ?? null;

        // Get the icon, if we have one
        if ($icon = $this->_getIcon($api, $owner, $repo, $ref, $config, $handle, $name)) {
            $iconHtml = Craft::$app->getView()->renderTemplate('_elements/element', [
                'element' => $icon
            ]);
        } else {
            $iconHtml = null;
        }

        return $this->asJson([
            'repository' => "https://github.com/{$owner}/{$repo}",
            'name' => $name,
            'packageName' => $config['name'] ?? null,
            'handle' => $handle,
            'shortDescription' => $config['extra']['description'] ?? $config['description'] ?? null,
            'longDescription' => $this->_getReadme($api, $owner, $repo),
            'documentationUrl' => $config['extra']['documentationUrl'] ?? $config['support']['docs'] ?? null,
            'changelogUrl' => $config['extra']['changelogUrl'] ?? null,
            'icon' => $iconHtml,
        ]);
    }

    public function actionEdit(int $pluginId = null, Plugin $plugin = null): Response
    {
        if ($plugin === null) {
            if ($pluginId !== null) {
                $plugin = Plugin::find()->id($pluginId)->status(null)->one();
                if ($plugin === false) {
                    throw new NotFoundHttpException('Invalid plugin ID: '.$pluginId);
                }
            } else {
                $plugin = new Plugin([
                    'categories' => [],
                    'screenshots' => [],
                ]);
            }
        }

        $title = $plugin->id ? $plugin->name : 'Add a new plugin';

        return $this->renderTemplate('craftcom/plugins/_edit', compact('plugin', 'title'));
    }

    public function actionSave()
    {
        $request = Craft::$app->getRequest();

        if ($pluginId = $request->getBodyParam('pluginId')) {
            $plugin = Plugin::find()->id($pluginId)->one();
            if ($plugin === false) {
                throw new NotFoundHttpException('Invalid plugin ID: '.$pluginId);
            }
        } else {
            $plugin = new Plugin();
        }

        $plugin->enabled = (bool)$request->getBodyParam('enabled');
        $plugin->developerId = $request->getBodyParam('developerId')[0] ?? null;
        $plugin->iconId = $request->getBodyParam('iconId')[0] ?? null;
        $plugin->packageName = $request->getBodyParam('packageName');
        $plugin->repository = $request->getBodyParam('repository');
        $plugin->name = $request->getBodyParam('name');
        $plugin->handle = $request->getBodyParam('handle');
        $plugin->price = (float)$request->getBodyParam('price');
        $plugin->renewalPrice = (float)$request->getBodyParam('renewalPrice');
        $plugin->license = $request->getBodyParam('license');
        $plugin->shortDescription = $request->getBodyParam('shortDescription');
        $plugin->longDescription = $request->getBodyParam('longDescription');
        $plugin->documentationUrl = $request->getBodyParam('documentationUrl');
        $plugin->changelogUrl = $request->getBodyParam('changelogUrl');

        $plugin->categories = Category::find()->id($request->getBodyParam('categoryIds'))->all();
        $plugin->screenshots = Asset::find()->id($request->getBodyParam('screenshotIds'))->all();

        if (!Craft::$app->getElements()->saveElement($plugin)) {
            Craft::$app->getSession()->setError('Couldn’t save plugin.');
            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin
            ]);
            return null;
        }

        Craft::$app->getSession()->setNotice('Plugin saved.');
        return $this->redirectToPostedUrl($plugin);
    }

    public function actionDelete()
    {
        $request = Craft::$app->getRequest();
        $pluginId = $request->getBodyParam('pluginId');
        $plugin = Plugin::find()->id($pluginId)->status(null)->one();

        if (!$plugin) {
            throw new NotFoundHttpException('Plugin not found');
        }

        if (!Craft::$app->getElements()->deleteElement($plugin)) {
            Craft::$app->getSession()->setError('Couldn’t delete plugin.');
            return null;
        }

        Craft::$app->getSession()->setNotice('Plugin deleted.');
        return $this->redirectToPostedUrl($plugin);
    }

    /**
     * Returns a plugin’s README contents
     *
     * @param Repo   $api
     * @param string $owner
     * @param string $repo
     *
     * @return string|null
     */
    private function _getReadme(Repo $api, string $owner, string $repo)
    {
        try {
            return $api->readme($owner, $repo);
        } catch (RuntimeException $e) {
            return null;
        }
    }

    /**
     * Returns a plugin’s icon.
     *
     * @param Repo        $api
     * @param string      $owner
     * @param string      $repo
     * @param string|null $ref
     * @param array       $config
     * @param string|null $handle
     * @param string|null $name
     *
     * @return Asset|null
     */
    private function _getIcon(Repo $api, string $owner, string $repo, string $ref = null, array $config, string $handle = null, string $name = null)
    {
        // Make sure the plugin has a handle
        if (!$handle) {
            return null;
        }

        // Kebab-case it
        if (strtolower($handle) !== $handle) {
            $handle = preg_replace('/\-{2,}/', '-', Inflector::camel2id($handle));
        }

        // See if we already happen to have an icon for this handle
        $filename = $handle.'.svg';
        if ($icon = Asset::find()->volume('icons')->filename($filename)->one()) {
            return $icon;
        }

        // Make sure there are some autoload paths
        if (!isset($config['autoload']['psr-4'])) {
            return null;
        }

        // Loop through the autoload paths and look for icons
        $basePath = isset($config['extra']['basePath']) && strpos($config['extra']['basePath'], '@') === 0 ? rtrim($config['extra']['basePath']) : null;
        $pluginClass = isset($config['extra']['class']) ? ltrim($config['extra']['class'], '\\') : null;

        foreach ($config['autoload']['psr-4'] as $namespace => $path) {
            $namespace = trim($namespace, '\\');
            $path = rtrim($path, '/');

            // If basePath is defined, we only care about namespace path(s) that include it
            if ($basePath !== null) {
                $alias = '@'.str_replace('\\', '/', $namespace);
                if (strpos($basePath.'/', $alias.'/') === 0) {
                    $testPath = $path.substr($config['extra']['basePath'], strlen($alias));
                    if ($icon = $this->_getIconInPath($api, $owner, $repo, $ref, $handle, $name, $testPath)) {
                        return $icon;
                    };
                }
            } // If the plugin class is defined, we only care about namespace path(s) that include its directory
            else if ($pluginClass !== null) {
                if (strpos($pluginClass, $namespace.'\\') === 0) {
                    $subPath = str_replace('\\', '/', substr($pluginClass, strlen($namespace) + 1));
                    $testPath = $path.(dirname($subPath) !== '.' ? '/'.dirname($subPath) : '');
                    if ($icon = $this->_getIconInPath($api, $owner, $repo, $ref, $handle, $name, $testPath)) {
                        return $icon;
                    };
                }
            } else {
                // Only include all autoload paths if basePath and pluginClass are not set
                if ($icon = $this->_getIconInPath($api, $owner, $repo, $ref, $handle, $name, $path)) {
                    return $icon;
                };
            }
        }

        return null;
    }

    /**
     * Looks for a plugin’s icon within a specific path
     *
     * @param Repo        $api
     * @param string      $owner
     * @param string      $repo
     * @param string|null $ref
     * @param string      $handle
     * @param string|null $name
     *
     * @param string      $testPath
     *
     * @return Asset|null
     * @throws Exception if the icon asset can't be saved
     */
    private function _getIconInPath(Repo $api, string $owner, string $repo, string $ref = null, string $handle, string $name = null, string $testPath)
    {
        try {
            $response = $api->contents()->show($owner, $repo, $testPath.'/icon.svg', $ref);
        } catch (RuntimeException $e) {
            return null;
        }

        // Decode and save it
        $contents = base64_decode($response['content']);
        $tempPath = Craft::$app->getPath()->getTempPath()."/icon-{$handle}-".StringHelper::randomString().'.svg';
        FileHelper::writeToFile($tempPath, $contents);

        // Save as an asset
        $volumesService = Craft::$app->getVolumes();
        $volume = $volumesService->getVolumeByHandle('icons');
        $folderId = $volumesService->ensureTopFolder($volume);

        $icon = new Asset([
            'title' => $name,
            'tempFilePath' => $tempPath,
            'newLocation' => "{folder:{$folderId}}{$handle}.svg",
        ]);

        if (!Craft::$app->getElements()->saveElement($icon, false)) {
            throw new Exception('Unable to save icon asset: '.implode(',', $icon->getFirstErrors()));
        }

        return $icon;
    }
}
