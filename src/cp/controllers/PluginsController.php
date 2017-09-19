<?php

namespace craftcom\cp\controllers;

use Craft;
use craft\elements\Asset;
use craft\elements\Category;
use craft\web\Controller;
use craftcom\Module;
use craftcom\plugins\Plugin;
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
}
