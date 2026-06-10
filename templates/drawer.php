<?php

/**
 * Drawer panel template — output via wp_footer at the end of <body>.
 *
 * Rendering outside the post-content wrapper avoids stacking-context /
 * overflow-hidden traps that page builders (WPBakery, Elementor, etc.)
 * and some themes create around the_content output.
 *
 * @var array[] $csfn_items           Footnote items.
 * @var string  $csfn_heading         Section heading text.
 * @var string  $csfn_link_attrs      Pre-escaped target/rel attribute string.
 * @var string  $csfn_fallback        Icon fallback style (number | globe).
 * @var string  $csfn_view_mode       View mode for the sources list.
 * @var string  $csfn_drawer_position Drawer position (left | right).
 * @var string  $csfn_appearance      Appearance mode (system | light | dark).
 */

if (!defined('ABSPATH')) {
    exit;
}

$csfn_fallback        = in_array($csfn_fallback, ['number', 'globe'], true) ? $csfn_fallback : 'number';
$csfn_view_mode       = in_array($csfn_view_mode, ['compact', 'detailed'], true) ? $csfn_view_mode : 'detailed';
$csfn_drawer_position = in_array($csfn_drawer_position, ['left', 'right'], true) ? $csfn_drawer_position : 'right';
$csfn_appearance      = in_array($csfn_appearance, ['system', 'light', 'dark'], true) ? $csfn_appearance : 'system';
$csfn_drawer_class    = 'csfn-drawer csfn-drawer--' . $csfn_view_mode;
if ('left' === $csfn_drawer_position) {
    $csfn_drawer_class .= ' csfn-drawer--left';
}
// Add appearance class: system uses CSS media query, light/dark forces the mode
if ('dark' === $csfn_appearance) {
    $csfn_drawer_class .= ' csfn-drawer--dark-mode';
} elseif ('light' === $csfn_appearance) {
    $csfn_drawer_class .= ' csfn-drawer--light-mode';
}
$csfn_globe_svg = '<svg viewBox="0 0 24 24" width="10" height="10" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.93 6h-2.95a15.7 15.7 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8ZM12 4c.83 1.2 1.48 2.54 1.91 4h-3.82c.43-1.46 1.08-2.8 1.91-4ZM4.26 14a7.96 7.96 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26Zm.81 2h2.95c.34 1.27.8 2.46 1.38 3.56A8.03 8.03 0 0 1 5.07 16Zm2.95-8H5.07a8.03 8.03 0 0 1 4.33-3.56A15.7 15.7 0 0 0 8.02 8ZM12 20c-.83-1.2-1.48-2.54-1.91-4h3.82c-.43 1.46-1.08 2.8-1.91 4Zm2.34-6H9.66a14.4 14.4 0 0 1 0-4h4.68a14.4 14.4 0 0 1 0 4Zm.32 5.56c.58-1.1 1.04-2.29 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56ZM16.36 14a16.5 16.5 0 0 0 0-4h3.38a7.96 7.96 0 0 1 0 4h-3.38Z"/></svg>';
?>

<!-- Sliding Drawer (Modal Flyout) — rendered at end of <body> via wp_footer -->
<div class="csfn-drawer-overlay" id="csfn-drawer-overlay"></div>
<aside class="<?php echo esc_attr($csfn_drawer_class); ?>" id="csfn-drawer" aria-labelledby="csfn-drawer-title" role="dialog" aria-modal="true" data-csfn-view-mode="<?php echo esc_attr($csfn_view_mode); ?>">
    <div class="csfn-drawer__header">
        <h5 class="csfn-drawer__title" id="csfn-drawer-title"><?php echo esc_html($csfn_heading); ?></h5>
        <button class="csfn-drawer__close" aria-label="<?php esc_attr_e('Close sources list', 'ai-footnotes'); ?>">&times;</button>
    </div>
    <div class="csfn-drawer__body">
        <ol class="csfn-drawer__list">
            <?php foreach ($csfn_items as $csfn_item) :
                $csfn_n        = (int) $csfn_item['n'];
                $csfn_is_link  = ('link' === $csfn_item['type']);
                $csfn_has_icon = $csfn_is_link && '' !== $csfn_item['favicon'];
                $csfn_show_globe = $csfn_is_link && !$csfn_has_icon && 'globe' === $csfn_fallback;
                ?>
                <li id="csfn-src-<?php echo esc_attr($csfn_n); ?>" class="csfn-source csfn-source--<?php echo esc_attr($csfn_item['type']); ?>">
                    <span class="csfn-source__marker">
                        <?php if ($csfn_has_icon) : ?>
                            <img class="csfn-source__favicon" src="<?php echo esc_url($csfn_item['favicon'], ['http', 'https', 'data']); ?>" alt="" loading="lazy" width="20" height="20" />
                        <?php elseif ($csfn_show_globe) : ?>
                            <span class="csfn-source__globe"><?php echo $csfn_globe_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
                        <?php else : ?>
                            <span class="csfn-source__num"><?php echo esc_html($csfn_n); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="csfn-source__body">
                        <?php if ($csfn_is_link) : ?>
                            <a class="csfn-source__link" href="<?php echo esc_url($csfn_item['url']); ?>"<?php echo $csfn_link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in renderer ?>>
                                <span class="csfn-source__title"><?php echo esc_html($csfn_item['title']); ?></span>
                                <?php if ('' !== $csfn_item['domain']) : ?>
                                    <span class="csfn-source__domain"><?php echo esc_html($csfn_item['domain']); ?></span>
                                <?php endif; ?>
                            </a>
                            <?php if ('' !== $csfn_item['note']) : ?>
                                <span class="csfn-source__note"><?php echo esc_html($csfn_item['note']); ?></span>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php if ('' !== $csfn_item['title']) : ?>
                                <span class="csfn-source__title"><?php echo esc_html($csfn_item['title']); ?></span>
                            <?php endif; ?>
                            <span class="csfn-source__note"><?php echo esc_html($csfn_item['note']); ?></span>
                        <?php endif; ?>
                    </span>
                    <a class="csfn-source__back" href="#csfn-ref-<?php echo esc_attr($csfn_n); ?>" role="doc-backlink" aria-label="<?php esc_attr_e('Back to content', 'ai-footnotes'); ?>">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</aside>
