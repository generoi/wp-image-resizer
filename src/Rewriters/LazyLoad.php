<?php

namespace GeneroWP\ImageResizer\Rewriters;

use GeneroWP\ImageResizer\Contracts\Rewriter;

/**
 * Lazy load images, videos and iframes.
 */
class LazyLoad implements Rewriter
{
    public function __construct()
    {
        add_filter('wp_get_attachment_image', [$this, 'filterLazyLoadTag'], 100);
        add_filter('wp_content_img_tag', [$this, 'filterLazyLoadTag'], 100);
        add_filter('the_content', [$this, 'filterVideoLoadingAttribute'], 11);
        add_filter('wp_lazy_loading_enabled', [$this, 'enableVideoLoading'], 9, 2);
    }

    /**
     * Opt in to use "loading" attribute for videos.
    */
    public function enableVideoLoading(bool $loading, string $tagName): bool
    {
        if ($tagName === 'video') {
            return true;
        }
        return $loading;
    }

    /**
     * Add loading attribute to all <video> tags.
     *
     * @see wp_filter_content_tags
     * @see https://github.com/WordPress/WordPress/blob/baf1c9d87a332773d69ad365aa15a01315ce8b46/wp-includes/media.php#L1810
     */
    public function filterVideoLoadingAttribute(string $content): string
    {
        $context = current_filter();
        $addVideoLoadingAttr = wp_lazy_loading_enabled('video', $context);
        $addImgLoadingAttr = wp_lazy_loading_enabled('img', $context);
        $addIframeLoadingAttr = wp_lazy_loading_enabled('iframe', $context);

        if (!$addVideoLoadingAttr && !$addImgLoadingAttr && !$addIframeLoadingAttr) {
            return $content;
        }

        if (! preg_match_all('/<(video|img|iframe)\s[^>]+>/', $content, $matches, PREG_SET_ORDER)) {
            return $content;
        }

        // Rough estimation of wp_increase_content_media_count() since it's
        // already been invoked and we cannot retrieve exact values after that
        // fact.
        $mediaCount = 0;

        foreach ($matches as $match) {
            [$tag, $tagName] = $match;
            switch ($tagName) {
                case 'img':
                    if ($addImgLoadingAttr) {
                        $mediaCount++;
                    }
                    break;
                case 'iframe':
                    if ($addIframeLoadingAttr) {
                        $mediaCount++;

                        $filteredTag = $this->filterLazyLoadTag($tag);
                        $content = str_replace($tag, $filteredTag, $content);
                    }
                    break;
                case 'video':
                    if ($addVideoLoadingAttr) {
                        $mediaCount++;

                        if (str_contains($tag, ' loading=')) {
                            break;
                        }

                        if ($mediaCount > wp_omit_loading_attr_threshold()) {
                            $filteredTag = $this->addVideoLoadingAttribute($tag, $context);
                            $content = str_replace($tag, $filteredTag, $content);
                        }
                    }
                    break;
            }
        }

        return $content;
    }

    /**
     * Add the loading attribute to video tags.
     * @see wp_iframe_tag_add_loading_attr().
     */
    protected function addVideoLoadingAttribute(string $tag, string $context): string
    {
        // WordPress 6.3.0
        if (function_exists('wp_get_loading_optimization_attributes')) {
            $optimization = wp_get_loading_optimization_attributes('video', [
                'width' => str_contains($tag, ' width="') ? 100 : null,
                'height' => str_contains($tag, ' height="') ? 100 : null,
                'loading' => null,
            ], $context);
            $value = isset($optimization['loading']) ? $optimization['loading'] : 'lazy';
        } else {
            $value = match (wp_get_loading_attr_default($context)) {
                false => false,
                'eager' => 'eager',
                default => 'lazy',
            };
        }

        if ($value) {
            $tag = str_replace('<video', sprintf('<video loading="%s"', esc_attr($value)), $tag);
            return $this->filterLazyLoadTag($tag);
        }
        return $tag;
    }

    /**
     * Alter the HTML to use data-src for lazy loaded content.
     *
     * Note that this runs as a filter only for <img>. For <video> and <iframe>
     * it's invoked directly.
     */
    public function filterLazyLoadTag(string $html): string
    {
        if (apply_filters('wp-image-resizer/skip-lazy-load', wp_is_rest_endpoint())) {
            return $html;
        }

        if (str_contains($html, 'loading="lazy"')) {
            $html = str_replace(
                ' src=',
                ' data-src=',
                $html
            );

            $html = str_replace(' srcset=', ' data-srcset=', $html);

            if (str_contains($html, ' sizes')) {
                $html = preg_replace('/ sizes="[^"]+"/', ' data-sizes="auto"', $html);
            } elseif (str_contains($html, '<img')) {
                $html = str_replace(' loading="lazy"', ' loading="lazy" data-sizes="auto"', $html);
            }
        }
        return $html;
    }
}
