<?php

namespace GeneroWP\ImageResizer;

use GeneroWP\ImageResizer\Rewriters\InlineStyles;
use GeneroWP\ImageResizer\Rewriters\LazyLoad;
use GeneroWP\ImageResizer\Rewriters\Preload;
use GeneroWP\ImageResizer\Rewriters\Urls;
use GeneroWP\ImageResizer\Contracts\Rewriter;

use function Env\env;

class Plugin
{
    public string $name = 'wp-image-resizer';
    public string $file;
    public string $path;
    public string $url;

    /** @var Rewriter[] $rewriters */
    public array $rewriters = [];

    protected static Plugin $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->file = realpath(__DIR__ . '/../wp-image-resizer.php');
        $this->path = untrailingslashit(plugin_dir_path($this->file));
        $this->url = untrailingslashit(plugin_dir_url($this->file));

        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('script_loader_tag', [$this, 'deferScript'], 10, 2);
    }

    public function init(): void
    {
        if (! Config::enabled()) {
            return;
        }

        foreach ($this->rewriters() as $rewriter) {
            $this->rewriters[$rewriter] = new $rewriter;
        }
    }

    /**
     * @return class-string[]
     */
    public function rewriters(): array
    {
        return apply_filters('wp-image-resizer/rewriters', [
            InlineStyles::class,
            LazyLoad::class,
            Preload::class,
            Urls::class,
        ]);
    }

    public function registerAssets(): void
    {
        wp_register_script(
            "{$this->name}/js",
            "{$this->url}/dist/wp-image-resizer.js",
            [],
            filemtime($this->path . '/dist/wp-image-resizer.js')
        );
    }

    public function enqueueAssets(): void
    {
        wp_enqueue_script("{$this->name}/js");
    }

    public function deferScript(string $tag, string $handle): string
    {
        if ($handle === "{$this->name}/js") {
            $tag = str_replace(' src', " defer src", $tag);
        }
        return $tag;
    }
}
