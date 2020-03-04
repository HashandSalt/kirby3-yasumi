<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('hashsandsalt/kirby3-yasumi', [

    'siteMethods' => [
        'holidays' => function ($country, $year, $locale = 'en_US') {
            $holidays = Yasumi\Yasumi::create($country, $year, $locale);
            return $holidays;
        }
    ],

    'collections' => [
        'holidays' => function ($site) {
            $country = option('hashandsalt.kirby3-yasumi.country', 'Germany/LowerSaxony');
            $year = option('hashandsalt.kirby3-yasumi.year', date('Y'));
            $locale = option('hashandsalt.kirby3-yasumi.locale', 'de_DE');

            foreach ($site->holidays($country, $year, $locale) as $holiday) {
                $date = new DateTime($holiday);

                $pages[] = [
                    'slug' => $holiday->shortName,
                    'num' => $date->format('Ymd'),
                    'model' => 'holiday',
                    'content' => [
                        'title' => $holiday->getName(),
                        'date' => $date->format('Y-m-d'),
                        'type' => $holiday->getType()
                    ]
                ];
            }

            return Pages::factory($pages);
        }
    ]

]);
