<?php

namespace Hashsandsalt\Yasumi;

use ArrayIterator;
use DateTime;
use DateTimeInterface;
use IteratorAggregate;
use Kirby\Cms\Structure;
use Traversable;
use Yasumi\Filters\OfficialHolidaysFilter;
use Yasumi\Filters\ObservedHolidaysFilter;
use Yasumi\Filters\BankHolidaysFilter;
use Yasumi\Filters\OnFilter;
use Yasumi\Filters\SeasonalHolidaysFilter;
use Yasumi\Filters\OtherHolidaysFilter;
use Yasumi\ProviderInterface;

/**
 * Holidays collection
 */
class Holidays implements IteratorAggregate
{
    private ProviderInterface $provider;
    private ?array $data = null;
    private ?string $type = null;
    private ?DateTimeInterface $start = null;
    private ?DateTimeInterface $end = null;
    private bool $equals = true;
    private ?DateTimeInterface $date = null;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Retrives and filters the holiday data.
     */
    private function getData(): void
    {
        if ($this->data !== null) {
            return;
        }

        $this->data = [];

        $provider = $this->provider;

        // If set, filter by start and end date
        if ($this->start !== null && $this->end !== null) {
            $provider = $provider->between(
                $this->start,
                $this->end,
                $this->equals
            );
        }

        // If set, filter by date
        if ($this->date !== null) {
            $iterator = $this->providerToIterator($provider);
            $provider = new OnFilter($iterator, $this->date);
        }

        // If set, filter by type
        if ($this->type !== null) {
            $iterator = $this->providerToIterator($provider);

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
        foreach ($provider as $date) {
            $holiday = new Holiday($date);

            $this->data[$holiday->id()] = $holiday;
        }
    }

    /**
     * Resolves the country provider to its iterator if it isn't a iterator already.
     *
     * @param $provider The country provider.
     */
    private function providerToIterator($provider)
    {
        // If the collection is filtered by start and end dates already,
        // the provider is an iterator already
        return method_exists($provider, 'getIterator')
            ? $provider->getIterator()
            : $provider;
    }

    /**
     * Returns the holiday iterator.
     */
    public function getIterator(): Traversable
    {
        $this->getData();

        return new ArrayIterator($this->data);
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
        // Reset data
        $this->data = null;

        // Set type filter
        $this->type = $type;

        return $this;
    }

    /**
     * Filters the collection by start and end date.
     *
     * @param $start The start date.
     * @param $end The end date.
     * @param $equals Whether to include start and end dates, defaults to true.
     */
    public function filterByRange(
        string|DateTimeInterface $start,
        string|DateTimeInterface $end,
        bool $equals = true
    ): self {
        // Reset data
        $this->data = null;

        // Set date range filter
        $this->start = $this->ensureDateTime($start);
        $this->end = $this->ensureDateTime($end);
        $this->equals = $equals;

        return $this;
    }

    /**
     * Filters the collection by date.
     *
     * @param $date The holiday date to filter the collection with.
     */
    public function filterByDate(string|DateTimeInterface $date): self
    {
        // Reset data
        $this->data = null;

        // Set date filter
        $this->date = $this->ensureDateTime($date);

        return $this;
    }

    /**
     * Ensures the date variable is a DateTime object.
     *
     * @param $date Either a date string or DateTime interface.
     */
    private function ensureDateTime(string|DateTimeInterface $date): DateTime
    {
        return is_string($date) ? new DateTime($date) : $date;
    }

    /**
     * Finds a holiday by id.
     *
     * @param $id The holiday id.
     */
    public function find(string $id): Holiday|null
    {
        $this->getData();

        return $this->data[$id] ?? null;
    }

    /**
     * Returns a list of all holiday names.
     */
    public function names(): array
    {
        return $this->provider->getHolidayNames();
    }

    /**
     * Returns a list of all holiday dates as values and their names as keys.
     */
    public function dates(): array
    {
        return $this->provider->getHolidayDates();
    }

    /**
     * Checks if the collection is empty.
     */
    public function isEmpty(): bool
    {
        $this->getData();

        return $this->data === [];
    }

    /**
     * Checks if the collection is not empty.
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Converts the Holiday collection to array.
     *
     * @param $deep Whether to convert each Holiday object to array as well, defaults to false.
     */
    public function toArray($deep = false): array
    {
        $holidays = iterator_to_array($this->getIterator());

        if ($deep === true) {
            $holidays = array_map(
                fn($holiday) => $holiday->toArray(),
                $holidays
            );
        }

        return $holidays;
    }

    /**
     * Converts the Holiday collection to a Kirby Structure object.
     */
    public function toStructure()
    {
        return new Structure($this->toArray());
    }

    /**
     * Returns debug information for the collection.
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
