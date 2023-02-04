<?php

use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use markhuot\craftpest\factories\Entry as EntryFactory;
use trendyminds\cargo\Cargo;
use trendyminds\cargo\models\IndexQuery;
use trendyminds\cargo\models\Settings;

// Before each test reset the plugin settings
beforeEach(function () {
    $settings = (new Settings())->toArray();
    Cargo::getInstance()->setSettings($settings);
});

it('returns null if the index does not exist', function () {
    expect(Cargo::getInstance()->index->get('foo'))->toBeNull();
});

it('returns an index model if the index does exist', function () {
    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [];
        },
    ];

    expect(Cargo::getInstance()->index->get('foo'))
        ->toBeInstanceOf(IndexQuery::class);
});

it('returns a query', function () {
    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [
                'elementType' => Entry::class,
                'criteria' => [],
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                    ];
                },
            ];
        },
    ];

    $index = Cargo::getInstance()->index->get('foo');
    expect($index->query())->toBeInstanceOf(EntryQuery::class);
});

it('implements a default criteria', function () {
    EntryFactory::factory()->count(2)->create();
    expect(Entry::find()->all())->toHaveCount(2);

    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [
                'elementType' => Entry::class,
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                    ];
                },
            ];
        },
    ];

    $index = Cargo::getInstance()->index->get('foo');
    expect($index->query()->all())->toHaveCount(2);
});

it('implements filterable criteria', function () {
    $entries = EntryFactory::factory()->count(2)->create();
    expect(Entry::find()->all())->toHaveCount(2);

    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () use ($entries) {
            return [
                'elementType' => Entry::class,
                'criteria' => [
                    'slug' => $entries[1]->slug,
                ],
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                    ];
                },
            ];
        },
    ];

    $index = Cargo::getInstance()->index->get('foo');
    expect($index->query()->all())->toHaveCount(1);
});

it('transforms elements', function () {
    $entry = EntryFactory::factory()->create();
    expect(Entry::find()->all())->toHaveCount(1);

    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [
                'elementType' => Entry::class,
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                    ];
                },
            ];
        },
    ];

    $index = Cargo::getInstance()->index->get('foo');
    expect($index->transform(Entry::find()->all()))
        ->toBe([
            [
                'id' => $entry->id,
                'title' => $entry->title,
            ],
        ]);
});

it('can skip elements when returning an empty array in the transformer', function () {
    EntryFactory::factory()->create(['title' => 'foo']);
    EntryFactory::factory()->create(['title' => 'bar']);

    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [
                'elementType' => Entry::class,
                'transformer' => function (Entry $entry) {
                    if ($entry->title === 'foo') {
                        return [];
                    }

                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                    ];
                },
            ];
        },
    ];

    $index = Cargo::getInstance()->index->get('foo');
    expect($index->transform(Entry::find()->all()))->toHaveCount(1);
});

it('finds all indices that contain the given entry', function () {
    $entries = EntryFactory::factory()->count(10)->create();

    Cargo::getInstance()->getSettings()->indices = [
        'one' => function () use ($entries) {
            return [
                'elementType' => Entry::class,
                'criteria' => ['slug' => $entries[0]->slug],
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                    ];
                },
            ];
        },

        'two' => function () use ($entries) {
            return [
                'elementType' => Entry::class,
                'criteria' => ['slug' => $entries[1]->slug],
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                    ];
                },
            ];
        },

        'all' => function () {
            return [
                'elementType' => Entry::class,
                'criteria' => [],
                'transformer' => function (Entry $entry) {
                    return [
                        'id' => $entry->id,
                    ];
                },
            ];
        },
    ];

    $indices = Cargo::getInstance()->entry->indices($entries[0]->id);
    $indexNames = collect($indices)->pluck('indexName')->toArray();
    expect($indices)->toHaveCount(2);
    expect($indexNames)->toBe(['one', 'all']);
});

it('adds an update job to the queue when a new entry is created', function () {
    Cargo::getInstance()->getSettings()->indices = [
        'foo' => function () {
            return [
                'elementType' => Entry::class,
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
