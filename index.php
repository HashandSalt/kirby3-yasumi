<?php

use Hashsandsalt\Yasumi\Holidays;
use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\Str;
use Yasumi\Yasumi;

require_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('hashsandsalt/yasumi', [
    'siteMethods' => [
        /**
         * Gets Yasumi object for the provided context.
         */
        'yasumi' => function (
            ?string $country = null,
            ?int $year = null,
            ?string $locale = null
        ) {
            // Determine current locale.
            // This equals the current language displayed by Kirby.
            if ($locale === null) {
                $locale = Locale::getDefault();
                $locale = Str::before($locale, '.');
                $locale = Str::replace($locale, '-', '_');
            }

            // Determine country context
            $country = $country ?? Locale::getDisplayRegion($locale, 'en');

            // Determine year
            $year = $year ?? date('Y');

            return Yasumi::create($country, $year, $locale);
        },
        /**
         * Create individual Holidays collection with context options.
         * If your context does not change across templates, use the global
         * Holidays collection that can be customized via the configuration.
         */
        'holidays' => function (
            ?string $country = null,
            ?int $year = null,
            ?string $locale = null
        ) {
            // Get Yasumi holiday data
            $yasumi = $this->yasumi($country, $year, $locale);

            // Create Holidays collection
            return new Holidays($yasumi);
        }
    ],
    'collections' => [
        /**
         * Global Holidays collection you can customize via the configuration.
         * If you need individual collection contexts, use the holiday site method.
         */
        'holidays' => function ($site): Holidays {
            $country = option('hashandsalt.yasumi.country');
            $year = intval(option('hashandsalt.yasumi.year'));
            $locale = option('hashandsalt.yasumi.locale');
            $yasumi = $site->yasumi($country, $year, $locale);

            return new Holidays($yasumi);
        }
    ]
]);
