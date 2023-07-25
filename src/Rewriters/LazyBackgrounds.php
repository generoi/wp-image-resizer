<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Contracts\Rewriter;

/**
 * Lazy load inline background images defined with a style attribute.
 */
class LazyBackgrounds implements Rewriter
{
    const TRANSPARENT_PIXEL = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

    public function __construct()
    {
        add_filter('the_content', [$this, 'filterStyles'], 11);
    }

    public function filterStyles(string $content): string
    {
        preg_match_all('/style="[^"]*?background-image:\s*url\([\'"]?([^)]*?[\'"]?)\)/', $content, $matches);

        $attributes = $matches[0];
        $urls = $matches[1];

        foreach ($attributes as $idx => $attribute) {
            $url = $urls[$idx];

            $newAttribute = sprintf(
                'data-background-image="%s" %s',
                $url,
                str_replace($url, self::TRANSPARENT_PIXEL, $attribute),
            );
            $content = str_replace($attribute, $newAttribute, $content);
        }

        return $content;
    }
}
