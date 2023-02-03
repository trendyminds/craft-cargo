<?php

namespace trendyminds\cargo\services;

use Algolia\AlgoliaSearch\SearchClient;
use trendyminds\cargo\Cargo;
use yii\base\Component;

/**
 * Algolia
 */
class Algolia extends Component
{
    public function update(string $indexName, array $data)
    {
        $this::client()
            ->initIndex($indexName)
            ->saveObjects($data, [
                'objectIDKey' => 'id',
            ]);
    }

    public function refresh(string $indexName, array $data)
    {
        $this::client()
            ->initIndex($indexName)
            ->replaceAllObjects($data, [
                'objectIDKey' => 'id',
            ]);
    }

    protected static function client(): SearchClient
    {
        $settings = Cargo::getInstance()->getSettings();

        return SearchClient::create(
            $settings->algolia['id'],
            $settings->algolia['secret'],
        );
    }
}
