<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap ipgp-wrap ipgp-calendar-page">
    <h1><?php esc_html_e('Calendário Editorial', 'ip-gestao-de-posts'); ?></h1>

    <div class="ipgp-toolbar" style="display: flex; justify-content: space-between;">
        <div style="display: flex; gap: 12px; align-items: end;">
            <label>
                <?php esc_html_e('Tipos', 'ip-gestao-de-posts'); ?>
                <select id="ipgp-calendar-post-types">
                    <?php foreach ($post_types as $post_type => $label) : ?>
                        <option value="<?php echo esc_attr($post_type); ?>" <?php selected(in_array($post_type, $settings['enabled_post_types'], true)); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div id="ipgp-calendar-total" style="font-weight: 600; font-size: 16px; align-self: center;">
            Total: 0 artigos
        </div>
    </div>

    <div class="ipgp-calendar-layout">
        <div id="ipgp-calendar"></div>
        <aside class="ipgp-legend-panel">
            <h2><?php esc_html_e('Autores', 'ip-gestao-de-posts'); ?></h2>
            <div id="ipgp-author-legend" class="ipgp-author-legend"></div>
        </aside>
    </div>
</div>
