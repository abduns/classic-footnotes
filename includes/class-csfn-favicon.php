<?php

/**
 * Favicon URL resolver for Classic Footnotes.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CSFN_Favicon
{
    /**
     * Extract a clean display domain (host without leading "www.").
     */
    public static function domain($url)
    {
        $host = wp_parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return '';
        }

        return preg_replace('/^www\./i', '', $host);
    }

    /**
     * Retrieve the favicon Data URI for a source URL, based on the provider setting.
     * Fetches and caches the image server-side to prevent visitor IP leaks.
     * Returns '' when no favicon should be shown or fetch fails.
     */
    public static function url($source_url)
    {
        $provider = CSFN_Settings::get('favicon');
        $domain   = self::domain($source_url);

        if ('none' === $provider || '' === $domain) {
            return '';
        }

        $transient_key = 'csfn_fav_' . md5($domain . '_' . $provider);
        $cached_data   = get_transient($transient_key);

        if (false !== $cached_data) {
            return $cached_data;
        }

        if ('duckduckgo' === $provider) {
            $remote_url = 'https://icons.duckduckgo.com/ip3/' . rawurlencode($domain) . '.ico';
        } else {
            // Default: Google S2 favicon service.
            $remote_url = 'https://www.google.com/s2/favicons?sz=64&domain=' . rawurlencode($domain);
        }

        $response = wp_remote_get($remote_url, [
            'timeout'     => 5,
            'redirection' => 3,
        ]);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            // Cache the failure for 12 hours to prevent repeated slow requests.
            set_transient($transient_key, '', 12 * HOUR_IN_SECONDS);
            return '';
        }

        $body         = wp_remote_retrieve_body($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type');

        if (empty($body) || empty($content_type) || strpos($content_type, 'image/') !== 0) {
            set_transient($transient_key, '', 12 * HOUR_IN_SECONDS);
            return '';
        }

        $base64   = base64_encode($body);
        $data_uri = 'data:' . $content_type . ';base64,' . $base64;

        // Cache the successful base64 Data URI for 30 days.
        set_transient($transient_key, $data_uri, 30 * DAY_IN_SECONDS);

        return $data_uri;
    }
}
