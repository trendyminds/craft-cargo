<?php

use craft\elements\Entry;
use markhuot\craftpest\factories\Entry as EntryFactory;
use trendyminds\cargo\Cargo;
use trendyminds\cargo\models\Settings;

// Before each test reset the plugin settings
beforeEach(function () {
    $settings = (new Settings())->toArray();
    Cargo::getInstance()->setSettings($settings);
});

it('adds an update job to the queue when a new entry is created', function () {
    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [
                'criteria' => [],
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                    ];
                },
            ];
        },
    ];

    EntryFactory::factory()->create();

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->isNotEmpty();

    expect($updating)->toBeTrue();
});
