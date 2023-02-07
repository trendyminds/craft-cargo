<?php

/**
 * Do not edit this file directly, instead copy it as cargo.php and place it
 * into your config directory.
 */

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
                // Entry::class is the only supported element type at this time
                'elementType' => craft\elements\Entry::class,
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
