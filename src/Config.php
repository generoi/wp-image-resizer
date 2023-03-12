<?php

namespace GeneroWP\ImageResizer;

use Exception;

use function Env\env;

class Config
{
    public const FILTER_DEFAULT_SETTINGS = 'wp-image-resizer/config/default';
    public const FILTER_PLACEHOLDER_IMAGE = 'wp-image-resizer/config/placeholder';
    public const FILTER_BREAKPOINTS = 'wp-image-resizer/config/breakpoints';

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

    public static function placeholderImage(): string
    {
        return apply_filters(
            self::FILTER_PLACEHOLDER_IMAGE,
            'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='
        );
    }

    /**
     * @return int[]
     */
    public static function breakpoints(): array
    {
        return apply_filters(self::FILTER_BREAKPOINTS, [
            ...range(50, 500, 50),
            ...range(600, 2000, 100),
        ]);
    }

    /**
     * @return array<string,int|string>
     */
    public static function defaultSettings(): array
    {
        return apply_filters(self::FILTER_DEFAULT_SETTINGS, [
            'quality' => 82,
            'fit' => 'scale-down',
            'format' => 'auto',
        ]);
    }
}
