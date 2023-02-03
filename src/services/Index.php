<?php

namespace trendyminds\cargo\services;

use trendyminds\cargo\Cargo;
use trendyminds\cargo\models\IndexQuery;
use yii\base\Component;

class Index extends Component
{
    public function get(string $indexName): ?IndexQuery
    {
        if (! array_key_exists($indexName, Cargo::getInstance()->getSettings()->indices)) {
            return null;
        }

        return new IndexQuery(['indexName' => $indexName]);
    }
}
