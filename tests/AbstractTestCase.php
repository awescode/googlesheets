<?php

namespace Awescode\GoogleSheets\Tests;

use Awescode\GoogleSheets\GoogleSheetsServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class AbstractTestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            GoogleSheetsServiceProvider::class,
        ];
    }
}
