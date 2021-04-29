<?php

namespace Focusable\Setup;

/**
 * Enqueue.
 */
class Enqueue
{
    /**
     * register default hooks and actions for WordPress
     * @return
     */
    public function register()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function vendor_assets()
    {
        $assets = [
            "js" => [
                   [
                    "name" => "ally",
                    "file_name" => "ally.min.js",
                    "version" => "1.4.1",
                ],
            ],
            "css" => [],
        ];

        return $assets;
    }

    /**
     * Notice the mix() function in wp_enqueue_...
     * It provides the path to a versioned asset by Laravel Mix using querystring-based
     * cache-busting (This means, the file name won't change, but the md5. Look here for
     * more information: https://github.com/JeffreyWay/laravel-mix/issues/920 )
     */
    public function enqueue_scripts()
    {


        $assets = $this->vendor_assets();

        foreach ($assets['js'] as $index => $javascript_asset) {

            wp_enqueue_script(
                $javascript_asset['name'],
                FOCUSABLE_DIR_URI . 'assets/vendor/js/' . $javascript_asset['file_name'],
                array(),
                $javascript_asset['version'],
                true
            );
        };

        wp_enqueue_script('focusable-main', focusable_assets('js/main.js'), array(), time(), true);

        wp_enqueue_style('focusable-style', focusable_assets('css/style.css'), array(), time(), 'all');
    }

}
