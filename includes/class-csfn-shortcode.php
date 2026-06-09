<?php

/**
 * Shortcode handlers for Classic Footnotes: [fn] and [fn_sources].
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Shortcode
{
    public function __construct()
    {
        add_shortcode('fn', [$this, 'render_fn']);
        add_shortcode('fn_sources', [$this, 'render_sources']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue'], 20);
    }

    /**
     * Register (but do not enqueue) frontend assets.
     */
    public function register_assets()
    {
        wp_register_style(
            'csfn-style',
            CSFN_PLUGIN_URL . 'assets/style.css',
            [],
            CSFN_VERSION
        );

        wp_register_script(
            'csfn-script',
            CSFN_PLUGIN_URL . 'assets/script.js',
            [],
            CSFN_VERSION,
            true
        );
    }

    /**
     * Enqueue assets in the head when the current singular post uses [fn].
     */
    public function maybe_enqueue()
    {
        if (!is_singular() || !is_main_query()) {
            return;
        }

        $post = get_post();
        if (!$post || !has_shortcode($post->post_content, 'fn')) {
            return;
        }

        $this->enqueue();
    }

    /**
     * Enqueue assets.
     */
    private function enqueue()
    {
        wp_enqueue_style('csfn-style');
        wp_enqueue_script('csfn-script');
    }

    /**
     * [fn] — inline footnote marker.
     */
    public function render_fn($atts, $content = null)
    {
        $atts = shortcode_atts(
            [
                'url'   => '',
                'title' => '',
                'text'  => '',
            ],
            $atts,
            'fn'
        );

        // Note text comes from the `text` attribute (self-closing, collision-free)
        // or, as a fallback, from enclosed content. The attribute is preferred so
        // a self-closing link [fn url=...] can never greedily swallow a later
        // [/fn] from a separate note shortcode.
        $note = '' !== $atts['text']
            ? trim(wp_strip_all_tags($atts['text']))
            : ((null === $content) ? '' : trim(wp_strip_all_tags($content)));

        // Outside our active context (archives, feeds, etc.) degrade gracefully.
        if (!CSFN_Content::is_active()) {
            return ('' !== $note) ? esc_html($note) : '';
        }

        $item = CSFN_Store::instance()->add([
            'url'   => esc_url_raw($atts['url']),
            'title' => sanitize_text_field($atts['title']),
            'note'  => $note,
        ]);

        if (null === $item) {
            return '';
        }

        // Belt-and-braces in case maybe_enqueue() did not catch this content.
        $this->enqueue();

        return CSFN_Renderer::chip($item);
    }

    /**
     * [fn_sources] — manual placement of the "Sources" list.
     */
    public function render_sources($atts, $content = null)
    {
        if (!CSFN_Content::is_active()) {
            return '';
        }

        $store = CSFN_Store::instance();
        $store->mark_manual();

        if (!$store->has_items()) {
            return '';
        }

        return CSFN_Renderer::sources($store->all());
    }
}
