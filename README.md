# 📦 Cargo

Ship your Craft entries to Algolia!

## Requirements

This plugin requires Craft CMS 4.3.6.1 or later, and PHP 8.0.2 or later.

## Installation

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require trendyminds/craft-cargo

# tell Craft to install the plugin
./craft plugin/install cargo
```

## Configuration

You configure Cargo by copying `src/cargo.php` into your `config/` directory. A configuration example:

```php
<?php

return [
    /**
     * Whether Cargo should sync data to Algolia when an entry is updated
     * It is possible to still run sync commands via the console or utility area
     */
    'enabled' => true,

    /**
     * The Algolia application ID and secret.
     */
    'algolia' => [
        'id' => '',
        'secret' => '',
    ],

    /**
     * The size of the batch used when updating Algolia indices.
     * If you are having issues with timeouts, try lowering this number.
     */
    'batchSize' => 100,

    /**
     * Your Algolia indices and the Craft entries that should be synced to them.
     */
    'indices' => [
        'my_index_name' => function () {
            return [
                'criteria' => ['section' => 'pages'],
                'transformer' => function (craft\elements\Entry $entry) {
                    return [
                        // Your index must include 'id' and be the ID of the entry
                        'id' => $entry->id,
                        'title' => $entry->title,
                    ];
                },
            ];
        },
    ],
];
```

### ⚠️ Entry IDs as the `objectId`

Algolia requires an `objectId` and you **must** use the entry's ID for this value. Because all Cargo events happen in the queue we may not have access to a deleted entry when the queue runs. We are able, however, to reference the delete entry's ID and remove any record from your Algolia index matching that ID.
