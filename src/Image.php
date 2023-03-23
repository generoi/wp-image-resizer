<?php

namespace GeneroWP\ImageResizer;

class Image
{
    public const URL_FILTER = 'wp-image-resizer/image/url';

    /** @var array<string,int|string> $settings */
    public array $settings;

    /**
     * @param array<string,int|string> $settings
     */
    public function __construct(
        public string $url,
        array $settings = [],
        public ?int $width = null,
        public ?int $height = null,
        public ?int $attachmentId = null,
    ) {
        $this->settings = $this->withDefaults($settings, $attachmentId);
    }

    /**
     * @param array<string,int|string> $settings
     */
    public static function fromAttachment(int $attachmentId, string $size = 'full', array $settings = []): Image
    {
        [$url, $width, $height] = wp_get_attachment_image_src($attachmentId, $size);

        return new self($url, $settings, $width, $height, $attachmentId);
    }


    public function src(): string
    {
        return $this->url();
    }

    public function srcset(): string
    {
        $sources = [];
        foreach (Config::breakpoints() as $breakpoint) {
            if ($this->width && ($breakpoint > $this->width)) {
                continue;
            }
            $image = clone $this;
            $image->settings['width'] = $breakpoint;

            $sources[] = sprintf(
                '%s %sw',
                $image->url(),
                $breakpoint,
            );
        }

        return implode(', ', $sources);
    }

    public function url(): string
    {
        $url = apply_filters(self::URL_FILTER, $this->url);
        return Config::resizer()->buildUrl($url, $this->settings);
    }

    /**
     * @param array<string,int|string> $settings
     * @return array<string,int|string>
     */
    protected function withDefaults(array $settings = [], ?int $attachmentId = null): array
    {
        $settings = array_merge(
            Config::defaultSettings(),
            $settings
        );

        // When cropping with fit: "cover" and fit: "crop", this parameter
        // defines the side or point that should not be cropped
        if ($attachmentId && in_array($settings['fit'] ?? '', ['fit', 'cover'])) {
            if ($gravity = $this->gravity()) {
                $settings = array_merge([
                    'gravity' => $gravity,
                ], $settings);
            }
        }

        return $settings;
    }

    /**
     * Get gravity from plugins if they exist.
     */
    protected function gravity(): ?string
    {
        if (! $this->attachmentId) {
            return null;
        }

        if (get_post_meta($this->attachmentId, '_wpsmartcrop_enabled', true) !== '1') {
            return null;
        }

        $focus = get_post_meta($this->attachmentId, '_wpsmartcrop_image_focus', true);
        if (empty($focus['left']) || empty($focus['top'])) {
            return null;
        }

        $x = round($focus['left'] / 100, 1);
        $y = round($focus['top'] / 100, 1);

        return sprintf('%sx%s', $x, $y);
    }
}
