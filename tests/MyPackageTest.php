<?php

namespace Awescode\GoogleSheets\Tests;

use Awescode\GoogleSheets\GoogleSheets;
use InvalidArgumentException;

class GoogleSheetsTest extends AbstractTestCase
{
    public function test_validate_method_lowerStr()
    {
		$this->assertEquals('some text', (new GoogleSheets)->lowerStr('Some Text'));
    }
}
