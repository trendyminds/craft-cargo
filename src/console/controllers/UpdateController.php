<?php

namespace trendyminds\cargo\console\controllers;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use trendyminds\cargo\Cargo;
use trendyminds\cargo\jobs\UpdateIndex;
use yii\console\ExitCode;

class UpdateController extends Controller
{
    public ?string $indexName = null;

    public ?bool $refresh = false;

    public function options($actionID): array
    {
        return ['indexName', 'refresh'];
    }

    /**
     * cargo/update command
     */
    public function actionIndex(): int
    {
        // Check that the index name is set
        if (! $this->indexName) {
            $this->stderr('Error: You must specify an index name for updating/refreshing'.PHP_EOL, Console::FG_RED);
            $this->stderr('php craft cargo/update --indexName=foo'.PHP_EOL);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Check that the index name exists
        if (! Cargo::getInstance()->index->get($this->indexName)) {
            $this->stderr('Error: The index name you specified does not exist'.PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Push an update job to the queue
        Craft::$app->getQueue()->push(
            new UpdateIndex([
                'indexName' => $this->indexName,
                'refresh' => $this->refresh,
            ])
        );

        // Display a message to the console confirming the update is queued
        $message = $this->refresh
            ? "Refreshing \"{$this->indexName}\" index"
            : "Updating \"{$this->indexName}\" index";

        $this->stdout($message.PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
