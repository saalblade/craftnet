<?php

namespace craftnet\console\controllers;

use Craft;
use craft\elements\User;
use craftnet\developers\UserBehavior;
use craftnet\Module;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Manages Craft licenses.
 *
 * @property Module $module
 */
class CmsLicensesController extends Controller
{
    /**
     * Claims licenses for the user with the given username or email.
     *
     * @param string $username
     * @return int
     */
    public function actionClaim(string $username): int
    {
        /** @var User|UserBehavior|null $user */
        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($username);

        if (!$user) {
            $this->stderr('Invalid username or email'.PHP_EOL, Console::FG_RED);
            return 1;
        }

        $num = $this->module->getCmsLicenseManager()->claimLicenses($user);
        $this->stdout("{$num} licenses claimed".PHP_EOL, Console::FG_GREEN);
        return 0;
    }
}
