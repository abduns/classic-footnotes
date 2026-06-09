<?php

/**
 * Classic Editor helpers for inserting footnotes.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Editor
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('mce_external_plugins', [$this, 'add_tinymce_plugin']);
        add_filter('mce_buttons', [$this, 'add_tinymce_button']);
    }

    public function enqueue_assets($hook)
    {
        if (!$this->should_load($hook)) {
            return;
        }

        wp_enqueue_script('quicktags');

        wp_enqueue_script(
            'csfn-editor',
            CSFN_PLUGIN_URL . 'assets/editor.js',
            ['quicktags'],
            CSFN_VERSION,
            true
        );

        wp_localize_script('csfn-editor', 'csfnEditorL10n', $this->labels());

        wp_enqueue_style(
            'csfn-editor',
            CSFN_PLUGIN_URL . 'assets/editor.css',
            [],
            CSFN_VERSION
        );
    }

    public function add_tinymce_plugin($plugins)
    {
        if (!$this->should_load() || !user_can_richedit()) {
            return $plugins;
        }

        $plugins['csfn_reference_note'] = add_query_arg(
            'ver',
            CSFN_VERSION,
            CSFN_PLUGIN_URL . 'assets/editor.js'
        );

        return $plugins;
    }

    public function add_tinymce_button($buttons)
    {
        if (!$this->should_load() || !user_can_richedit()) {
            return $buttons;
        }

        $buttons[] = 'csfn_reference_note';

        return $buttons;
    }

    private function should_load($hook = null)
    {
        if (null !== $hook && !in_array($hook, ['post.php', 'post-new.php'], true)) {
            return false;
        }

        $post_type = $this->current_post_type();

        if ('' === $post_type || !post_type_exists($post_type)) {
            return false;
        }

        return in_array($post_type, (array) CSFN_Settings::get('post_types'), true);
    }

    private function current_post_type()
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if ($screen && !empty($screen->post_type)) {
            return sanitize_key($screen->post_type);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only determination of current screen.
        if (!empty($_GET['post'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
            $post_type = get_post_type(absint($_GET['post']));

            return $post_type ? sanitize_key($post_type) : '';
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only determination of current screen.
        if (!empty($_GET['post_type'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
            return sanitize_key(wp_unslash($_GET['post_type']));
        }

        return 'post';
    }

    private function labels()
    {
        return [
            'buttonTitle' => __('Reference note', 'classic-footnotes'),
            'dialogTitle' => __('Add reference note', 'classic-footnotes'),
            'noteLabel'   => __('Footnote text', 'classic-footnotes'),
            'urlLabel'    => __('Source URL', 'classic-footnotes'),
            'titleLabel'  => __('Source title', 'classic-footnotes'),
            'insertLabel' => __('Insert', 'classic-footnotes'),
            'cancelLabel' => __('Cancel', 'classic-footnotes'),
            'notePrompt'  => __('Footnote text:', 'classic-footnotes'),
        ];
    }
}
