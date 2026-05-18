<?php

namespace Hashsandsalt\Yasumi;

use Kirby\Content\Content;
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

    public function title()
    {
        $kirby = kirby();

        if ($kirby->multilang()) {
            return $this->data->getName([$kirby->language(), 'en_US']);
        }

        return $this->data->getName();
    }

    public function slug()
    {
        if ($this->slug !== null) {
            return $this->slug;
        }

        return $this->slug = Str::kebab($this->data->getKey());
    }

    public function id()
    {
        if ($this->id !== null) {
            return $this->id;
        }

        return $this->id = 'holidays/' . $this->slug();
    }

    public function content()
    {
        return $this->content;
    }

    public function __call($name, $arguments)
    {
        return $this->content->{$name}($arguments);
    }

    public function __toString()
    {
        return $this->content->title()->value();
    }

    public function __debugInfo(): array
    {
        return [
            'id' => $this->id(),
            'slug' => $this->slug(),
            'content' => $this->content()->toArray()
        ];
    }
}
