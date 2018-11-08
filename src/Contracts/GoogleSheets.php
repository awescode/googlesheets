<?php

namespace Awescode\GoogleSheets\Contracts;

interface GoogleSheets
{
    public function parse(string $key, string $sheet = ''): string;
}
