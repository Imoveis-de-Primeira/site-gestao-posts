<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap ipgp-wrap ipgp-settings-page">
    <h1><?php esc_html_e('Configurações', 'ip-gestao-de-posts'); ?></h1>

    <form id="ipgp-settings-form" class="ipgp-panel">
        <h2><?php esc_html_e('Visualização', 'ip-gestao-de-posts'); ?></h2>

        <fieldset class="ipgp-fieldset">
            <label>
                <input type="checkbox" name="show_categories" value="1" <?php checked($settings['show_categories']); ?>>
                <?php esc_html_e('Exibir categorias no hover', 'ip-gestao-de-posts'); ?>
            </label>
            <label>
                <input type="checkbox" name="show_tags" value="1" <?php checked($settings['show_tags']); ?>>
                <?php esc_html_e('Exibir tags no hover', 'ip-gestao-de-posts'); ?>
            </label>
        </fieldset>

        <p>
            <button type="submit" class="button button-primary"><?php esc_html_e('Salvar configurações', 'ip-gestao-de-posts'); ?></button>
            <span class="ipgp-save-status" aria-live="polite"></span>
        </p>
    </form>

    <div class="ipgp-panel">
        <h2 id="ipgp-goal-form-title"><?php esc_html_e('Adicionar Metas de publicação', 'ip-gestao-de-posts'); ?></h2>
        <?php $goals = $this->settings->get_goals(); ?>
        
        <form id="ipgp-goal-form">
            <input type="hidden" name="goal_id" id="ipgp-goal-id" value="">
            <fieldset class="ipgp-fieldset ipgp-goal-grid">
                <div class="ipgp-goal-col">
                    <label class="ipgp-field">
                        <?php esc_html_e('Nome da meta', 'ip-gestao-de-posts'); ?>
                        <input type="text" name="goal_name" id="ipgp-goal-name" value="" placeholder="Ex: 300 artigos em 2026" required>
                    </label>
                    <label class="ipgp-field">
                        <?php esc_html_e('Quantidade alvo', 'ip-gestao-de-posts'); ?>
                        <input type="number" name="goal_target" id="ipgp-goal-target" value="" min="1" required>
                    </label>
                </div>
                <div class="ipgp-goal-col">
                    <label class="ipgp-field">
                        <?php esc_html_e('Data inicial', 'ip-gestao-de-posts'); ?>
                        <input type="date" name="goal_start_date" id="ipgp-goal-start-date" value="" required>
                    </label>
                    <label class="ipgp-field">
                        <?php esc_html_e('Data final', 'ip-gestao-de-posts'); ?>
                        <input type="date" name="goal_end_date" id="ipgp-goal-end-date" value="" required>
                    </label>
                </div>
            </fieldset>

            <p>
                <button type="submit" id="ipgp-goal-submit-btn" class="button button-primary"><?php esc_html_e('Adicionar meta', 'ip-gestao-de-posts'); ?></button>
                <button type="button" id="ipgp-goal-cancel-btn" class="button" style="display: none;"><?php esc_html_e('Cancelar', 'ip-gestao-de-posts'); ?></button>
                <span class="ipgp-save-status" aria-live="polite"></span>
            </p>
        </form>

        <div class="ipgp-goals-list" style="margin-top: 24px;">
            <?php if (!empty($goals)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Nome', 'ip-gestao-de-posts'); ?></th>
                            <th><?php esc_html_e('Alvo', 'ip-gestao-de-posts'); ?></th>
                            <th><?php esc_html_e('Início', 'ip-gestao-de-posts'); ?></th>
                            <th><?php esc_html_e('Fim', 'ip-gestao-de-posts'); ?></th>
                            <th style="width: 140px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($goals as $g) : ?>
                            <tr>
                                <td><?php echo esc_html($g['name']); ?></td>
                                <td><?php echo esc_html($g['target']); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($g['start_date'])); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($g['end_date'])); ?></td>
                                <td style="text-align: right;">
                                    <button type="button" class="button button-small ipgp-edit-goal" 
                                        data-id="<?php echo esc_attr($g['id']); ?>"
                                        data-name="<?php echo esc_attr($g['name']); ?>"
                                        data-target="<?php echo esc_attr($g['target']); ?>"
                                        data-start="<?php echo esc_attr($g['start_date']); ?>"
                                        data-end="<?php echo esc_attr($g['end_date']); ?>">
                                        <?php esc_html_e('Editar', 'ip-gestao-de-posts'); ?>
                                    </button>
                                    <button type="button" class="button button-small ipgp-delete-goal" data-id="<?php echo esc_attr($g['id']); ?>">
                                        <?php esc_html_e('Excluir', 'ip-gestao-de-posts'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('Sem metas definidas.', 'ip-gestao-de-posts'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <form id="ipgp-author-colors-form" class="ipgp-panel">
        <h2><?php esc_html_e('Cores por autor', 'ip-gestao-de-posts'); ?></h2>
        <?php if (empty($authors)) : ?>
            <p><?php esc_html_e('Nenhum autor com posts publicados foi encontrado para os tipos selecionados.', 'ip-gestao-de-posts'); ?></p>
        <?php else : ?>
            <div class="ipgp-author-colors">
                <?php foreach ($authors as $author) : ?>
                    <label>
                        <span><?php echo esc_html($author->display_name ?: $author->user_login); ?></span>
                        <input type="color" name="colors[<?php echo esc_attr($author->ID); ?>]" value="<?php echo esc_attr($colors[$author->ID] ?? (new IPGP_Author_Colors())->get_color($author->ID)); ?>">
                    </label>
                <?php endforeach; ?>
            </div>
            <p>
                <button type="submit" class="button button-primary"><?php esc_html_e('Salvar cores', 'ip-gestao-de-posts'); ?></button>
                <span class="ipgp-save-status" aria-live="polite"></span>
            </p>
        <?php endif; ?>
    </form>
</div>
