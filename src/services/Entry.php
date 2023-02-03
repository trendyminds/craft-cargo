<?php

namespace trendyminds\cargo\services;

use craft\helpers\ElementHelper;
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

        return null;
    }
}
