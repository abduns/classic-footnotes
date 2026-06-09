<?php

/**
 * Uninstall handler — remove legacy plugin options.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('csfn_settings');
