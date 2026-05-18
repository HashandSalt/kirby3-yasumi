# Kirby Yasumi

This plugin provides dates and names of holidays and other special celebrations from various countries and states for any year using the [Yasumi](https://github.com/azuyalabs/yasumi) library. It is calculation and rule-driven, avoiding the need for a comprehensive database.

## Installation

### Manual

To use this plugin, place all the files in `site/plugins/kirby-yasumi`.

### Git submodule

```
git submodule add https://github.com/hashandsalt/kirby-yasumi.git site/plugins/yasumi
```

### Composer

```
composer require hashandsalt/kirby-yasumi
```

> [!TIP]
> This plugin is free but if you use it in a commercial project please consider to [make a donation 🍻](https://paypal.me/hashandsalt?locale.x=en_GB).

## Holidays collection

The plugin provides a Holidays collection with an interface similar to Kirby's Pages object. Holidays are filtered by country and year and can be translated to a given locale. If they are not specified manually, these settings are taken from the current Kirby context automatically, using the current system language and the current year.

> [!NOTE]
> Previously, this plugin was providing the Yasumi object directly. As of version 2.0 it returns a dedicated Holidays collection. If you need to access the Yasumi object, use `$site->yasumi()`.

In the frontend, there are two ways to access the collection:

1. Using the site method: `$site->holidays('Germany', 2020, 'de_DE')`.
2. Using the global collection: `$kirby->collection('holidays')`.

If you need variable holiday information, use the site method which allows for direct customization.

If your holiday context does not change, use the global collection which can be set up via the Kirby configuration.

### Options

The Holidays collection accepts the following settings:

- `country`: The name of the country or region, e. g. `UnitedKingdom` or `Germany/LowerSaxony`. Full coverage information is available on the Yasumi website, https://www.yasumi.dev/providers/providers.html#coverage. If you don't provide an country name, it is automatically fetched from the current locale.
- `year`: The year you want to get the holidays for. This defaults to the current year.
- `locale`: The locale used to retrieve holiday names. This defaults to the current Kirby locale. If you need the holiday names in the current language of your install (whether is only uses a single language or offers multilingual content) there is no need to set this manually.

The site method accepts these options as direct arguments:

```php
<?php

// Get French holidays for the current year in the current locale
$site->holidays('France');

// Get French holidays for the current year but in German
$site->holidays('France', locale: 'de_DE');

// Get French holidays for 2020 and in English
$site->holidays('France', 2020, 'en_GB');
```

The collection can be configured globally inside `config.php` using the `hashandsalt.yasumi` key. All settings are optional, e. g. if you always want to retrieve holidays for the current year leave this setting out.

```php
<?php

return [
  'hashandsalt.yasumi' => [
    'country' => 'UnitedKingdom',
    'year' => '2020',
    'locale' => 'en_GB'
  ]
];
```

### Methods

#### `between(string $start, string $end, bool $equals)`

Filters the collection by start and end date.

- `$start`
  The start date.
- `$end`
  The end date.
- `$equals`
  Whether to include start and end dates, defaults to true.

#### `filterByType(string $type)`

Filters the collection by holiday type. Available type filters:

- `official`
  Holidays that are marked as public or statutory.
- `observed`
  Holidays that are not necessarily official however are being observed.
- `bank`
  A public holiday in the United Kingdom, some Commonwealth countries and some other European countries.
- `seasonal`
  Holidays that are celebrated due to its seasonal character (e.g. Halloween).
- `other`
  Holidays that fall outside any of the above type.

#### `find(string $id)`

Finds a holiday by id.

```php
<?php
// Find this year's International Workers' Day
$easter = $site->holidays()->find('internationalWorkersDay');
```

#### `names()`

Returns a list of all holiday names.

```php
<?php
$holidays = $site->holidays('Japan')->names();

// Display list of all Japanese holidays
echo implode(', ', $holiday);
```

#### `dates()`

Returns a list of all holiday dates as values and their names as keys.

```php
<?php
$holidays = $site->holidays('USA')->dates();

// Find all events not on a holiday
$site->find('events')->children()->filterBy('date', 'not in', $holidays);
```

#### `toArray(bool $deep)`

Converts the Holiday collection to array.

#### `toStructure()`

Converts the Holiday collection to a Kirby Structure object so it can be used as a query provider in field options.

## Holiday object

Each holiday in the Holidays collection is represented by a Holiday object. It is structured similar to a page object.

### Methods

#### `title()`

Returns the holiday title, translated if the site is multilingual.

#### `slug()`

Returns the holiday slug.

#### `id()`

Returns the holiday id.

#### `content()`

Returns the content object. All content fields can be called as magic methods on the Holiday object directly, exactly how it works for pages:

- `date()`: the holiday's date
- `type()`: the holiday type

#### `toArray()`

Converts the Holiday object to array.

## Yasumi object

For advanced usage, the Yasumi object can be accessed via a site method, using `$site->yasumi()`. For more information on how to use the library directly, please visit https://www.yasumi.dev/getting-started.html.

```php
<?php
$today = new DateTime();

// Check if date is working day
$site->yasumi()->isWorkingDay($today);

// Check if date is holiday
$site->yasumi()->isHoliday($today);

// Check if date falls on a weekend
$site->yasumi()->isWeekendDay($today);

// Get the weekday of a holiday
$site->yasumi()->whatWeekDayIs('newYearsDay');

// Get the date of a holiday
$site->yasumi()->whenIs('mothersDay');
```

You can check out the Yasumi repository for an overview of all available holidays:
https://github.com/azuyalabs/yasumi/tree/develop/src/Yasumi/data/translations

## Panel integration

In addition to the frontend methods, this plugin also provides panel integrations.

### Holiday section

A holiday section will display a list of all holiday names and dates of a country for the given year. If country or year are not defined, they are retrieved from the Kirby context.

```yml
sections:
  holidays:
    type: holidays
    country: Germany
    year: 2026
```

### Options

A Holiday collection can be converted to a Structure object which can be used as dynamic options provider:

```yml
fields:
  holidays:
    type: checkboxes
    label: Feiertage
    options:
      type: query
      query: site.holidays.toStructure
      text: '{{ item.title }}'
      value: '{{ item.slug }}'
```

## Single language istes

If you site only offers a single language, you might have to set your current language manually. You can do so by adding `Locale::setDefault()` on top of your configuration file:

```php
<?php

Locale::setDefault('de_DE');

return [];
```
