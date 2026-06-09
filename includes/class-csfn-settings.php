<?php

/**
 * Small General settings page and option accessor for Classic Footnotes.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Settings
{
    const OPTION = 'csfn_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register']);
        add_filter('plugin_action_links_' . plugin_basename(CSFN_PLUGIN_FILE), [$this, 'add_settings_link']);
    }

    /**
     * Built-in defaults. Only the General keys are editable for now.
     */
    public static function defaults()
    {
        return [
            'auto_append'      => 1,
            'heading'          => 'Sources',
            'post_types'       => ['post'],
            'new_tab'          => 1,
            'rel'              => 'nofollow',
            'favicon'          => 'google',
            'fallback'         => 'number',
            'view_mode'        => 'detailed',
            'drawer_position'  => 'right',
            'appearance'       => 'system',
        ];
    }

    /**
     * Get a setting, or the full settings array when $key is null.
     */
    public static function get($key = null)
    {
        $settings = array_merge(self::defaults(), self::saved_general_settings());

        if (null === $key) {
            return $settings;
        }

        return array_key_exists($key, $settings) ? $settings[$key] : null;
    }

    public function add_menu()
    {
        add_options_page(
            __('Classic Footnotes', 'classic-footnotes'),
            __('Classic Footnotes', 'classic-footnotes'),
            'manage_options',
            'classic-footnotes',
            [$this, 'render_page']
        );
    }

    public function add_settings_link($links)
    {
        $settings_link = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url(admin_url('options-general.php?page=classic-footnotes')),
            esc_html__('Settings', 'classic-footnotes')
        );

        array_unshift($links, $settings_link);

        return $links;
    }

    public function register()
    {
        register_setting('csfn_settings_group', self::OPTION, [
            'type'              => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default'           => self::editable_defaults(),
        ]);

        add_settings_section(
            'csfn_general',
            __('General Settings', 'classic-footnotes'),
            '__return_false',
            'csfn-settings'
        );

        $fields = [
            'auto_append'      => __('Append sources automatically', 'classic-footnotes'),
            'heading'          => __('Sources heading', 'classic-footnotes'),
            'post_types'       => __('Enable on post types', 'classic-footnotes'),
            'drawer_position'  => __('Drawer position', 'classic-footnotes'),
            'appearance'       => __('Appearance', 'classic-footnotes'),
        ];

        foreach ($fields as $key => $label) {
            $args = ['key' => $key];
            if (!in_array($key, ['post_types', 'drawer_position', 'appearance'], true)) {
                $args['label_for'] = 'csfn_' . $key;
            }

            add_settings_field(
                'csfn_' . $key,
                $label,
                [$this, 'render_field'],
                'csfn-settings',
                'csfn_general',
                $args
            );
        }
    }

    public function sanitize($input)
    {
        if (!is_array($input)) {
            $input = [];
        }

        $defaults = self::editable_defaults();

        $heading = isset($input['heading']) ? sanitize_text_field($input['heading']) : $defaults['heading'];
        $heading = '' !== $heading ? $heading : $defaults['heading'];

        $drawer_position = isset($input['drawer_position']) ? sanitize_text_field($input['drawer_position']) : $defaults['drawer_position'];
        $drawer_position = in_array($drawer_position, ['left', 'right'], true) ? $drawer_position : $defaults['drawer_position'];

        $appearance = isset($input['appearance']) ? sanitize_text_field($input['appearance']) : $defaults['appearance'];
        $appearance = in_array($appearance, ['system', 'light', 'dark'], true) ? $appearance : $defaults['appearance'];

        return [
            'auto_append'     => empty($input['auto_append']) ? 0 : 1,
            'heading'         => $heading,
            'post_types'      => self::sanitize_post_types(isset($input['post_types']) ? $input['post_types'] : []),
            'drawer_position' => $drawer_position,
            'appearance'      => $appearance,
        ];
    }

    public function render_field($args)
    {
        $key      = $args['key'];
        $settings = self::get();
        $value    = isset($settings[$key]) ? $settings[$key] : null;

        switch ($key) {
            case 'auto_append':
                printf(
                    '<input type="hidden" name="%1$s[auto_append]" value="0">',
                    esc_attr(self::OPTION)
                );
                printf(
                    '<label><input type="checkbox" id="csfn_auto_append" name="%1$s[auto_append]" value="1" %2$s> %3$s</label>',
                    esc_attr(self::OPTION),
                    checked(1, $value, false),
                    esc_html__('Show the Sources list automatically after post content.', 'classic-footnotes')
                );
                break;

            case 'heading':
                printf(
                    '<input type="text" id="csfn_heading" name="%1$s[heading]" value="%2$s" class="regular-text">',
                    esc_attr(self::OPTION),
                    esc_attr($value)
                );
                break;

            case 'post_types':
                $this->render_post_types_field((array) $value);
                break;

            case 'drawer_position':
                printf(
                    '<label style="display:inline-block;margin:0 14px 8px 0;"><input type="radio" name="%1$s[drawer_position]" value="right" %2$s> %3$s</label>',
                    esc_attr(self::OPTION),
                    checked('right', $value, false),
                    esc_html__('Right', 'classic-footnotes')
                );
                printf(
                    '<label style="display:inline-block;margin:0 14px 8px 0;"><input type="radio" name="%1$s[drawer_position]" value="left" %2$s> %3$s</label>',
                    esc_attr(self::OPTION),
                    checked('left', $value, false),
                    esc_html__('Left', 'classic-footnotes')
                );
                break;

            case 'appearance':
                printf(
                    '<label style="display:inline-block;margin:0 14px 8px 0;"><input type="radio" name="%1$s[appearance]" value="system" %2$s> %3$s</label>',
                    esc_attr(self::OPTION),
                    checked('system', $value, false),
                    esc_html__('System', 'classic-footnotes')
                );
                printf(
                    '<label style="display:inline-block;margin:0 14px 8px 0;"><input type="radio" name="%1$s[appearance]" value="light" %2$s> %3$s</label>',
                    esc_attr(self::OPTION),
                    checked('light', $value, false),
                    esc_html__('Light', 'classic-footnotes')
                );
                printf(
                    '<label style="display:inline-block;margin:0 14px 8px 0;"><input type="radio" name="%1$s[appearance]" value="dark" %2$s> %3$s</label>',
                    esc_attr(self::OPTION),
                    checked('dark', $value, false),
                    esc_html__('Dark', 'classic-footnotes')
                );
                echo '<p class="description">' . esc_html__('Choose how the drawer and tooltips should appear. System follows your device settings.', 'classic-footnotes') . '</p>';
                break;
        }
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Classic Footnotes Settings', 'classic-footnotes'); ?></h1>

            <form action="options.php" method="post">
                <?php
                settings_fields('csfn_settings_group');
                do_settings_sections('csfn-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private static function editable_defaults()
    {
        $defaults = self::defaults();

        return [
            'auto_append'     => $defaults['auto_append'],
            'heading'         => $defaults['heading'],
            'post_types'      => $defaults['post_types'],
            'drawer_position' => $defaults['drawer_position'],
            'appearance'      => $defaults['appearance'],
        ];
    }

    private static function saved_general_settings()
    {
        $saved = get_option(self::OPTION, []);

        if (!is_array($saved)) {
            return [];
        }

        $out = [];

        if (array_key_exists('auto_append', $saved)) {
            $out['auto_append'] = empty($saved['auto_append']) ? 0 : 1;
        }

        if (isset($saved['heading']) && is_scalar($saved['heading'])) {
            $heading = sanitize_text_field($saved['heading']);
            if ('' !== $heading) {
                $out['heading'] = $heading;
            }
        }

        if (isset($saved['post_types']) && is_array($saved['post_types'])) {
            $out['post_types'] = self::sanitize_post_types($saved['post_types']);
        }

        if (isset($saved['drawer_position']) && is_scalar($saved['drawer_position'])) {
            $position = sanitize_text_field($saved['drawer_position']);
            if (in_array($position, ['left', 'right'], true)) {
                $out['drawer_position'] = $position;
            }
        }

        if (isset($saved['appearance']) && is_scalar($saved['appearance'])) {
            $appearance = sanitize_text_field($saved['appearance']);
            if (in_array($appearance, ['system', 'light', 'dark'], true)) {
                $out['appearance'] = $appearance;
            }
        }

        return $out;
    }

    private static function sanitize_post_types($post_types)
    {
        if (!is_array($post_types)) {
            return [];
        }

        $out = [];

        foreach ($post_types as $post_type) {
            if (!is_scalar($post_type)) {
                continue;
            }

            $post_type = sanitize_key($post_type);
            if ('' === $post_type || !post_type_exists($post_type)) {
                continue;
            }

            $out[] = $post_type;
        }

        return array_values(array_unique($out));
    }

    private function render_post_types_field($enabled)
    {
        $types = get_post_types(['public' => true], 'objects');

        printf(
            '<input type="hidden" name="%1$s[post_types][]" value="">',
            esc_attr(self::OPTION)
        );

        foreach ($types as $post_type) {
            if ('attachment' === $post_type->name) {
                continue;
            }

            printf(
                '<label style="display:inline-block;margin:0 14px 8px 0;"><input type="checkbox" name="%1$s[post_types][]" value="%2$s" %3$s> %4$s</label>',
                esc_attr(self::OPTION),
                esc_attr($post_type->name),
                checked(in_array($post_type->name, $enabled, true), true, false),
                esc_html($post_type->labels->singular_name)
            );
        }
    }
}
