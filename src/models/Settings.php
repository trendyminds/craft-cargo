<?php

namespace trendyminds\cargo\models;

use craft\base\Model;

/**
 * Cargo settings
 */
class Settings extends Model
{
    /**
     * Whether Cargo should sync data to Algolia when an entry is updated
     * It is possible to still run sync commands via the console or utility area
     *
     * @var bool
     */
    public bool $enabled = true;

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
