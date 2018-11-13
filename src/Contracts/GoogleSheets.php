<?php

namespace Awescode\GoogleSheets\Contracts;

interface GoogleSheets
{
    public function sheets(string $key, string $sheet = ''): string;

    public function docs(string $key, $option = []): string;
}
