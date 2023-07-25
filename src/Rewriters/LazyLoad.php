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
        add_filter('the_content', [$this, 'filterLoadingAttribute'], 9);
        add_filter('wp_lazy_loading_enabled', [$this, 'enableVideoLoading'], 9, 2);
    }

    /**
     * Opt in to use "loading" attribute for videos.
    */
    public function enableVideoLoading(bool $loading, string $tagName)
    {
        if ($tagName === 'video') {
            return true;
        }
        return $loading;
    }

    /**
     * Add loading attribute to all <video> tags as well as the default core
     * <img> and <iframe>. We need to run this before wp_filter_content_tags()
     * since it doesnt take videos into account.
     *
     * @see wp_filter_content_tags
     */
    public function filterLoadingAttribute(string $content): string
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

        foreach ($matches as $match) {
            [$tag, $tagName] = $match;
            switch ($tagName) {
                case 'img':
                    if ($addImgLoadingAttr && ! str_contains($tag, ' loading=')) {
                        // We need to use our own implementation since height/width still isnt set, we set eager explicitly.
                        $loading = wp_get_loading_attr_default($context) ?: 'eager';
                        $loading = apply_filters('wp_img_tag_add_loading_attr', $loading, $tag, $context);
                        $loading = in_array($loading, ['lazy', 'eager']) ? $loading : 'lazy';
                        $filteredTag = str_replace('<img', sprintf('<img loading="%s"', $loading), $tag);
                        $content = str_replace($tag, $filteredTag, $content);
                    }
                    break;
                case 'iframe':
                    if ($addIframeLoadingAttr && !str_contains($tag, ' loading=')) {
                        $filteredTag = wp_iframe_tag_add_loading_attr($tag, $context);
                        $content = str_replace($tag, $filteredTag, $content);
                    }
                    break;
                case 'video':
                    if ($addVideoLoadingAttr && !str_contains($tag, ' loading=')) {
                        $filteredTag = $this->addVideoLoadingAttribute($tag, $context);
                        $content = str_replace($tag, $filteredTag, $content);
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
            $value = isset($optimization['loading'] ) ? $optimization['loading'] : 'lazy';
        } else {
            $value = match (wp_get_loading_attr_default($context)) {
                false => false,
                'eager' => 'eager',
                default => 'lazy',
            };
        }

        if ($value) {
            $tag = str_replace( '<video', sprintf('<video loading="%s"', esc_attr($value)), $tag);
            return $this->filterLazyLoadTag($tag);
        }
        return $tag;
    }

    /**
     * Alter the HTML to use data-src for lazy loaded content.
     *
     * Note that this runs as a filter only for <img> and <iframe>. For <video>
     * it's invoked directly.
     */
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
}
