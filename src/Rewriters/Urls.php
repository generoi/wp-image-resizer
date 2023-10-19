<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Config;
use GeneroWP\ImageResizer\Contracts\Rewriter;
use GeneroWP\ImageResizer\Image;

/**
 * Rewrite all image urls
 */
class Urls implements Rewriter
{
    public function __construct()
    {
        add_filter('wp_content_img_tag', [$this, 'filterImgTag']);
        add_filter('wp_get_attachment_image', [$this, 'filterImgTag']);
        add_filter('wp_calculate_image_srcset', [$this, 'filterSrcset'], 10, 5);
        add_filter('wp_resource_hints', [$this, 'dnsPrefetch'], -100, 2);
    }

    public function filterImgTag(string $html): string
    {
        // Support passing custom settings through a data-resizer-args attribute
        $customArgs = preg_match('/data-resizer-args="([^"]+)"/', $html, $matchSrc) ? $matchSrc[1] : [];
        $settings = wp_parse_args($customArgs, []);

        $src = preg_match('/src="([^"]+)"/', $html, $matchSrc) ? $matchSrc[1] : '';
        if ($src) {
            $image = new Image(
                $this->decodeAttribute($src),
                $settings,
            );
            $html = preg_replace(
                '/ src="([^"]+)"/',
                sprintf(' src="%s"', $image->url()),
                $html
            );
        }

        $srcset = preg_match('/srcset="([^"]+)"/', $html, $matchSrc) ? $matchSrc[1] : '';
        if ($srcset) {
            // If there are data-resizer-args passed, we need to parse the
            // `srcset` attribute and rebuild urls.
            // Note this fails if there are `,` in the image urls
            if ($settings) {
                $sources = explode(',', $srcset);
                $sources = array_map(function ($source) use ($settings) {
                    [$source, $descriptor] = explode(' ', trim($source), 2);

                    $image = new Image(
                        $this->decodeAttribute($source),
                        $settings,
                    );

                    return implode(' ', [$image->url(), $descriptor]);
                }, $sources);
                $srcset = implode(',', $sources);
            }

            $html = preg_replace(
                '/ srcset="([^"]+)"/',
                sprintf(' srcset="%s"', $this->decodeAttribute($srcset)),
                $html
            );
        }

        return $html;
    }

    /**
     * WP runs src and srcset through esc_attr() which escapes & that can cause
     * issues. We decode the escaped attributes.
     */
    protected function decodeAttribute(string $attribute): string
    {
        $replacements = [
            '&amp;' => '&',
            '&#038;' => '&',
            '&#x26;' => '&',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $attribute
        );
    }

    /**
     * @param array<array{"url": string, "descriptor": string, "value": int}> $sources
     * @param int[] $size
     * @param array<string,mixed> $meta
     * @return array<array{"url": string, "descriptor": string, "value": int}>
     */
    public function filterSrcset(array $sources, array $size, string $src, array $meta, int $attachmentId): array
    {
        $sources = [];
        foreach (Config::breakpoints() as $breakpoint) {
            if ($breakpoint > $meta['width']) {
                continue;
            }
            $image = new Image($src, ['width' => $breakpoint]);
            $sources[$breakpoint] = [
                'url' => $image->url(),
                'descriptor' => 'w',
                'value' => $breakpoint,
            ];
        }

        return $sources;
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
