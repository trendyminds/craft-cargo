<?php

namespace trendyminds\cargo\jobs;

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
        // Get the entry
        $entry = Entry::find()->id($this->entryId)->all();

        // Loop through each index it is used within
        foreach (Cargo::getInstance()->entry->indices($this->entryId) as $index) {
            $indexName = $index->indexName;
            $data = $index->transform($entry);

            // Send the transformed data to be reindexed
            Cargo::getInstance()->algolia->update($indexName, $data);
        }

        // Find any related entries
        $related = Entry::find()->relatedTo($this->entryId)->all();

        // Loop through each related entry and the indices it is used in
        foreach ($related as $entry) {
            foreach (Cargo::getInstance()->entry->indices($entry->id) as $index) {
                $indexName = $index->indexName;
                $data = $index->transform([$entry]);

                // Send the transformed data to be reindexed
                Cargo::getInstance()->algolia->update($indexName, $data);
            }
        }
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
