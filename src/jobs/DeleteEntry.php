<?php

namespace trendyminds\cargo\jobs;

use craft\elements\Entry;
use craft\queue\BaseJob;
use trendyminds\cargo\Cargo;

class DeleteEntry extends BaseJob
{
    public int $entryId;

    /**
     * Execute the queued job
     */
    public function execute($queue): void
    {
        collect(Cargo::getInstance()->getSettings()->indices)
            ->keys()
            ->each(function ($indexName) {
                // Loop through all of the indices and remove any records with the deleted ID
                Cargo::getInstance()->algolia->delete($indexName, [$this->entryId]);
            });

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
        return 'Deleting record';
    }
}
