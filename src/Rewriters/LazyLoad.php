<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Rewriter;

class LazyLoad implements Rewriter
{
    public function __construct()
    {
        add_filter('wp_get_attachment_image', [$this, 'filterLazyLoadTag'], 100);
        add_filter('wp_content_img_tag', [$this, 'filterLazyLoadTag'], 100);
        add_filter('wp_resource_hints', [$this, 'dnsPrefetch'], -100, 2);
    }

    public function filterLazyLoadTag(string $html): string
    {
        if (str_contains($html, 'loading="lazy"')) {
            $html = str_replace(
                ' src=',
                ' data-src=',
                $html
            );

            $html = str_replace(' srcset=', ' data-srcset=', $html);

            if (str_contains($html, ' sizes')) {
                $html = preg_replace('/ sizes="[^"]+"/', ' data-sizes="auto"', $html);
            } else {
                $html = str_replace(' loading="lazy"', ' loading="lazy" data-sizes="auto"', $html);
            }
        }
        return $html;
    }

    /**
     * @param array<int,array<mixed>> $hints
     * @return array<int,array<mixed>>
     */
    public function dnsPrefetch(array $hints, string $type): array
    {
        if ($type === 'preconnect') {
            $hints[] = [
                'href' => '//' . parse_url(Config::zone(), PHP_URL_HOST),
                'crossorigin',
            ];
        }
        return $hints;
    }
}
