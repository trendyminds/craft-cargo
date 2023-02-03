<?php

namespace trendyminds\cargo\jobs;

use craft\queue\BaseJob;
use trendyminds\cargo\Cargo;

/**
 * Update Index queue job
 */
class UpdateIndex extends BaseJob
{
    /**
     * The name of the index to update
     *
     * @var string
     */
    public string $indexName;

    /**
     * Whether the index should be refreshed or not
     *
     * @var bool
     */
    public bool $refresh = false;

    /**
     * Execute the queued job
     */
    public function execute($queue): void
    {
        $index = Cargo::getInstance()->index->get($this->indexName);
        $batchSize = Cargo::getInstance()->getSettings()->batchSize;
        $elementsCount = $index->query()->count();
        $updated = 0;

        foreach ($index->query()->batch($batchSize) as $elements) {
            // Transform the elements into an array of data
            $data = $index->transform($elements);

            // Send the data to be updated (or refreshed) by the search engine driver
            $this->refresh
                ? Cargo::getInstance()->algolia->refresh($this->indexName, $data)
                : Cargo::getInstance()->algolia->update($this->indexName, $data);

            // Increment the progress bar
            $updated += count($elements);

            // Update the progress in Craft
            $this->setProgress($queue, $updated / $elementsCount, "$updated of $elementsCount");
        }
    }

    /**
     * Description for the control panel rendering of the job progress
     *
     * @return string|null
     */
    protected function defaultDescription(): ?string
    {
        return $this->refresh
            ? "Refreshing \"$this->indexName\" index"
            : "Updating \"$this->indexName\" index";
    }
}
