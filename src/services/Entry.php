<?php

namespace trendyminds\cargo\services;

use craft\helpers\ElementHelper;
use trendyminds\cargo\Cargo;
use yii\base\Component;
use yii\base\Event;

/**
 * Entry
 */
class Entry extends Component
{
    public function hasChanges(Event $event): ?\craft\elements\Entry
    {
        /** @var \craft\elements\Entry $entry */
        $entry = $event->sender;

        if (! $entry instanceof \craft\elements\Entry) {
            return null;
        }

        if ($entry->firstSave) {
            return $entry;
        }

        if (ElementHelper::isDraft($entry)) {
            return null;
        }

        if (ElementHelper::rootElement($entry)->isProvisionalDraft) {
            return null;
        }

        if (ElementHelper::isRevision($entry)) {
            return null;
        }

        if ($entry->getDirtyAttributes()) {
            return $entry;
        }

        $original = $entry->duplicateOf;

        if ($original !== null) {
            if (! empty($original->getModifiedAttributes()) || ! empty($original->getModifiedFields())) {
                return $entry;
            }
        }

        if ($event->name === 'afterDelete') {
            return $entry;
        }

        if ($event->name === 'afterMoveInStructure') {
            return $entry;
        }

        return null;
    }

    /**
     * Returns the indices that contain the given entry ID
     */
    public function indices(int $entryId): array
    {
        return collect(Cargo::getInstance()->getSettings()->indices)
            ->keys()
            ->filter(function ($indexName) use ($entryId) {
                $index = Cargo::getInstance()->index->get($indexName);

                return $index->query()->id($entryId)->exists();
            })
            ->values()
            ->map(fn ($indexName) => Cargo::getInstance()->index->get($indexName))
            ->toArray();
    }
}
