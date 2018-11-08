<?php

namespace Awescode\GoogleSheets\Facades;

use Illuminate\Support\Facades\Facade;

class GoogleSheets extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'googlesheets';
    }
}
