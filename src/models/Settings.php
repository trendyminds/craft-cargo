<?php

namespace trendyminds\cargo\models;

use craft\base\Model;

/**
 * Cargo settings
 */
class Settings extends Model
{
    /**
     * Your Algolia credentials
     *
     * @var array
     */
    public array $algolia = [
        'id' => '',
        'secret' => '',
    ];

    /**
     * The batch size to use when syncing your indices
     *
     * @var int
     */
    public int $batchSize = 100;

    /**
     * Your indices to sync using your specified driver
     *
     * @var array
     */
    public array $indices = [];
}
