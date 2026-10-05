<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Calendar_Service
{
    private $settings;
    private $colors;

    public function __construct(IPGP_Settings_Service $settings, IPGP_Author_Colors $colors)
    {
        $this->settings = $settings;
        $this->colors = $colors;
    }

    public function get_events($request)
    {
        $settings = $this->settings->get();
        $post_types = $this->settings->sanitize_post_types($request['post_types'] ?? $settings['enabled_post_types']);

        if (empty($post_types)) {
            $post_types = $settings['enabled_post_types'];
        }

        $start = $this->sanitize_fullcalendar_date($request['start'] ?? '');
        $end = $this->sanitize_fullcalendar_date($request['end'] ?? '');

        $query_args = [
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'no_found_rows' => true,
        ];

        if ($start || $end) {
            $date_query = [
                'column' => 'post_date',
                'inclusive' => true,
            ];

            if ($start) {
                $date_query['after'] = $start;
            }

            if ($end) {
                $date_query['before'] = $end;
            }

            $query_args['date_query'] = [$date_query];
        }

        $query = new WP_Query($query_args);
        $events = [];

        foreach ($query->posts as $post) {
            $events[] = $this->format_event($post, $settings);
        }

        return $events;
    }

    private function format_event(WP_Post $post, $settings)
    {
        $author = get_user_by('id', $post->post_author);
        $author_name = $author ? $author->display_name : sprintf(__('Autor #%d', 'ip-gestao-de-posts'), (int) $post->post_author);
        $color = $this->colors->get_color($post->post_author);

        $categories = $settings['show_categories'] ? wp_get_post_terms($post->ID, 'category', ['fields' => 'names']) : [];
        $tags = $settings['show_tags'] ? wp_get_post_terms($post->ID, 'post_tag', ['fields' => 'names']) : [];

        return [
            'id' => (int) $post->ID,
            'title' => html_entity_decode(get_the_title($post), ENT_QUOTES, get_bloginfo('charset')),
            'start' => mysql2date('c', $post->post_date, false),
            'url' => get_edit_post_link($post->ID, 'raw'),
            'display' => 'block',
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'extendedProps' => [
                'author_id' => (int) $post->post_author,
                'author_name' => $author_name,
                'color' => $color,
                'permalink' => get_permalink($post),
                'edit_link' => get_edit_post_link($post->ID, 'raw'),
                'date' => get_the_date(get_option('date_format'), $post),
                'categories' => is_wp_error($categories) ? [] : array_values($categories),
                'tags' => is_wp_error($tags) ? [] : array_values($tags),
            ],
        ];
    }

    private function sanitize_fullcalendar_date($date)
    {
        $date = sanitize_text_field($date);

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
            return substr($date, 0, 10);
        }

        return '';
    }
}
