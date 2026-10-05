<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Author_Colors
{
    const OPTION = 'ipgp_author_colors';

    private $palette = [
        '#2563eb', '#dc2626', '#16a34a', '#9333ea', '#ea580c',
        '#0891b2', '#be123c', '#4f46e5', '#0f766e', '#a16207',
    ];

    public function get_all()
    {
        $colors = get_option(self::OPTION, []);

        return is_array($colors) ? $colors : [];
    }

    public function save($raw_colors)
    {
        $raw_colors = is_array($raw_colors) ? $raw_colors : [];
        $colors = [];

        foreach ($raw_colors as $user_id => $color) {
            $user_id = absint($user_id);
            $color = sanitize_hex_color($color);

            if ($user_id && $color) {
                $colors[$user_id] = $color;
            }
        }

        update_option(self::OPTION, $colors, false);

        return $colors;
    }

    public function get_color($user_id)
    {
        $user_id = absint($user_id);
        $colors = $this->get_all();

        if (isset($colors[$user_id])) {
            return $colors[$user_id];
        }

        return $this->palette[$user_id % count($this->palette)];
    }
}
