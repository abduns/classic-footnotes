<?php

/**
 * Wires footnotes into the_content: resets the store before shortcodes run
 * and appends the "Sources" section after them.
 *
 * Priorities are deliberately extreme rather than "just after 11". Some sites
 * (caching/optimization plugins, page builders) remap the_content filter
 * priorities, so core's do_shortcode can run much later than its default 11.
 * Resetting very early (1) and appending very late (PHP_INT_MAX - 10) keeps
 * this correct regardless of where do_shortcode ends up.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Content
{
    public function __construct()
    {
        // Reset the per-request store before any shortcode runs.
        add_filter('the_content', [$this, 'reset'], 1);
        // Append the Sources list after shortcodes have populated the store.
        add_filter('the_content', [$this, 'append_sources'], PHP_INT_MAX - 10);
    }

    /**
     * Whether footnotes should be processed for the current request.
     */
    public static function is_active()
    {
        if (is_feed() || !is_singular() || !in_the_loop() || !is_main_query()) {
            return false;
        }

        $enabled = (array) CSFN_Settings::get('post_types');

        return in_array(get_post_type(), $enabled, true);
    }

    public function reset($content)
    {
        if (self::is_active()) {
            CSFN_Store::instance()->reset();
        }

        return $content;
    }

    public function append_sources($content)
    {
        if (!self::is_active()) {
            return $content;
        }

        $store = CSFN_Store::instance();

        if (!$store->has_items() || $store->used_manual() || !CSFN_Settings::get('auto_append')) {
            return $content;
        }

        return $content . CSFN_Renderer::sources($store->all());
    }
}
