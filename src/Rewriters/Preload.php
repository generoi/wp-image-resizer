<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Contracts\Rewriter;

class Preload implements Rewriter
{
    public const FILTER_SHOULD_STOP = 'wp-image-resizer/preload/should_stop';
    public const FILTER_PRELOAD_BLOCKS = 'wp-image-resizer/preload/blocks';

    /** @var string[] $imageBlocks */
    public array $imageBlocks = [
        'core/cover',
        'core/image',
        'core/media-text'
    ];

    public function __construct()
    {
        add_action('wp_head', [$this, 'preloadImage'], 2);
    }

    public function preloadImage(): void
    {
        if (is_singular()) {
            $post = get_post();
            $blocks = parse_blocks($post->post_content);
            $preloadBlocks = apply_filters(self::FILTER_PRELOAD_BLOCKS, $this->imageBlocks);

            while ($block = array_shift($blocks)) {
                if (in_array($block['blockName'], $preloadBlocks)) {
                    $content = str_replace('<img ', '<img loading="eager" ', $block['innerHTML']);
                    $content = wp_filter_content_tags($content, 'preload');
                    echo self::buildLink($content);
                }

                if (apply_filters(self::FILTER_SHOULD_STOP, true, $block, $blocks)) {
                    break;
                }
            }
        }
    }

    public static function buildLink(string $content, string $priority = 'high'): string
    {
        $src = preg_match('/src="([^"]+)"/', $content, $matchSrc) ? $matchSrc[1] : '';
        $srcset = preg_match('/srcset="([^"]+)"/', $content, $matchSrc) ? $matchSrc[1] : '';
        $sizes = preg_match('/sizes="([^"]+)"/', $content, $matchSrc) ? $matchSrc[1] : '';

        if ($src && $srcset && $sizes) {
            // @see https://web.dev/preload-responsive-images/#imagesrcset-and-imagesizes
            return sprintf(
                '<link rel="preload" fetchpriority="%s" as="image" href="%s" imagesrcset="%s" imagesizes="%s">',
                $priority,
                $src,
                $srcset,
                $sizes
            );
        }

        return '';
    }
}
