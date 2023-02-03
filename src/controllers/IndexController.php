<?php

namespace trendyminds\cargo\controllers;

use Craft;
use craft\web\Controller;
use trendyminds\cargo\jobs\UpdateIndex;
use yii\web\Response;

/**
 * Index controller
 */
class IndexController extends Controller
{
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionUpdate(string $indexName, ?bool $refresh = false): Response
    {
        // Push an update job to the queue
        Craft::$app->getQueue()->push(
            new UpdateIndex([
                'indexName' => $indexName,
                'refresh' => $refresh,
            ])
        );

        // Register a flash message to the screen
        Craft::$app->getSession()->setNotice(
            $refresh
                ? "Refreshing \"{$indexName}\" index"
                : "Updating \"{$indexName}\" index"
        );

        // Redirect back to the utilities page
        return $this->redirect('utilities/cargo');
    }
}
