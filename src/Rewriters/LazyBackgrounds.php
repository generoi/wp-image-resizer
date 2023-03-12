<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Rewriter;
use GeneroWP\ImageResizer\Image;

class LazyBackgrounds implements Rewriter
{
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
                str_replace($url, Config::placeholderImage(), $attribute),
            );
            $content = str_replace($attribute, $newAttribute, $content);
        }

        return $content;
    }
}
