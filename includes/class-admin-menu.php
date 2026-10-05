<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Admin_Menu
{
    const SLUG_DASHBOARD = 'ipgp-dashboard';
    const SLUG_CALENDAR = 'ipgp-calendar';
    const SLUG_SETTINGS = 'ipgp-settings';

    private $settings;

    public function __construct(IPGP_Settings_Service $settings)
    {
        $this->settings = $settings;
    }

    public function init()
    {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu()
    {
        add_menu_page(
            __('IP Gestão de Posts', 'ip-gestao-de-posts'),
            __('IP Gestão de Posts', 'ip-gestao-de-posts'),
            'edit_posts',
            self::SLUG_DASHBOARD,
            [$this, 'render_dashboard'],
            'dashicons-calendar-alt',
            26
        );

        add_submenu_page(
            self::SLUG_DASHBOARD,
            __('Dashboard', 'ip-gestao-de-posts'),
            __('Dashboard', 'ip-gestao-de-posts'),
            'edit_posts',
            self::SLUG_DASHBOARD,
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            self::SLUG_DASHBOARD,
            __('Calendário', 'ip-gestao-de-posts'),
            __('Calendário', 'ip-gestao-de-posts'),
            'edit_posts',
            self::SLUG_CALENDAR,
            [$this, 'render_calendar']
        );

        add_submenu_page(
            self::SLUG_DASHBOARD,
            __('Configurações', 'ip-gestao-de-posts'),
            __('Configurações', 'ip-gestao-de-posts'),
            'manage_options',
            self::SLUG_SETTINGS,
            [$this, 'render_settings']
        );
    }

    public function render_dashboard()
    {
        $settings = $this->settings->get();
        $post_types = $this->settings->get_available_post_types();
        require IPGP_PATH . 'views/dashboard.php';
    }

    public function render_calendar()
    {
        $settings = $this->settings->get();
        $post_types = $this->settings->get_available_post_types();
        require IPGP_PATH . 'views/calendar.php';
    }

    public function render_settings()
    {
        $settings = $this->settings->get();
        $post_types = $this->settings->get_available_post_types();
        $colors = (new IPGP_Author_Colors())->get_all();
        $authors = $this->get_authors_for_settings($settings['enabled_post_types']);
        require IPGP_PATH . 'views/settings.php';
    }

    private function get_authors_for_settings($post_types)
    {
        global $wpdb;

        $post_types = $this->settings->sanitize_post_types($post_types);
        $placeholders = implode(',', array_fill(0, count($post_types), '%s'));

        if (empty($placeholders)) {
            return [];
        }

        $sql = $wpdb->prepare(
            "SELECT DISTINCT u.ID, u.display_name, u.user_login
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->users} u ON u.ID = p.post_author
             WHERE p.post_status = 'publish' AND p.post_type IN ({$placeholders})
             ORDER BY u.display_name ASC",
            $post_types
        );

        return $wpdb->get_results($sql);
    }
}
