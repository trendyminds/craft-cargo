<?php

namespace trendyminds\cargo\utilities;

use Craft;
use craft\base\Utility;
use trendyminds\cargo\Cargo;

/**
 * Cargo utility
 */
class CargoUtility extends Utility
{
    public static function displayName(): string
    {
        return 'Cargo';
    }

    public static function id(): string
    {
        return 'cargo';
    }

    public static function iconPath(): ?string
    {
        return null;
    }

    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('cargo/_utility', [
            'indices' => collect(Cargo::getInstance()->getSettings()->indices)->keys(),
        ]);
    }
}
