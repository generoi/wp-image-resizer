<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Contracts\Rewriter;
use WP_HTML_Tag_Processor;

/**
 * Add preload links to <head>.
 */
class Preload implements Rewriter
{
    public const FILTER_SHOULD_STOP = 'wp-image-resizer/preload/should_stop';
    public const FILTER_PRELOAD_BLOCKS = 'wp-image-resizer/preload/blocks';
    public const FILTER_CONTENT_TAGS = 'wp-image-resizer/preload/content_tags';

    /** @var string[] $imageBlocks */
    public array $imageBlocks = [
        'core/cover',
        'core/video',
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
                    $content = render_block($block);
                    $content = str_replace('<img ', '<img loading="eager" ', $content);
                    $content = str_replace('<video ', '<video loading="eager" ', $content);
                    $content = wp_filter_content_tags($content, 'preload');
                    $content = apply_filters(self::FILTER_CONTENT_TAGS, $content);

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
        $tag = new WP_HTML_Tag_Processor($content);

        while ($tag->next_tag()) {
            switch ($tag->get_tag()) {
                case 'PICTURE':
                    // Quite difficult to convert this to preload links since
                    // queries cant fallthrough with preloads
                    return '';
                case 'VIDEO':
                    $poster = $tag->get_attribute('poster');
                    if (! $poster) {
                        return '';
                    }

                    $attributes = [
                        'rel' => 'preload',
                        'fetchpriority' => $priority,
                        'as' => 'image',
                        'href' => $poster,
                        'crossorigin' => $tag->get_attribute('crossorigin'),
                    ];

                    return sprintf('<link %s>', self::buildHtmlAttributes($attributes));
                case 'IMG':
                    $src = $tag->get_attribute('src');
                    if (! $src) {
                        return '';
                    }
                    $attributes = [
                        'rel' => 'preload',
                        'fetchpriority' => $priority,
                        'as' => 'image',
                        'href' => $src,
                        'srcset' => $tag->get_attribute('srcset'),
                        'sizes' => $tag->get_attribute('sizes'),
                        'crossorigin' => $tag->get_attribute('crossorigin'),
                    ];
                    return sprintf('<link %s>', self::buildHtmlAttributes($attributes));
            }
        }
        return '';
    }

    protected static function buildHtmlAttributes(array $attributes): string
    {
        $html = [];
        foreach ($attributes as $attr => $value) {
            if ($value === null) {
                continue;
            }

            if ($value === true) {
                $html[] = $attr;
            } elseif (in_array($attr, ['href', 'src', 'srcset'])) {
                $html[] = $attr . '="' . esc_url($value) . '"';
            } else {
                $html[] = $attr . '="' . esc_attr($value) . '"';
            }
        }

        return implode(' ', $html);
    }
}
