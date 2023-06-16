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
                    if ($addImgLoadingAttr && !str_contains($tag, ' loading=')) {
                        $filteredTag = wp_img_tag_add_loading_attr($tag, $context);
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
        $value = match (wp_get_loading_attr_default($context)) {
            false => false,
            'eager' => 'eager',
            default => 'lazy',
        };

        if ($value) {
            $tag = str_replace( '<video', sprintf('<video loading="%s"', esc_attr($value)), $tag);
            return $this->filterLazyLoadTag($tag);
        }
        return $tag;
    }

    /**
     * Alter the HTML to use data-src for lazy loaded content.
     *
     * Note that this filter only for <img> and <iframe>, not on <video>.
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
