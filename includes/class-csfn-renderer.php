<?php

/**
 * Builds the HTML for inline chips, the Sources footer bar, and the drawer panel.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Renderer
{
    /**
     * Render a single inline citation chip.
     */
    public static function chip($item)
    {
        return self::template('chip', [
            'csfn_item'     => $item,
            'csfn_fallback' => CSFN_Settings::get('fallback'),
        ]);
    }

    /**
     * Render the Sources footer bar (inline in content) + the drawer.
     *
     * Used by [fn_sources] manual shortcode where both parts are needed at once.
     */
    public static function sources($items)
    {
        if (empty($items)) {
            return '';
        }

        return self::sources_bar($items) . self::drawer($items);
    }

    /**
     * Render only the Sources footer bar (placed inline in content).
     */
    public static function sources_bar($items)
    {
        if (empty($items)) {
            return '';
        }

        return self::template('sources-bar', [
            'csfn_items'    => $items,
            'csfn_fallback' => CSFN_Settings::get('fallback'),
            'csfn_view_mode' => CSFN_Settings::get('view_mode'),
        ]);
    }

    /**
     * Render only the drawer panel (output at end of <body> via wp_footer).
     */
    public static function drawer($items)
    {
        if (empty($items)) {
            return '';
        }

        return self::template('drawer', [
            'csfn_items'           => $items,
            'csfn_heading'         => CSFN_Settings::get('heading'),
            'csfn_link_attrs'      => self::link_attrs(),
            'csfn_fallback'        => CSFN_Settings::get('fallback'),
            'csfn_view_mode'       => CSFN_Settings::get('view_mode'),
            'csfn_drawer_position' => CSFN_Settings::get('drawer_position'),
            'csfn_appearance'      => CSFN_Settings::get('appearance'),
        ]);
    }

    /**
     * Pre-built, escaped target/rel attribute string for source links.
     */
    private static function link_attrs()
    {
        $attrs = '';
        $rel   = [];

        if (CSFN_Settings::get('new_tab')) {
            $attrs .= ' target="_blank"';
            $rel[]  = 'noopener';
        }

        $rel_setting = CSFN_Settings::get('rel');
        if ('none' !== $rel_setting) {
            $rel[] = $rel_setting;
        }

        if ($rel) {
            $attrs .= ' rel="' . esc_attr(implode(' ', array_unique($rel))) . '"';
        }

        return $attrs;
    }

    /**
     * Render a template file from /templates and return its output.
     */
    private static function template($name, $vars)
    {
        $file = CSFN_PLUGIN_DIR . 'templates/' . $name . '.php';

        if (!file_exists($file)) {
            return '';
        }

        extract($vars, EXTR_SKIP);

        ob_start();
        include $file;
        return ob_get_clean();
    }
}

