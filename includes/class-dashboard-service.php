<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Dashboard_Service
{
    private $settings;

    public function __construct(IPGP_Settings_Service $settings)
    {
        $this->settings = $settings;
    }

    public function get_stats($request)
    {
        $settings = $this->settings->get();
        $post_types = $this->settings->sanitize_post_types($request['post_types'] ?? $settings['enabled_post_types']);

        if (empty($post_types)) {
            $post_types = $settings['enabled_post_types'];
        }

        $range = IPGP_Date_Range::resolve(
            $request['range'] ?? 'always',
            $request['start_date'] ?? '',
            $request['end_date'] ?? ''
        );

        $cache_key = 'ipgp_dashboard_' . md5(serialize(compact('post_types', 'range')));
        $stats = get_transient($cache_key);

        if ($stats !== false) {
            return $stats;
        }

        $stats = $this->calculate_stats($post_types, $range);
        set_transient($cache_key, $stats, HOUR_IN_SECONDS);

        return $stats;
    }

    private function calculate_stats($post_types, $range)
    {
        global $wpdb;

        $where = $this->build_posts_where($post_types, $range);
        $first_last = $this->get_first_last_dates($where);

        $total_posts = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} p WHERE {$where['sql']}");
        $active_authors = (int) $wpdb->get_var("SELECT COUNT(DISTINCT p.post_author) FROM {$wpdb->posts} p WHERE {$where['sql']} AND p.post_author > 0");

        $categories_used = (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT tt.term_id)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
             INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
             WHERE {$where['sql']} AND tt.taxonomy = 'category'"
        );

        $average_start = $range['start'] ?: ($first_last['first_date'] ?? null);
        $average_end = $range['end'] ?: ($first_last['last_date'] ?? null);
        $months = IPGP_Date_Range::months_between($average_start, $average_end);
        $average = ($active_authors > 0) ? round($total_posts / $active_authors / $months, 2) : 0;

        return [
            'totals' => [
                'published_posts' => $total_posts,
                'active_authors' => $active_authors,
                'used_categories' => $categories_used,
                'monthly_average_per_author' => $average,
            ],
            'rankings' => [
                'authors' => $this->get_author_ranking($where),
                'categories' => $this->get_category_ranking($where),
            ],
            'evolution' => $this->get_evolution_data($where),
            'heatmap' => $this->get_heatmap_data($where),
            'goals' => $this->get_goals_progress($post_types),
            'range' => $range,
        ];
    }

    private function get_author_ranking($where)
    {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT p.post_author AS id, COALESCE(NULLIF(u.display_name, ''), u.user_login, CONCAT('Autor #', p.post_author)) AS label, COUNT(1) AS total
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->users} u ON u.ID = p.post_author
             WHERE {$where['sql']} AND p.post_author > 0
             GROUP BY p.post_author
             ORDER BY total DESC, label ASC
             LIMIT 10",
            ARRAY_A
        );
    }

    private function get_category_ranking($where)
    {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT t.term_id AS id, t.name AS label, COUNT(DISTINCT p.ID) AS total
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
             INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
             INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
             WHERE {$where['sql']} AND tt.taxonomy = 'category'
             GROUP BY t.term_id, t.name
             ORDER BY total DESC, label ASC
             LIMIT 50",
            ARRAY_A
        );
    }

    private function get_evolution_data($where)
    {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT DATE(p.post_date) AS date, COUNT(1) AS total
             FROM {$wpdb->posts} p
             WHERE {$where['sql']}
             GROUP BY date
             ORDER BY date ASC",
            ARRAY_A
        );
    }

    private function get_heatmap_data($where)
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT DATE_ADD(DATE(p.post_date), INTERVAL - DAYOFWEEK(p.post_date) + 1 DAY) AS date, COUNT(1) AS total
             FROM {$wpdb->posts} p
             WHERE {$where['sql']}
             GROUP BY date
             ORDER BY date ASC",
            ARRAY_A
        );

        $heatmap = [];
        foreach ($results as $row) {
            $heatmap[$row['date']] = (int) $row['total'];
        }

        return $heatmap;
    }

    private function get_goals_progress($post_types)
    {
        global $wpdb;

        $goals = $this->settings->get_goals();
        $results = [];

        foreach ($goals as $goal) {
            if (empty($goal['name']) || empty($goal['target']) || empty($goal['start_date']) || empty($goal['end_date'])) {
                continue;
            }

            $range = [
                'start' => $goal['start_date'] . ' 00:00:00',
                'end' => $goal['end_date'] . ' 23:59:59',
            ];

            $where = $this->build_posts_where($post_types, $range);

            $total_posts = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} p WHERE {$where['sql']}");

            $start_timestamp = strtotime($goal['start_date']);
            $end_timestamp = strtotime($goal['end_date']);
            $now = current_time('timestamp');

            $total_days = max(1, round(($end_timestamp - $start_timestamp) / DAY_IN_SECONDS));
            
            $days_remaining = 0;
            if ($now < $start_timestamp) {
                $days_remaining = $total_days;
            } elseif ($now <= $end_timestamp) {
                $days_remaining = max(0, round(($end_timestamp - $now) / DAY_IN_SECONDS));
            }

            $posts_remaining = max(0, $goal['target'] - $total_posts);
            $rate_needed = ($days_remaining > 0) ? round($posts_remaining / $days_remaining, 2) : 0;
            
            $percent = min(100, round(($total_posts / $goal['target']) * 100));

            $results[] = [
                'id' => $goal['id'],
                'name' => $goal['name'],
                'target' => $goal['target'],
                'current' => $total_posts,
                'percent' => $percent,
                'days_remaining' => $days_remaining,
                'posts_remaining' => $posts_remaining,
                'rate_needed' => $rate_needed,
                'start_date' => $goal['start_date'],
                'end_date' => $goal['end_date']
            ];
        }
        
        return $results;
    }

    private function get_first_last_dates($where)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            "SELECT MIN(p.post_date) AS first_date, MAX(p.post_date) AS last_date
             FROM {$wpdb->posts} p
             WHERE {$where['sql']}",
            ARRAY_A
        );

        return is_array($row) ? $row : [];
    }

    private function build_posts_where($post_types, $range)
    {
        global $wpdb;

        $post_types = array_values(array_filter(array_map('sanitize_key', $post_types)));
        $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
        $values = $post_types;
        $sql = "p.post_status = 'publish' AND p.post_type IN ({$placeholders})";

        if (! empty($range['start'])) {
            $sql .= ' AND p.post_date >= %s';
            $values[] = $range['start'];
        }

        if (! empty($range['end'])) {
            $sql .= ' AND p.post_date <= %s';
            $values[] = $range['end'];
        }

        return [
            'sql' => $wpdb->prepare($sql, $values),
            'values' => $values,
        ];
    }
}
