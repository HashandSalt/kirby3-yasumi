<?php

namespace Hashsandsalt\Yasumi;

use ArrayIterator;
use DateTime;
use IteratorAggregate;
use Traversable;
use Yasumi\Filters\OfficialHolidaysFilter;
use Yasumi\Filters\ObservedHolidaysFilter;
use Yasumi\Filters\BankHolidaysFilter;
use Yasumi\Filters\SeasonalHolidaysFilter;
use Yasumi\Filters\OtherHolidaysFilter;
use Yasumi\ProviderInterface;

/**
 * Holidays collection
 */
class Holidays implements IteratorAggregate
{
    private ProviderInterface $provider;
    private array $data = [];
    private ?string $type = null;
    private ?DateTime $start = null;
    private ?DateTime $end = null;
    private bool $equals = true;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Retrives and filters the holiday data.
     */
    private function getData(): void
    {
        $provider = $this->provider;

        // If set, filter by start and end date
        if ($this->start !== null && $this->end !== null) {
            $provider = $provider->between(
                $this->start,
                $this->end,
                $this->equals
            );
        }

        // If set, filter by type
        if ($this->type !== null) {
            // If the collection is filtered by start and end dates already,
            // the provider is an iterator already
            $iterator = method_exists($provider, 'getIterator')
                ? $provider->getIterator()
                : $provider;

            $provider = match ($this->type) {
                'official' => new OfficialHolidaysFilter($iterator),
                'observed' => new ObservedHolidaysFilter($iterator),
                'bank' => new BankHolidaysFilter($iterator),
                'seasonal' => new SeasonalHolidaysFilter($iterator),
                'other' => new OtherHolidaysFilter($iterator),
                default => $provider
            };
        }

        // Convert Yasumi data to Holiday object
        foreach ($provider as $holiday) {
            $this->data[] = new Holiday($holiday);
        }
    }

    /**
     * Returns the holidy iterator.
     */
    public function getIterator(): Traversable
    {
        $this->getData();

        return new ArrayIterator($this->data);
    }

    /**
     * Filters the collection by start and end date.
     *
     * @param $start The start date.
     * @param $end The end date.
     * @param $equals Whether to include start and end dates, defaults to true.
     */
    public function between(
        string $start,
        string $end,
        bool $equals = true
    ): self {
        $this->start = new DateTime($start);
        $this->end = new DateTime($end);
        $this->equals = $equals;

        return $this;
    }

    /**
     * Filters the collection by holiday type. Available filters:
     *
     * - `official
     *    Holidays that are marked as public or statutory.
     * - `observed`
     *    Holidays that are not necessarily official however are being observed.
     * - `bank`
     *   A public holiday in the United Kingdom, some Commonwealth countries and
     *   some other European countries.
     * - `seasonal`
     *   Holidays that are celebrated due to its seasonal character (e.g. Halloween).
     * - `other`
     *   Holidays that fall outside any of the above type.
     *
     * @param string $type The holiday type to filter the collection with.
     */
    public function filterByType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns debug information for the collection.
     */
    public function __debugInfo(): array
    {
        return iterator_to_array($this->getIterator());
    }
}
