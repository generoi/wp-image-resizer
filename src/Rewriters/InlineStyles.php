<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Contracts\Rewriter;
use GeneroWP\ImageResizer\Image;

class InlineStyles implements Rewriter
{
    public function __construct()
    {
        add_filter('the_content', [$this, 'filterStyles']);
    }

    public function filterStyles(string $content): string
    {
        preg_match_all('/style="[^"]*?background-image:\s*url\([\'"]?([^)]*?[\'"]?)\)/', $content, $matches);

        $attributes = $matches[0];
        $urls = $matches[1];

        foreach ($attributes as $idx => $attribute) {
            $url = $urls[$idx];

            $image = new Image($url, ['width' => 1000]);
            $newAttribute = str_replace($url, $image->url(), $attribute);
            $content = str_replace($attribute, $newAttribute, $content);
        }

        return $content;
    }
}
