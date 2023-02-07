<?php

use markhuot\craftpest\factories\Entry as EntryFactory;
use trendyminds\cargo\Cargo;
use trendyminds\cargo\models\Settings;

// Before each test reset the plugin settings
beforeEach(function () {
    $settings = (new Settings())->toArray();
    Cargo::getInstance()->setSettings($settings);
});

it('ensures the queue can be wiped', function () {
    $entry = EntryFactory::factory()->create();
    expect(Craft::$app->getQueue()->getJobInfo())->not->toBeEmpty();

    // Clear the existing queue
    Craft::$app->getQueue()->releaseAll();
    expect(Craft::$app->getQueue()->getJobInfo())->toBeEmpty();
});

it('adds a single update job to the queue when a new entry is created', function () {
    EntryFactory::factory()->create();

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(1);
});

it('adds a single update job to the queue when an entry is updated', function () {
    $entry = EntryFactory::factory()->create();
    Craft::$app->getQueue()->releaseAll();

    $entry->title = 'New title';
    Craft::$app->elements->saveElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(1);
});

it('does not send an update when an entry is unchanged', function () {
    $entry = EntryFactory::factory()->create();
    Craft::$app->getQueue()->releaseAll();
    Craft::$app->elements->saveElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(0);
});

it('does not send an update when an entry is first created as disabled', function () {
    EntryFactory::factory()->enabled(false)->create();
    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    $deleting = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Deleting record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(0);
    expect($deleting)->toHaveCount(0);
});

it('adds a single delete job to the queue when an entry is closed', function () {
    $entry = EntryFactory::factory()->create();
    Craft::$app->getQueue()->releaseAll();

    $entry->enabled = false;
    Craft::$app->elements->saveElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    $deleting = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Deleting record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(0);
    expect($deleting)->toHaveCount(1);
});

it('adds a single delete job to the queue when an entry is deleted', function () {
    $entry = EntryFactory::factory()->create();
    Craft::$app->getQueue()->releaseAll();
    Craft::$app->elements->deleteElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    $deleting = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Deleting record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(0);
    expect($deleting)->toHaveCount(1);
});

it('does not send an update when saving an already-disabled entry', function () {
    $entry = EntryFactory::factory()->enabled(false)->create();
    Craft::$app->getQueue()->releaseAll();

    $entry->title = 'New title';
    Craft::$app->elements->saveElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    $deleting = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Deleting record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(0);
    expect($deleting)->toHaveCount(0);
});

it('sends a single update when duplicating an entry', function () {
    $entry = EntryFactory::factory()->create();
    Craft::$app->getQueue()->releaseAll();
    Craft::$app->elements->duplicateElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(1);
});

it('does not send an update when duplicating a disabled entry', function () {
    $entry = EntryFactory::factory()->enabled(false)->create();
    Craft::$app->getQueue()->releaseAll();
    Craft::$app->elements->duplicateElement($entry);

    $updating = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Updating record')
        ->values()
        ->toArray();

    $deleting = collect(Craft::$app->getQueue()->getJobInfo())
        ->filter(fn ($job) => $job['description'] === 'Deleting record')
        ->values()
        ->toArray();

    expect($updating)->toHaveCount(0);
    expect($deleting)->toHaveCount(0);
});
