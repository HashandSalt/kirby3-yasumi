# Kirby 3 Yasumi Plugin

Get a list of official public holidays and the dates they fall on for any country and year. A wrapper around the [Yasumi](https://azuyalabs.github.io/yasumi/) library, exposing it as a Kirby site method. Anything Yasumi can do, you should be able to chain in to the site method.

## Installation

### Manual

To use this plugin, place all the files in `site/plugins/kirby3-yasumi`.

### Git submodule

```
git submodule add https://github.com/hashandsalt/kirby3-yasumi.git site/plugins/yasumi
```

### Composer

```
composer require hashandsalt/kirby3-yasumi
```

---

## Commerical Usage

This plugin is free but if you use it in a commercial project please consider to

- [make a donation 🍻](https://paypal.me/hashandsalt?locale.x=en_GB) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/36141?link=1170)

---

## Usage

Get all the holidays for the United States in 2020 - be warned, this is a huge array containing translations. If you literally just want the dates, keep reading.

```
$site->holidays('USA', 2020);
```

If you want it to keep rolling with the current year, use date()...

```
$site->holidays('Netherlands', date("Y"));
```

## Just want the dates?

```
$holidays = $site->holidays('France', 2020);

$days = [];
foreach ($holidays->getHolidayDates() as $date) {
  $days[] = $date;
}

dump($days);
```

## Advanced

The example below uses Yasumi in combination with my [Recurr](https://github.com/HashandSalt/kirby-recurr) plugin to filter out repeating event dates that fall on public holidays. Thanks to [Bruno Meilick](https://github.com/bnomei) for the filter array code.

```
<?php

// Get set of dates from Recurr
$events = $site->recurr('2020-01-01 20:00:00', '2020-01-01 22:00:00', 'WEEKLY', ['WE', 'TH', 'FR'], '2021-01-26');

// Get Holidays for USA for Current Year
$holidays = $site->holidays('USA', date("Y"));

// Turn those holidays in to an array of just dates
$filter = [];
foreach ($holidays->getHolidayDates() as $date) {
  $filter[] = $date;
}

// Filter out those dates from events
$filteredEvents = array_filter($events, function($event) use ($filter) {
  $event = implode('//', $event);

  foreach($filter as $day) {
    // if contains then discard
    if (strstr($event, $day) !== false) {
      return false;
    }
  }
  // else keep
  return true;
});

dump($filteredEvents);

?>
```

## Collection

If you'd like to display holidays in the panel, you can use the provided `holidays` collection. It features title, date and type of all holidays. This requires [Roman Steiner's Pages Display Section](https://github.com/rasteiner/k3-pagesdisplay-section) plugin.

```
sections:
  holidays:
    type: pagesdisplay
    headline: "Holidays"
    text: "{{page.date.toDate('Y-m-d')}}: {{page.title}}"
    query: kirby.collection('holidays')
```

Country, year and used locale can be set in the configuration:

```php
<?php

return [
    'hashandsalt.yasumi' => [
        'country' => 'Germany/LowerSaxony',
        'year' => '2020',
        'locale' => 'de_DE'
    ]
]
```
