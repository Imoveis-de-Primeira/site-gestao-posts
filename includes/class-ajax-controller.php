<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Ajax_Controller
{
    private $settings;
    private $dashboard;
    private $calendar;
    private $colors;

    public function __construct(
        IPGP_Settings_Service $settings,
        IPGP_Dashboard_Service $dashboard,
        IPGP_Calendar_Service $calendar,
        IPGP_Author_Colors $colors
    ) {
        $this->settings = $settings;
        $this->dashboard = $dashboard;
        $this->calendar = $calendar;
        $this->colors = $colors;
    }

    public function init()
    {
        add_action('wp_ajax_ipgp_dashboard_stats', [$this, 'dashboard_stats']);
        add_action('wp_ajax_ipgp_calendar_events', [$this, 'calendar_events']);
        add_action('wp_ajax_ipgp_filter_options', [$this, 'filter_options']);
        add_action('wp_ajax_ipgp_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_ipgp_save_goal', [$this, 'save_goal']);
        add_action('wp_ajax_ipgp_delete_goal', [$this, 'delete_goal']);
        add_action('wp_ajax_ipgp_save_author_colors', [$this, 'save_author_colors']);
    }

    public function dashboard_stats()
    {
        $this->verify('edit_posts');
        wp_send_json_success($this->dashboard->get_stats(wp_unslash($_REQUEST)));
    }

    public function calendar_events()
    {
        $this->verify('edit_posts');
        wp_send_json($this->calendar->get_events(wp_unslash($_REQUEST)));
    }

    public function filter_options()
    {
        $this->verify('edit_posts');

        wp_send_json_success([
            'post_types' => $this->settings->get_available_post_types(),
            'settings' => $this->settings->get(),
        ]);
    }

    public function save_settings()
    {
        $this->verify('manage_options');
        $settings = $this->settings->save(wp_unslash($_POST));
        wp_send_json_success(['settings' => $settings]);
    }

    public function save_goal()
    {
        $this->verify('manage_options');
        $goals = $this->settings->add_or_update_goal(wp_unslash($_POST));
        wp_send_json_success(['goals' => $goals]);
    }

    public function delete_goal()
    {
        $this->verify('manage_options');
        $id = sanitize_text_field(wp_unslash($_POST['goal_id'] ?? ''));
        $goals = $this->settings->delete_goal($id);
        wp_send_json_success(['goals' => $goals]);
    }

    public function save_author_colors()
    {
        $this->verify('manage_options');
        $colors = $this->colors->save(wp_unslash($_POST['colors'] ?? []));
        wp_send_json_success(['colors' => $colors]);
    }

    private function verify($capability)
    {
        check_ajax_referer('ipgp_admin_nonce', 'nonce');

        if (! current_user_can($capability)) {
            wp_send_json_error(['message' => __('Permissao insuficiente.', 'ip-gestao-de-posts')], 403);
        }
    }
}
