<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Settings_Service
{
    const SETTINGS_OPTION = 'ipgp_settings';
    const GOAL_OPTION = 'ipgp_goal';

    public function defaults()
    {
        return [
            'show_categories' => true,
            'show_tags' => true,
            'enabled_post_types' => ['post'],
            'week_starts_on' => 0,
        ];
    }

    public function get()
    {
        $settings = get_option(self::SETTINGS_OPTION, []);
        $settings = is_array($settings) ? $settings : [];
        $settings = wp_parse_args($settings, $this->defaults());

        $settings['show_categories'] = (bool) $settings['show_categories'];
        $settings['show_tags'] = (bool) $settings['show_tags'];
        $settings['enabled_post_types'] = $this->sanitize_post_types($settings['enabled_post_types']);
        $settings['week_starts_on'] = 0;

        return $settings;
    }

    public function save($raw)
    {
        $settings = [
            'show_categories' => ! empty($raw['show_categories']),
            'show_tags' => ! empty($raw['show_tags']),
            'enabled_post_types' => $this->sanitize_post_types($raw['enabled_post_types'] ?? []),
            'week_starts_on' => 0,
        ];

        if (empty($settings['enabled_post_types'])) {
            $settings['enabled_post_types'] = ['post'];
        }

        update_option(self::SETTINGS_OPTION, $settings, false);

        return $settings;
    }

    public function get_available_post_types()
    {
        $allowed = ['post', 'page', 'lp_lancamento', 'lp_lancamentos'];
        $objects = get_post_types(['public' => true], 'objects');
        $post_types = [];

        foreach ($objects as $name => $object) {
            if (in_array($name, $allowed, true)) {
                $post_types[$name] = $object->labels->name ?: $object->label;
            }
        }

        // Se LP de Lancamentos nao foi encontrado dinamicamente, garanta que mostre apenas o que a regra pede
        // 'lp_lancamento' costuma ser um custom post type. Vamos deixar o filtro apenas retornar se estiver registrado.

        return $post_types;
    }

    public function sanitize_post_types($post_types)
    {
        $post_types = is_array($post_types) ? $post_types : [$post_types];
        $available = array_keys($this->get_available_post_types());
        $clean = [];

        foreach ($post_types as $post_type) {
            $post_type = sanitize_key($post_type);

            if (in_array($post_type, $available, true)) {
                $clean[] = $post_type;
            }
        }

        return array_values(array_unique($clean));
    }

    public function get_goals()
    {
        $goals = get_option(self::GOAL_OPTION, []);
        return is_array($goals) ? array_values($goals) : [];
    }

    public function add_or_update_goal($raw)
    {
        $goals = $this->get_goals();
        
        $id = !empty($raw['goal_id']) ? sanitize_text_field($raw['goal_id']) : uniqid('goal_');
        $updated = false;

        foreach ($goals as &$goal) {
            if ($goal['id'] === $id) {
                $goal['name'] = sanitize_text_field($raw['goal_name'] ?? '');
                $goal['target'] = max(0, absint($raw['goal_target'] ?? 0));
                $goal['start_date'] = $this->sanitize_date($raw['goal_start_date'] ?? '');
                $goal['end_date'] = $this->sanitize_date($raw['goal_end_date'] ?? '');
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $goals[] = [
                'id' => $id,
                'name' => sanitize_text_field($raw['goal_name'] ?? ''),
                'target' => max(0, absint($raw['goal_target'] ?? 0)),
                'start_date' => $this->sanitize_date($raw['goal_start_date'] ?? ''),
                'end_date' => $this->sanitize_date($raw['goal_end_date'] ?? ''),
            ];
        }

        update_option(self::GOAL_OPTION, $goals, false);
        $this->clear_dashboard_cache();

        return $goals;
    }
    
    public function delete_goal($id)
    {
        $goals = $this->get_goals();
        $goals = array_filter($goals, function($goal) use ($id) {
            return $goal['id'] !== $id;
        });
        
        update_option(self::GOAL_OPTION, array_values($goals), false);
        $this->clear_dashboard_cache();
        
        return array_values($goals);
    }

    public function clear_dashboard_cache()
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like('_transient_ipgp_dashboard_') . '%',
                $wpdb->esc_like('_transient_timeout_ipgp_dashboard_') . '%'
            )
        );
    }

    private function sanitize_date($date)
    {
        $date = sanitize_text_field($date);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '';
    }
}
