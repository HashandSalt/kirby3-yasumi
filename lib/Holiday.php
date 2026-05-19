<?php

namespace Hashsandsalt\Yasumi;

use Kirby\Content\Content;
use Kirby\Content\Field;
use Kirby\Toolkit\Str;
use Yasumi\Holiday as YasumiHoliday;

/**
 * Holiday object
 */
class Holiday
{
    private YasumiHoliday $data;
    private Content $content;
    private ?string $id = null;
    private ?string $slug = null;

    public function __construct(YasumiHoliday $holiday)
    {
        $this->data = $holiday;
        $this->content = new Content([
            'title' => $this->title(),
            'date' => $holiday->format('Y-m-d'),
            'type' => $holiday->getType()
        ]);
    }

    /**
     * Returns the title, translated if the site is multilingual.
     */
    public function title()
    {
        $kirby = kirby();

        if ($kirby->multilang()) {
            $value = $this->data->getName([$kirby->language(), 'en_US']);
        } else {
            $value = $this->data->getName();
        }

        return new Field(parent: null, key: 'title', value: $value);
    }

    /**
     * Returns the slug.
     */
    public function slug()
    {
        if ($this->slug === null) {
            $this->slug = Str::kebab($this->data->getKey());
        }

        return $this->slug;
    }

    /**
     * Returns the id.
     */
    public function id()
    {
        if ($this->id === null) {
            $this->id = 'holidays/' . $this->slug();
        }

        return $this->id;
    }

    /**
     * Returns the content object.
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Checks the holiday against a given type, defaults to `official`.
     *
     * @param $type The holiday type to compare with.
     */
    public function isHoliday(HolidayTypes $type = HolidayTypes::Official): bool
    {
        return HolidayTypes::tryFrom($this->type()->value()) === $type;
    }

    /**
     * Converts the Holiday object to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'content' => $this->content()->toArray()
        ];
    }

    /**
     * Get content fields via magic methods.
     *
     * @param $name The field name.
     * @param $arguments The field arguments.
     */
    public function __call($name, $arguments)
    {
        return $this->content->{$name}($arguments);
    }

    /**
     * Converts the Holiday object to string by returning the title.
     */
    public function __toString()
    {
        return $this->content->title()->value();
    }

    /**
     * Returns debug information for the Holiday object.
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
