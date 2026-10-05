<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Assets
{
    private $settings;

    public function __construct(IPGP_Settings_Service $settings)
    {
        $this->settings = $settings;
    }

    public function init()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue($hook)
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';

        if (! in_array($page, [IPGP_Admin_Menu::SLUG_DASHBOARD, IPGP_Admin_Menu::SLUG_CALENDAR, IPGP_Admin_Menu::SLUG_SETTINGS], true)) {
            return;
        }

        wp_enqueue_style('ipgp-admin', IPGP_URL . 'assets/css/admin.css', [], IPGP_VERSION);
        wp_enqueue_script('ipgp-admin-common', IPGP_URL . 'assets/js/settings.js', ['jquery'], IPGP_VERSION, true);

        $settings = $this->settings->get();
        $common = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ipgp_admin_nonce'),
            'settings' => $settings,
            'i18n' => [
                'loading' => __('Carregando...', 'ip-gestao-de-posts'),
                'error' => __('Nao foi possivel carregar os dados.', 'ip-gestao-de-posts'),
            ],
        ];

        if (IPGP_Admin_Menu::SLUG_DASHBOARD === $page) {
            wp_enqueue_script('ipgp-chartjs', IPGP_URL . 'assets/vendor/chartjs/chart.umd.js', [], IPGP_VERSION, true);
            wp_enqueue_script('ipgp-dashboard', IPGP_URL . 'assets/js/dashboard.js', ['jquery', 'ipgp-chartjs'], IPGP_VERSION, true);
            wp_localize_script('ipgp-dashboard', 'IPGP', $common);
        }

        if (IPGP_Admin_Menu::SLUG_CALENDAR === $page) {
            wp_enqueue_style('ipgp-fullcalendar', IPGP_URL . 'assets/vendor/fullcalendar/index.global.css', [], IPGP_VERSION);
            wp_enqueue_script('ipgp-fullcalendar', IPGP_URL . 'assets/vendor/fullcalendar/index.global.js', [], IPGP_VERSION, true);
            wp_enqueue_script('ipgp-calendar', IPGP_URL . 'assets/js/calendar.js', ['jquery', 'ipgp-fullcalendar'], IPGP_VERSION, true);
            wp_localize_script('ipgp-calendar', 'IPGP', $common);
        }

        if (IPGP_Admin_Menu::SLUG_SETTINGS === $page) {
            wp_localize_script('ipgp-admin-common', 'IPGP', $common);
        }
    }
}
