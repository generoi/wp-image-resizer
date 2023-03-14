# wp-image-resizer

> A plugin which provides dynamic image sizes through a CDN

## Requirements

A cloudflare CDN zone with image resizing enabled.

Note that you'll need to manage the `loading` attribute yourself. Only tags with
`loading="lazy"` will get rewritten to use `lozad.js`. You should ensure that
the LCP blocks have `loading="eager"` or no `loading` attribute at all.

`IMAGERESIZER_ZONE` environment variable is required.

### Environment variables

```
IMAGERESIZER_ZONE='https://myapp.com/cdn-cgi/image/'
IMAGERESIZER_DISABLED=false
```

## Features

- Use [lozad.js](https://github.com/ApoorvSaxena/lozad.js/) to lazyload images, iframes and videos
- Support for `sizes="auto"` through `data-sizes="auto"`
- Preload first blocks image
- Rewrite image URLs to use Cloudflare CDN
- Replace all WP `srcset` with our Cloudflare Image Resizing URLs

## API

See [Cloudflare docs](https://developers.cloudflare.com/images/image-resizing/url-format/#options) for transformation options.

### Generate src and srcset from URL

```php
use GeneroWP\ImageResizer\Image;

$image = new Image($url, [
    'blur' => 50,
]);

return sprintf(
    '<img loading="eager" class="wp-block-cover__image-background" src="%s" srcset="%s" sizes="%s">',
    $image->src(),
    $image->srcset(),
    '100vw'
);
```

### Generate src and srcset from attachment

```php
use GeneroWP\ImageResizer\Image;

$image = Image::fromAttachment($bannerImageId, 'full', [
    'fit' => 'cover',
    'height' => 350 * 2,
]);

return sprintf(
    '<img loading="eager" class="wp-block-cover__image-background" src="%s" srcset="%s" sizes="%s">',
    $image->src(),
    $image->srcset(),
    '100vw'
);
```

### Add a preload link tag to head

```php
use GeneroWP\ImageResizer\Rewriters\Preload;

add_action('wp_head', function () {
    if (is_singular('recipe')) {
        $composer = new ContentRecipe();
        $featured = $composer->featuredImageHtml(get_post());
        echo Preload::buildLink($featured);
    }
}, 2);
```

### Filters

```php
// Alter rewriters
add_filter('wp-image-resizer/rewriters', function (array $rewriters) {
    $rewriters[] = MyCustomRewriter::class;
    return $rewriters;
});

// Alter resizer URLs
add_filter('wp-image-resizer/image/url', function (string $url) {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        $url = str_replace('development-domain.ddev.site', 'production-domain.com', $url);
    }
    return $url;
});

// Alter blocks that should be considered for preloading
add_filter('wp-image-resizer/preload/blocks', function (array $blockTypes) {
    $blockTypes[] = 'my-theme/hero-banner';
    return $blockTypes;
});

// Alter if preload should stop iterating blocks
add_filter('wp-image-resizer/preload/should_stop', function (bool $stop, array $block, array $blocks) {
    if ($block['blockType'] === 'my-theme/breadcrumb') {
        return false;
    }
    return $stop;
}, 10, 3);

// Alter srcset breakpoints
add_filter('wp-image-resizer/config/breakpoints', function (array $breakpoints) {
    return [
        ...range(50, 500, 50),
        ...range(600, 2000, 100),
    ];
});

// Alter default resizing settings
add_filter('wp-image-resizer/config/default', function (array $breakpoints) {
    return [
        'quality' => 90
    ];
});
```



## Development

Install dependencies

    composer install
    npm install

Run the tests

    npm run test

Build assets

    # Minified assets which are to be committed to git
    npm run build:production

    # Watch for changes and re-compile while developing the plugin
    npm run start
