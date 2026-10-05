<?php

if (! defined('ABSPATH')) {
    exit;
}

class IPGP_Date_Range
{
    public static function resolve($range, $start = '', $end = '')
    {
        $range = sanitize_key($range ?: 'always');
        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);

        switch ($range) {
            case 'current_quarter':
                $quarter = (int) ceil(((int) $now->format('n')) / 3);
                $start_month = (($quarter - 1) * 3) + 1;
                $from = $now->setDate((int) $now->format('Y'), $start_month, 1)->setTime(0, 0, 0);
                $to = $from->modify('+3 months -1 second');
                break;

            case 'previous_quarter':
                $quarter = (int) ceil(((int) $now->format('n')) / 3);
                $start_month = (($quarter - 1) * 3) + 1;
                $from = $now->setDate((int) $now->format('Y'), $start_month, 1)->setTime(0, 0, 0)->modify('-3 months');
                $to = $from->modify('+3 months -1 second');
                break;

            case 'current_month':
                $from = $now->modify('first day of this month')->setTime(0, 0, 0);
                $to = $from->modify('+1 month -1 second');
                break;

            case 'previous_month':
                $from = $now->modify('first day of previous month')->setTime(0, 0, 0);
                $to = $from->modify('+1 month -1 second');
                break;

            case 'custom':
                $from = self::parse_date($start, true);
                $to = self::parse_date($end, false);
                break;

            case 'always':
            default:
                $from = null;
                $to = null;
                break;
        }

        return [
            'range' => $range,
            'start' => $from ? $from->format('Y-m-d H:i:s') : null,
            'end' => $to ? $to->format('Y-m-d H:i:s') : null,
        ];
    }

    public static function months_between($start, $end)
    {
        if (! $start || ! $end) {
            return 1;
        }

        $from = new DateTimeImmutable($start, wp_timezone());
        $to = new DateTimeImmutable($end, wp_timezone());
        $months = (((int) $to->format('Y') - (int) $from->format('Y')) * 12) + ((int) $to->format('n') - (int) $from->format('n')) + 1;

        return max(1, $months);
    }

    private static function parse_date($date, $start_of_day)
    {
        $date = sanitize_text_field($date);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $time = $start_of_day ? '00:00:00' : '23:59:59';

        return new DateTimeImmutable($date . ' ' . $time, wp_timezone());
    }
}
