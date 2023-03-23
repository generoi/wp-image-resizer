<?php

namespace GeneroWP\ImageResizer\Contracts;

interface Resizer
{
    /**
     * @param array<string,int|string> $settings
     */
    public function buildUrl(string $sourceUrl, array $settings = []): string;

    /**
     * @return array<string,int|string>
     */
    public function defaultSettings(): array;
}
