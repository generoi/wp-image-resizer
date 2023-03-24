<?php

namespace GeneroWP\ImageResizer;

use Exception;
use GeneroWP\ImageResizer\Contracts\Resizer;
use GeneroWP\ImageResizer\Resizers\Cloudflare;

use function Env\env;

class Config
{
    public const FILTER_DEFAULT_SETTINGS = 'wp-image-resizer/config/default';
    public const FILTER_BREAKPOINTS = 'wp-image-resizer/config/breakpoints';
    public const FILTER_RESIZER = 'wp-image-resizer/config/resizer';

    public static function zone(): string
    {
        $zone = env('IMAGERESIZER_ZONE');
        if (! is_string($zone)) {
            throw new Exception('IMAGERESIZER_ZONE environment variable missing');
        }

        return $zone;
    }

    public static function enabled(): bool
    {
        return ! is_admin() && ! env('IMAGERESIZER_DISABLED');
    }

    /**
     * @return int[]
     */
    public static function breakpoints(): array
    {
        return apply_filters(self::FILTER_BREAKPOINTS, [
            ...range(50, 200, 50),
            ...range(200, 1000, 100),
            ...range(1000, 2000, 200),
        ]);
    }

    /**
     * @return array<string,int|string>
     */
    public static function defaultSettings(): array
    {
        return apply_filters(
            self::FILTER_DEFAULT_SETTINGS,
            self::resizer()->defaultSettings()
        );
    }

    public static function resizer(): Resizer
    {
        static $resizer = null;
        if (! $resizer) {
            $resizer = apply_filters(self::FILTER_RESIZER, new Cloudflare());
        }

        return $resizer;
    }
}
