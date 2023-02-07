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

        // If the entry was first created
        if ($entry->firstSave) {
            return $entry;
        }

        // Exclude drafts and revisions
        if (ElementHelper::isDraft($entry)) {
            return null;
        }

        if (ElementHelper::rootElement($entry)->isProvisionalDraft) {
            return null;
        }

        if (ElementHelper::isRevision($entry)) {
            return null;
        }

        // If one of the changed attributes was the entry's status
        if (collect($entry->getDirtyAttributes())->contains('enabled')) {
            return $entry;
        }

        // If an attribute changed, but the entry is disabled
        if ($entry->getDirtyAttributes() && $entry->status !== 'live') {
            return null;
        }

        // If we have any changed attributes after our initial status checks
        if ($entry->getDirtyAttributes()) {
            return $entry;
        }

        // If we have any modified fields based on a duplicated entry
        $original = $entry->duplicateOf;

        if ($original !== null) {
            if (! empty($original->getModifiedAttributes()) || ! empty($original->getModifiedFields())) {
                return $entry;
            }
        }

        // If the entry was deleted
        if ($event->name === 'afterDelete') {
            return $entry;
        }

        // If the entry was moved within a structure
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
