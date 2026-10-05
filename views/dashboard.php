<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap ipgp-wrap ipgp-dashboard-page">
    <h1><?php esc_html_e('IP Gestão de Posts', 'ip-gestao-de-posts'); ?></h1>

    <div class="ipgp-toolbar">
        <label>
            <?php esc_html_e('Período', 'ip-gestao-de-posts'); ?>
            <select id="ipgp-range">
                <option value="always"><?php esc_html_e('Sempre', 'ip-gestao-de-posts'); ?></option>
                <option value="previous_quarter"><?php esc_html_e('Último trimestre', 'ip-gestao-de-posts'); ?></option>
                <option value="current_quarter"><?php esc_html_e('Este trimestre', 'ip-gestao-de-posts'); ?></option>
                <option value="previous_month"><?php esc_html_e('Último mês', 'ip-gestao-de-posts'); ?></option>
                <option value="current_month"><?php esc_html_e('Este mês', 'ip-gestao-de-posts'); ?></option>
                <option value="custom"><?php esc_html_e('Personalizado', 'ip-gestao-de-posts'); ?></option>
            </select>
        </label>

        <span class="ipgp-custom-range" hidden>
            <label>
                <?php esc_html_e('Início', 'ip-gestao-de-posts'); ?>
                <input type="date" id="ipgp-start-date">
            </label>
            <label>
                <?php esc_html_e('Fim', 'ip-gestao-de-posts'); ?>
                <input type="date" id="ipgp-end-date">
            </label>
        </span>

        <label>
            <?php esc_html_e('Tipos', 'ip-gestao-de-posts'); ?>
            <select id="ipgp-dashboard-post-types">
                <?php foreach ($post_types as $post_type => $label) : ?>
                    <option value="<?php echo esc_attr($post_type); ?>" <?php selected(in_array($post_type, $settings['enabled_post_types'], true)); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <!-- 2. KPIs Maiores -->
    <div class="ipgp-big-numbers">
        <div class="ipgp-card"><span><?php esc_html_e('Posts publicados', 'ip-gestao-de-posts'); ?></span><strong data-metric="published_posts">0</strong></div>
        <div class="ipgp-card"><span><?php esc_html_e('Autores ativos', 'ip-gestao-de-posts'); ?></span><strong data-metric="active_authors">0</strong></div>
        <div class="ipgp-card"><span><?php esc_html_e('Categorias utilizadas', 'ip-gestao-de-posts'); ?></span><strong data-metric="used_categories">0</strong></div>
        <div class="ipgp-card"><span><?php esc_html_e('Média mensal por autor', 'ip-gestao-de-posts'); ?></span><strong data-metric="monthly_average_per_author">0</strong></div>
    </div>

    <!-- 3. Meta de publicação + Heatmap de publicações -->
    <div class="ipgp-grid ipgp-top-dash-grid">
        <div id="ipgp-goal-dashboard-wrapper">
            <div id="ipgp-goal-dashboard" class="ipgp-goal-dashboard ipgp-panel" style="display: none; height: 100%;">
                <div class="ipgp-goal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; width: 100%;">
                    <h2 id="ipgp-goal-title" style="margin: 0;">Metas</h2>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <small id="ipgp-goal-period" style="color: #646970;"></small>
                        <select id="ipgp-active-goal-select" style="display: none;"></select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 24px; align-items: center;">
                    <div class="ipgp-goal-stats">
                        <div class="ipgp-goal-progress">
                            <span id="ipgp-goal-current">0</span> / <span id="ipgp-goal-target">0</span> artigos
                        </div>
                        <div class="ipgp-goal-details">
                            <div class="ipgp-goal-detail-item">
                                <span>Faltam:</span>
                                <strong id="ipgp-goal-remaining-posts">0 artigos</strong>
                            </div>
                            <div class="ipgp-goal-detail-item">
                                <span>Restam:</span>
                                <strong id="ipgp-goal-remaining-days">0 dias</strong>
                            </div>
                            <div class="ipgp-goal-detail-item">
                                <span>Necessário:</span>
                                <strong id="ipgp-goal-rate">0 artigos/dia</strong>
                            </div>
                        </div>
                    </div>
                    <div class="ipgp-goal-gauge">
                        <canvas id="ipgp-goal-chart" height="150" width="300"></canvas>
                        <div class="ipgp-goal-percentage" id="ipgp-goal-percent">0%</div>
                    </div>
                </div>
            </div>
            <div id="ipgp-no-goal-dashboard" class="ipgp-panel" style="display: none; height: 100%; align-items: center; justify-content: center; color: #a7a7a7;">
                <?php esc_html_e('Sem meta definida', 'ip-gestao-de-posts'); ?>
            </div>
        </div>

        <section class="ipgp-panel ipgp-heatmap-panel" style="height: 100%;">
            <h2><?php esc_html_e('Heatmap de publicações', 'ip-gestao-de-posts'); ?></h2>
            <div id="ipgp-heatmap" class="ipgp-heatmap-container"></div>
        </section>
    </div>

    <!-- 4. Evolução ao longo do tempo + Ranking de produtividade -->
    <div class="ipgp-grid">
        <section class="ipgp-panel">
            <h2><?php esc_html_e('Evolução ao longo do tempo', 'ip-gestao-de-posts'); ?> <small id="ipgp-evolution-period" style="color: #a7a7a7; font-weight: normal; font-size: 13px;"></small></h2>
            <div style="position: relative; width: 100%; height: 130px;">
                <canvas id="ipgp-evolution-chart"></canvas>
            </div>
        </section>
        <section class="ipgp-panel">
            <div class="ipgp-panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0;"><?php esc_html_e('Ranking de produtividade', 'ip-gestao-de-posts'); ?></h2>
            </div>
            <ul id="ipgp-productivity-list" class="ipgp-productivity-list"></ul>
        </section>
    </div>

    <!-- 5. Ranking de autores + Ranking de categorias -->
    <div class="ipgp-grid">
        <section class="ipgp-panel">
            <h2><?php esc_html_e('Ranking de autores', 'ip-gestao-de-posts'); ?></h2>
            <div style="position: relative; width: 100%; height: 130px;">
                <canvas id="ipgp-authors-chart"></canvas>
            </div>
        </section>
        <section class="ipgp-panel">
            <h2><?php esc_html_e('Ranking de categorias', 'ip-gestao-de-posts'); ?></h2>
            <div style="max-height: 300px; overflow-y: auto;">
                <div style="position: relative; width: 100%; height: 130px;">
                    <canvas id="ipgp-categories-chart"></canvas>
                </div>
            </div>
        </section>
    </div>
</div>