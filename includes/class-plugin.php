<?php

if (! defined('ABSPATH')) {
    exit;
}

final class IPGP_Plugin
{
    private static $instance = null;

    public $settings;
    public $dashboard;
    public $calendar;
    public $assets;
    public $admin_menu;
    public $ajax;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->settings = new IPGP_Settings_Service();
        $colors = new IPGP_Author_Colors();
        $this->dashboard = new IPGP_Dashboard_Service($this->settings);
        $this->calendar = new IPGP_Calendar_Service($this->settings, $colors);
        $this->assets = new IPGP_Assets($this->settings);
        $this->admin_menu = new IPGP_Admin_Menu($this->settings);
        $this->ajax = new IPGP_Ajax_Controller($this->settings, $this->dashboard, $this->calendar, $colors);
    }

    public function init()
    {
        load_plugin_textdomain('ip-gestao-de-posts', false, dirname(plugin_basename(IPGP_FILE)) . '/languages');

        $this->admin_menu->init();
        $this->assets->init();
        $this->ajax->init();
    }

    public static function activate()
    {
        $role = get_role('administrator');

        if ($role && ! $role->has_cap('ipgp_manage_settings')) {
            $role->add_cap('ipgp_manage_settings');
        }
    }
}
