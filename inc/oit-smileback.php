<?php
/**
 * SmileBack CSAT reviews service.
 *
 * Fetches published reviews for the OptimizedIT SmileBack account directly
 * from SmileBack's keyless widget data endpoint -- the same JSON the official
 * widget's JS consumes -- so reviews can be rendered in our own components
 * instead of embedding SmileBack's styled widget. Result is cached in a
 * transient. The endpoint needs only the public widget "app" id (no auth)
 * and returns the CSAT score, reaction count, and the published comments.
 *
 * @package OptimizedIT
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('OIT_SMILEBACK_APP')) {
    // Public SmileBack widget id (from the website widget snippet).
    define('OIT_SMILEBACK_APP', 'e90e5bcabbdd424f8ab9c768c89b915a');
}

const OIT_SMILEBACK_TRANSIENT = 'oit_smileback_csat';
const OIT_SMILEBACK_TTL       = 12 * HOUR_IN_SECONDS;

/**
 * Return SmileBack CSAT data: score, reaction count, and reviews.
 *
 * Never throws and never leaves the page broken: on any fetch/parse failure
 * it returns the last good cached value if present, otherwise an empty set
 * (callers fall back to manual content).
 *
 * @param bool $force Bypass the cache and refetch.
 * @return array{score: float|null, count: int, reviews: array<int, array{quote:string,name:string,company:string,created:string}>}
 */
function oit_smileback_reviews(bool $force = false): array {
    $empty = ['score' => null, 'count' => 0, 'reviews' => []];

    if (!$force) {
        $cached = get_transient(OIT_SMILEBACK_TRANSIENT);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $app = (string) apply_filters('oit_smileback_app', OIT_SMILEBACK_APP);
    $url = 'https://d2ybfz51gt58l0.cloudfront.net/csat/cdn/widget/data/v2/'
        . rawurlencode($app)
        . '/?callback=WidgetCallbackCSAT&comments=true';

    $response = wp_remote_get($url, [
        'timeout' => 8,
        'headers' => [
            'Accept'     => 'application/javascript, */*',
            'User-Agent' => 'OptimizedIT/SmileBack',
        ],
    ]);

    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
        $prev = get_transient(OIT_SMILEBACK_TRANSIENT);
        return is_array($prev) ? $prev : $empty;
    }

    // Strip the JSONP wrapper -> WidgetCallbackCSAT({...});
    $body = trim(wp_remote_retrieve_body($response));
    if (preg_match('/^[A-Za-z0-9_]+\((.*)\);?\s*$/s', $body, $m)) {
        $body = $m[1];
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        $prev = get_transient(OIT_SMILEBACK_TRANSIENT);
        return is_array($prev) ? $prev : $empty;
    }

    $reviews = [];
    foreach (($data['comments'] ?? []) as $c) {
        if (!is_array($c)) {
            continue;
        }
        $quote = trim((string) ($c['comment'] ?? ''));
        if ($quote === '') {
            continue;
        }
        $reviews[] = [
            'quote'   => sanitize_text_field($quote),
            'name'    => sanitize_text_field((string) ($c['name'] ?? '')),
            'company' => sanitize_text_field((string) ($c['company'] ?? '')),
            'created' => sanitize_text_field((string) ($c['created'] ?? '')),
        ];
    }

    $result = [
        'score'   => isset($data['net_csat_score']) ? (float) $data['net_csat_score'] : null,
        'count'   => isset($data['net_amount_reactions']) ? (int) $data['net_amount_reactions'] : count($reviews),
        'reviews' => $reviews,
    ];

    set_transient(OIT_SMILEBACK_TRANSIENT, $result, OIT_SMILEBACK_TTL);

    return $result;
}

/**
 * Filter, order, and limit the SmileBack reviews for display.
 *
 * Shared by the testimonial blocks so their Source/order/filter controls
 * behave identically. Pulls from the cached set, then applies (in order):
 * filtering (require company / minimum length), ordering, and a count cap.
 *
 * @param array{max?:int, order?:string, require_company?:bool, min_length?:int} $args
 * @return array<int, array{quote:string,name:string,company:string,created:string}>
 */
function oit_smileback_select_reviews(array $args = []): array {
    $args = array_merge([
        'max'             => 12,
        'order'           => 'newest', // newest | random | default
        'require_company' => false,
        'min_length'      => 0,
    ], $args);

    $reviews = oit_smileback_reviews()['reviews'];

    // Filter.
    $reviews = array_values(array_filter($reviews, static function ($r) use ($args) {
        if (!empty($args['require_company']) && empty($r['company'])) {
            return false;
        }
        if ((int) $args['min_length'] > 0 && mb_strlen($r['quote']) < (int) $args['min_length']) {
            return false;
        }
        return true;
    }));

    // Order.
    if ($args['order'] === 'random') {
        shuffle($reviews);
    } elseif ($args['order'] === 'newest') {
        usort($reviews, static function ($a, $b) {
            return strcmp((string) $b['created'], (string) $a['created']);
        });
    } elseif ($args['order'] === 'oldest') {
        usort($reviews, static function ($a, $b) {
            return strcmp((string) $a['created'], (string) $b['created']);
        });
    }
    // 'default' keeps the feed's own order.

    // Limit.
    $max = (int) $args['max'];
    if ($max > 0) {
        $reviews = array_slice($reviews, 0, $max);
    }

    return $reviews;
}

/**
 * Clear the cached SmileBack data (e.g. after publishing new reviews).
 */
function oit_smileback_clear_cache(): void {
    delete_transient(OIT_SMILEBACK_TRANSIENT);
}
