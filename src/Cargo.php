<?php

namespace trendyminds\cargo;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Utilities;
use trendyminds\cargo\jobs\DeleteEntry;
use trendyminds\cargo\jobs\UpdateEntry;
use trendyminds\cargo\models\Settings;
use trendyminds\cargo\utilities\CargoUtility;
use yii\base\Event;

/**
 * Cargo plugin
 *
 * @author TrendyMinds
 * @copyright TrendyMinds
 * @license MIT
 *
 * @property-read \trendyminds\cargo\services\Algolia $algolia
 * @property-read \trendyminds\cargo\services\Index $index
 * @property-read \trendyminds\cargo\services\Entry $entry
 */
class Cargo extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                'algolia' => \trendyminds\cargo\services\Algolia::class,
                'index' => \trendyminds\cargo\services\Index::class,
                'entry' => \trendyminds\cargo\services\Entry::class,
            ],
        ];
    }

    public function init()
    {
        parent::init();
        Craft::setAlias('@trendyminds/cargo', $this->getBasePath());

        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    private function attachEventHandlers(): void
    {
        // Register the Cargo utility
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = CargoUtility::class;
            }
        );

        /**
         * Ony listen for save/update/delete events when the 'enabled' setting
         * is set to true. Otherwise, skip these.
         */
        if (! Cargo::getInstance()->getSettings()->enabled) {
            return;
        }

        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function (Event $event) {
                if ($entry = $this->entry->hasChanges($event)) {
                    if ($entry->status === 'live') {
                        Craft::$app->getQueue()->push(
                            new UpdateEntry(['entryId' => $entry->id])
                        );
                    }

                    if (! $entry->firstSave && $entry->status !== 'live') {
                        Craft::$app->getQueue()->push(
                            new DeleteEntry(['entryId' => $entry->id])
                        );
                    }
                }
            }
        );

        // Watch for updates to entries
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_MOVE_IN_STRUCTURE,
            function (Event $event) {
                if ($entry = $this->entry->hasChanges($event)) {
                    if ($entry->status === 'live') {
                        Craft::$app->getQueue()->push(
                            new UpdateEntry(['entryId' => $entry->id])
                        );
                    }
                }
            }
        );

        // Watch for deletes to entries
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_DELETE,
            function (Event $event) {
                if ($entry = $this->entry->hasChanges($event)) {
                    Craft::$app->getQueue()->push(
                        new DeleteEntry(['entryId' => $entry->id])
                    );
                }
            }
        );
    }
}
