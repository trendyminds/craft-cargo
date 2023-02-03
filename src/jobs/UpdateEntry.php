<?php

namespace trendyminds\cargo\jobs;

use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\queue\BaseJob;
use trendyminds\cargo\Cargo;

class UpdateEntry extends BaseJob
{
    public int $entryId;

    /**
     * Execute the queued job
     */
    public function execute($queue): void
    {
        ray()->clearAll();

        $entry = Entry::find()->id($this->entryId)->one();
        $relatedEntries = Entry::find()->relatedTo($entry)->all();

        // Loop through all of the entry queries in our config to see if the entry matches any of them
        $yo = collect(Cargo::getInstance()->getSettings()->indices)
            ->filter(function ($data, $indexName) use ($entry) {
                /** @var EntryQuery $query */
                $query = Cargo::getInstance()->getIndexData($indexName)->query;

                return $query->id($entry->id)->exists();
            })
            ->each(function ($data, $indexName) use ($entry) {
                /** @var EntryQuery $query */
                $query = Cargo::getInstance()->getIndexData($indexName)->query;
                ray($query->id($entry->id)->one());
            });

        // ray(
        // 	$entry->id,
        // 	$relatedEntries
        // );
    }

    /**
     * Description for the control panel rendering of the job progress
     *
     * @return string|null
     */
    protected function defaultDescription(): ?string
    {
        return 'Updating record';
    }
}
