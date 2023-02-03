<?php

namespace trendyminds\cargo\models;

use Craft;
use craft\base\Model;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use trendyminds\cargo\Cargo;

class IndexQuery extends Model
{
    public string $indexName;

    public function query(): EntryQuery
    {
        $index = Cargo::getInstance()->getSettings()->indices[$this->indexName]();
        $elementType = Entry::class;
        $query = $elementType::find();

        Craft::configure($query, $index['criteria'] ?? []);

        return $query;
    }

    public function transform(array $entries): array
    {
        $index = Cargo::getInstance()->getSettings()->indices[$this->indexName]();

        // Iterate over the elements and implement the transformer set by the user
        $resource = new Collection($entries, $index['transformer']);
        $data = (new Manager())->createData($resource)->toArray()['data'];

        // Skip over any records that are empty
        $data = collect($data)->filter()->values()->toArray();

        return $data;
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['indexName'], 'required'],
        ]);
    }
}
