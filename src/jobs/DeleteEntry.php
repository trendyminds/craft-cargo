<?php

namespace trendyminds\cargo\jobs;

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
