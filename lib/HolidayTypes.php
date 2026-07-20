<?php

namespace Hashsandsalt\Yasumi;

enum HolidayTypes: string
{
    case Official = 'official';
    case Observed = 'observed';
    case Bank = 'bank';
    case Seasonal = 'seasonal';
    case Other = 'other';
}
