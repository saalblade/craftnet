<?php

namespace craftnet\console\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Transaction as TransactionRecord;
use craft\db\Query;
use craft\elements\User;
use craftnet\cms\CmsEdition;
use craftnet\cms\CmsLicense;
use craftnet\developers\UserBehavior;
use craftnet\errors\InaccessibleFundsException;
use craftnet\errors\LicenseNotFoundException;
use craftnet\Module;
use craftnet\plugins\PluginEdition;
use craftnet\plugins\PluginLicense;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Transfers money owed to plugin developers who have accumulated a balance.
 *
 * @property Module $module
 */
class SettleController extends Controller
{
    /**
     * Outputs a list of accounts that have a balance owed.
     *
     * @return int
     */
    public function actionIndex(): int
    {
        // Find the accounts we owe money to
        $accounts = User::find()
            ->select(['username', 'email', 'balance', 'stripeAccount'])
            ->andWhere(['>', 'balance', 0])
            ->asArray()
            ->all();

        if (empty($accounts)) {
            $this->stdout('No accounts with a balance owed.' . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $tableData = [];
        $formatter = Craft::$app->getFormatter();
        foreach ($accounts as $account) {
            $tableData[] = [
                $account['username'],
                $account['email'],
                $formatter->asCurrency($account['balance'], 'USD'),
                $account['stripeAccount']
            ];
        }
        $this->stdout('Accounts with a balance owed:' . PHP_EOL . PHP_EOL);
        $this->table(['Username', 'Email', 'Balance', 'Stripe Account'], $tableData);
        $this->stdout(PHP_EOL);

        if ($this->confirm('Settle up now?')) {
            return $this->run('up');
        }

        return ExitCode::OK;
    }

    /**
     * Settles up with an account.
     *
     * @param string|null $username
     * @return int
     */
    public function actionUp(string $username = null): int
    {
        if ($username === null) {
            $username = $this->prompt('Username:', [
                'required' => true,
                'validator' => function($input, &$error) {
                    if (!User::find()->username($input)->exists()) {
                        $error = 'No account exists with that username.';
                        return false;
                    }
                    return true;
                }
            ]);
        }

        $account = User::find()
            ->username($username)
            ->one();

        if (!$account) {
            $this->stderr("No account exists with the username '{$username}'." . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        /** @var User|UserBehavior $account */
        $fm = $account->getFundsManager();
        $balance = $fm->getBalance();

        if ($balance <= 0) {
            $this->stderr("Account '{$username}' doesn't have a balanced owed." . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout('Transferring ' . Craft::$app->getFormatter()->asCurrency($fm->getBalance(), 'USD') . ' to the developer ... ', Console::FG_YELLOW);

        $fm->settleUp();

        $this->stdout('done' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }


    protected function table(array $headers, array $data)
    {
        // Figure out the max col sizes
        $cellSizes = [];
        foreach (array_merge($data, array($headers)) as $row) {
            foreach ($row as $i => $cell) {
                $cellSizes[$i][] = strlen($cell);
            }
        }

        $maxCellSizes = [];
        foreach ($cellSizes as $i => $sizes) {
            $maxCellSizes[$i] = max($sizes);
        }

        $this->tableRow($headers, $maxCellSizes);
        $this->tableRow([], $maxCellSizes, '-');
        foreach ($data as $row) {
            $this->tableRow($row, $maxCellSizes);
        }
    }

    protected function tableRow(array $row, array $sizes, $pad = ' ')
    {
        foreach ($sizes as $i => $size) {
            if ($i !== 0) {
                $this->stdout(' | ');
            }
            $this->stdout(str_pad($row[$i] ?? '', $sizes[$i], $pad));
        }

        $this->stdout(PHP_EOL);
    }
}
