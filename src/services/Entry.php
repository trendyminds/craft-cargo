<?php

namespace trendyminds\cargo\services;

use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use yii\base\Component;

/**
 * Entry
 */
class Entry extends Component
{
    public function hasChanges(ModelEvent $event): ?\craft\elements\Entry
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

        return null;
    }
}
