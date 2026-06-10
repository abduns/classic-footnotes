<?php

/**
 * Sources footer bar template (inline in content).
 *
 * @var array[] $csfn_items    Footnote items.
 * @var string  $csfn_fallback Icon fallback style (number | globe).
 * @var string  $csfn_view_mode View mode for the sources list.
 */

if (!defined('ABSPATH')) {
    exit;
}

$csfn_fallback  = in_array($csfn_fallback, ['number', 'globe'], true) ? $csfn_fallback : 'number';
$csfn_view_mode = in_array($csfn_view_mode, ['compact', 'detailed'], true) ? $csfn_view_mode : 'detailed';
$csfn_footer_class = 'csfn-sources-footer-bar csfn-sources-footer-bar--' . $csfn_view_mode;
$csfn_globe_svg = '<svg viewBox="0 0 24 24" width="10" height="10" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.93 6h-2.95a15.7 15.7 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8ZM12 4c.83 1.2 1.48 2.54 1.91 4h-3.82c.43-1.46 1.08-2.8 1.91-4ZM4.26 14a7.96 7.96 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26Zm.81 2h2.95c.34 1.27.8 2.46 1.38 3.56A8.03 8.03 0 0 1 5.07 16Zm2.95-8H5.07a8.03 8.03 0 0 1 4.33-3.56A15.7 15.7 0 0 0 8.02 8ZM12 20c-.83-1.2-1.48-2.54-1.91-4h3.82c-.43 1.46-1.08 2.8-1.91 4Zm2.34-6H9.66a14.4 14.4 0 0 1 0-4h4.68a14.4 14.4 0 0 1 0 4Zm.32 5.56c.58-1.1 1.04-2.29 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56ZM16.36 14a16.5 16.5 0 0 0 0-4h3.38a7.96 7.96 0 0 1 0 4h-3.38Z"/></svg>';
?>
<!-- Clean Sources Footer Bar -->
<div class="<?php echo esc_attr($csfn_footer_class); ?>">
    <button class="csfn-sources-bar" id="csfn-sources-bar-trigger" aria-haspopup="dialog" aria-expanded="false">
        <span class="csfn-sources-bar__avatars">
            <?php
            $csfn_max_icons = 3;
            $csfn_icon_count = 0;
            foreach ($csfn_items as $csfn_item) :
                if ($csfn_icon_count >= $csfn_max_icons) break;
                $csfn_is_link = ('link' === $csfn_item['type']);
                $csfn_has_icon = $csfn_is_link && '' !== $csfn_item['favicon'];
                if ($csfn_has_icon) :
                    $csfn_icon_count++;
            ?>
                <img class="csfn-sources-bar__favicon" src="<?php echo esc_url($csfn_item['favicon'], ['http', 'https', 'data']); ?>" alt="" loading="lazy" width="16" height="16" />
            <?php
                endif;
            endforeach;
            
            if ($csfn_icon_count === 0) :
            ?>
                <span class="csfn-sources-bar__fallback-circle">
                    <?php if ('globe' === $csfn_fallback) : ?>
                        <?php echo $csfn_globe_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?>
                    <?php else : ?>
                        <span><?php echo esc_html((int) $csfn_items[0]['n']); ?></span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </span>
        <span class="csfn-sources-bar__text">
            <?php
            $csfn_count = count($csfn_items);
            printf(
                /* translators: %d: number of sources */
                esc_html(_n('%d source', '%d sources', $csfn_count, 'ai-footnotes')),
                (int) $csfn_count
            );
            ?>
        </span>
    </button>
</div>
