<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('hashsandsalt/kirby3-yasumi', [

  'siteMethods' => [
    'holidays' => function ($country, $year) {
      $holidays = Yasumi\Yasumi::create($country, $year);
      return $holidays;
    }
  ],

]);
