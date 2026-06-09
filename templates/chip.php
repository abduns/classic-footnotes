<?php

/**
 * Inline citation chip template.
 *
 * @var array  $csfn_item     Footnote item (n, type, url, title, domain, note, favicon).
 * @var string $csfn_fallback Icon fallback style (number | globe).
 */

if (!defined('ABSPATH')) {
    exit;
}

$csfn_n        = (int) $csfn_item['n'];
$csfn_is_link  = ('link' === $csfn_item['type']);
$csfn_fallback = in_array($csfn_fallback, ['number', 'globe'], true) ? $csfn_fallback : 'number';
$csfn_has_icon = $csfn_is_link && '' !== $csfn_item['favicon'];
$csfn_globe    = $csfn_is_link && !$csfn_has_icon && 'globe' === $csfn_fallback;

$csfn_classes = 'csfn-chip';
if ('note' === $csfn_item['type']) {
    $csfn_classes .= ' csfn-chip--note';
}
if ($csfn_has_icon || $csfn_globe) {
    $csfn_classes .= ' csfn-chip--has-icon';
}

if ($csfn_is_link && ($csfn_has_icon || $csfn_globe)) {
    if (!empty($csfn_item['title']) && strlen($csfn_item['title']) <= 15 && $csfn_item['title'] !== $csfn_item['domain']) {
        $csfn_label = $csfn_item['title'];
    } else {
        $csfn_domain_parts = explode('.', $csfn_item['domain']);
        $csfn_label = !empty($csfn_domain_parts) ? $csfn_domain_parts[0] : $csfn_item['domain'];
    }
} else {
    $csfn_label = (string) $csfn_n;
}

$csfn_globe_svg = '<svg class="csfn-chip__globe" viewBox="0 0 24 24" width="10" height="10" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.93 6h-2.95a15.7 15.7 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8ZM12 4c.83 1.2 1.48 2.54 1.91 4h-3.82c.43-1.46 1.08-2.8 1.91-4ZM4.26 14a7.96 7.96 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26Zm.81 2h2.95c.34 1.27.8 2.46 1.38 3.56A8.03 8.03 0 0 1 5.07 16Zm2.95-8H5.07a8.03 8.03 0 0 1 4.33-3.56A15.7 15.7 0 0 0 8.02 8ZM12 20c-.83-1.2-1.48-2.54-1.91-4h3.82c-.43 1.46-1.08 2.8-1.91 4Zm2.34-6H9.66a14.4 14.4 0 0 1 0-4h4.68a14.4 14.4 0 0 1 0 4Zm.32 5.56c.58-1.1 1.04-2.29 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56ZM16.36 14a16.5 16.5 0 0 0 0-4h3.38a7.96 7.96 0 0 1 0 4h-3.38Z"/></svg>';
?>
<sup class="csfn-cite" id="csfn-ref-<?php echo esc_attr($csfn_n); ?>">
    <a
        class="<?php echo esc_attr($csfn_classes); ?>"
        href="#csfn-src-<?php echo esc_attr($csfn_n); ?>"
        role="doc-noteref"
        aria-describedby="csfn-pop-<?php echo esc_attr($csfn_n); ?>"
        data-csfn-n="<?php echo esc_attr($csfn_n); ?>"
        data-csfn-label="<?php echo esc_attr($csfn_label); ?>"
        data-csfn-type="<?php echo esc_attr($csfn_item['type']); ?>"
        data-csfn-fallback="<?php echo esc_attr($csfn_fallback); ?>"
        data-csfn-fallback-label="<?php echo esc_attr($csfn_n); ?>"
    >
        <?php if ($csfn_has_icon) : ?>
            <img class="csfn-chip__favicon" src="<?php echo esc_url($csfn_item['favicon'], ['http', 'https', 'data']); ?>" alt="" loading="lazy" width="12" height="12" />
        <?php endif; ?>
        <?php if ($csfn_globe) : ?>
            <?php echo $csfn_globe_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?>
        <?php elseif ($csfn_is_link && 'globe' === $csfn_fallback) : ?>
            <span class="csfn-chip__globe-fallback" hidden><?php echo $csfn_globe_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
        <?php endif; ?>
        <span class="csfn-chip__text"><?php echo esc_html($csfn_label); ?></span>
    </a>
    <span
        class="csfn-pop"
        id="csfn-pop-<?php echo esc_attr($csfn_n); ?>"
        role="tooltip"
        hidden
        data-csfn-url="<?php echo $csfn_is_link ? esc_attr($csfn_item['url']) : ''; ?>"
        data-csfn-favicon="<?php echo $csfn_has_icon ? esc_attr($csfn_item['favicon']) : ''; ?>"
    >
        <?php if ('' !== $csfn_item['title']) : ?>
            <span class="csfn-pop__title"><?php echo esc_html($csfn_item['title']); ?></span>
        <?php endif; ?>
        <?php if ($csfn_is_link && '' !== $csfn_item['domain']) : ?>
            <span class="csfn-pop__domain"><?php echo esc_html($csfn_item['domain']); ?></span>
        <?php endif; ?>
        <?php if ('' !== $csfn_item['note']) : ?>
            <span class="csfn-pop__note"><?php echo esc_html($csfn_item['note']); ?></span>
        <?php endif; ?>
    </span>
</sup>
