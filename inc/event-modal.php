<?php

// Date labels shared by cards and the modal.
function siff_event_date_parts($value)
{
    if (!preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{2}-\d{2})[,\sT]*(\d{1,2}:\d{2}(?::\d{2})?\s*(?:am|pm)?)$/i', (string) $value, $parts)) {
        return null;
    }
    $format = strpos($parts[1], '/') !== false ? '!d/m/Y' : '!Y-m-d';
    $timezone = wp_timezone();
    $date = DateTimeImmutable::createFromFormat($format, $parts[1], $timezone);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors && ($errors['warning_count'] || $errors['error_count']))) {
        return null;
    }
    return array(
        'card_date' => wp_date('l, F j', $date->getTimestamp(), $timezone),
        'date' => wp_date('j M Y', $date->getTimestamp(), $timezone),
        'time' => $parts[2],
    );
}

// Card excerpts.
function siff_clean_event_excerpt($excerpt, $post)
{
    if ($post->post_type === 'siff-event' && $post->post_excerpt === '') {
        $is_session = has_term(array('panel', 'workshop'), 'siff-event-types', $post);
        $description = get_post_meta($post->ID, $is_session ? 'short_description' : 'synopsis', true);
        $description = is_string($description) ? trim(wp_strip_all_tags(strip_shortcodes($description))) : '';
        if ($description !== '') {
            return wp_trim_words(
                $description,
                (int) apply_filters('excerpt_length', 55),
                apply_filters('excerpt_more', ' [&hellip;]')
            );
        }
        return preg_replace('/^Synopsis\s+/i', '', $excerpt);
    }
    return $excerpt;
}
add_filter('get_the_excerpt', 'siff_clean_event_excerpt', 10, 2);

function siff_page_uses_event_modal() {
    return is_page(array('events', 'schedule'));
}

// Give the Events page a class for page-specific styles.
function siff_event_page_classes($classes) {
    if (is_page('events')) {
        $classes[] = 'siff-events-page';
    }
    return $classes;
}

add_filter('body_class', 'siff_event_page_classes');

// Events wrapper.
function siff_add_events_section_class($block_content) {
    if (strpos($block_content, 'siff-event-filters') === false) {
        return $block_content;
    }

    $html = new WP_HTML_Tag_Processor($block_content);
    if ($html->next_tag(array('class_name' => 'wp-block-query'))) {
        $html->add_class('siff-events-section');
        $html->add_class('alignfull');
    }

    return $html->get_updated_html();
}

add_filter('render_block_core/query', 'siff_add_events_section_class');

// Modal markup.
function siff_render_event_modal() {
    if (!siff_page_uses_event_modal()) {
        return;
    }

    ?>
    <dialog id="siff-event-modal" aria-labelledby="siff-modal-title">
        <header class="siff-modal-header">
            <h2 id="siff-modal-title">Event details</h2>
            <button type="button" id="siff-modal-close" aria-label="Close event details">
                &times;
            </button>
        </header>

        <div class="siff-modal-scroll">
            <div class="siff-modal-layout">
                <div class="siff-modal-image">
                    <img id="siff-modal-image" alt="" hidden>
                </div>

                <div class="siff-modal-info">
                    <div id="siff-modal-meta"></div>
                </div>

                <div class="siff-modal-description siff-modal-tabs">
                    <div role="tablist" aria-label="Event information">
                        <button
                            type="button"
                            id="siff-tab-overview"
                            role="tab"
                            aria-selected="true"
                            aria-controls="siff-panel-overview"
                        >Synopsis</button>
                        <button
                            type="button"
                            id="siff-tab-details"
                            role="tab"
                            aria-selected="false"
                            aria-controls="siff-panel-details"
                            tabindex="-1"
                        >Details</button>
                    </div>

                    <div
                        id="siff-panel-overview"
                        role="tabpanel"
                        aria-labelledby="siff-tab-overview"
                        tabindex="0"
                    >
                        <div id="siff-modal-content"></div>
                    </div>
                    <div
                        id="siff-panel-details"
                        role="tabpanel"
                        aria-labelledby="siff-tab-details"
                        tabindex="0"
                        hidden
                    >
                        <div id="siff-modal-credits"></div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
    <?php
}

add_action('wp_footer', 'siff_render_event_modal');

// Assets.
function siff_enqueue_event_modal_assets() {
    if (!siff_page_uses_event_modal()) {
        return;
    }

    wp_enqueue_style(
        'siff-event-modal',
        get_theme_file_uri('/assets/css/event-modal.css'),
        array(),
        filemtime(get_theme_file_path('/assets/css/event-modal.css'))
    );

    wp_enqueue_script(
        'siff-event-modal',
        get_theme_file_uri('/assets/js/event-modal.js'),
        array(),
        filemtime(get_theme_file_path('/assets/js/event-modal.js')),
        true
    );
    siff_localize_event_modal();
}

add_action('wp_enqueue_scripts', 'siff_enqueue_event_modal_assets');

// Event data.
function siff_event_details_data($event_id) {
    $event = get_post($event_id);

    // Only expose published event posts.
    if (
        !$event ||
        $event->post_type !== 'siff-event' ||
        $event->post_status !== 'publish'
    ) {
        return null;
    }

    if (!function_exists('get_field')) {
        return null;
    }

    $event_types = get_the_terms($event_id, 'siff-event-types');
    $date = get_field('screening_date_and_time', $event_id) ?: get_field('date_and_time', $event_id);
    $date_parts = siff_event_date_parts($date);

    return array(
        'types' => is_array($event_types) ? wp_list_pluck($event_types, 'slug') : array(),
        'instructor' => get_field('instructor', $event_id),
        'instructor_profession' => get_field('instructor_profession', $event_id),
        'speaker' => get_field('speaker', $event_id),
        'speaker_profession' => get_field('speaker_profession', $event_id),
        'image' => wp_get_attachment_image_url(
            get_field('modal_image', $event_id, false),
            'large'
        ) ?: get_the_post_thumbnail_url($event_id, 'large') ?: '',
        'title' => html_entity_decode(
            get_the_title($event_id),
            ENT_QUOTES,
            get_bloginfo('charset')
        ),
        'description' => get_field('synopsis', $event_id)
            ?: get_field('short_description', $event_id),
        'date' => $date,
        'display_date' => $date_parts['date'] ?? '',
        'display_time' => strtoupper($date_parts['time'] ?? ''),
        'filmmaker' => get_field('filmmaker', $event_id),
        'director' => get_field('director', $event_id),
        'venue' => get_field('venue', $event_id),
        'duration' => get_field('runtime', $event_id)
            ?: get_field('duration', $event_id),
    );
}

function siff_localize_event_modal() {
    if (!siff_page_uses_event_modal()) {
        return;
    }

    $events = array();

    if (is_page('events')) {
        $event_posts = get_posts(array(
            'post_type' => 'siff-event',
            'post_status' => 'publish',
            'numberposts' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        foreach ($event_posts as $event_post) {
            $details = siff_event_details_data($event_post->ID);

            if ($details) {
                $events[$event_post->ID] = $details;
            }
        }
    }

    wp_localize_script('siff-event-modal', 'siffModal', array(
        'restUrl' => rest_url('siff/v1/events/'),
        'events' => $events,
    ));
}

// REST route.
function siff_register_event_routes() {
    register_rest_route('siff/v1', '/events/(?P<id>[1-9]\d*)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'siff_rest_event_details',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'siff_register_event_routes');

function siff_rest_event_details($request) {
    if (!function_exists('get_field')) {
        return new WP_Error(
            'siff_fields_unavailable',
            'Event fields are unavailable.',
            array('status' => 503)
        );
    }

    $event_id = absint($request['id']);
    $details = siff_event_details_data($event_id);

    if (!$details) {
        return new WP_Error(
            'siff_event_not_found',
            'Event not found.',
            array('status' => 404)
        );
    }

    return rest_ensure_response($details);
}

// Event links.
add_filter('render_block_core/post-title', function ($html, $parsed_block, $block) {
    $event_id = absint($block->context['postId'] ?? 0);

    if (!siff_page_uses_event_modal() || get_post_type($event_id) !== 'siff-event') {
        return $html;
    }

    // Older saved titles may have linking disabled.
    if (stripos($html, '<a ') === false) {
        $html = preg_replace(
            '/(<h[1-6]\b[^>]*>)(.*?)(<\/h[1-6]>)/s',
            '$1<a href="' . esc_url(get_permalink($event_id)) . '">$2</a>$3',
            $html,
            1
        );
    }

    $processor = new WP_HTML_Tag_Processor($html);
    if ($processor->next_tag()) {
        $processor->add_class('siff-event-trigger');
    }
    if ($processor->next_tag('A')) {
        $processor->set_attribute('data-event-id', $event_id);
        $processor->set_attribute('aria-haspopup', 'dialog');
        $processor->set_attribute('aria-controls', 'siff-event-modal');
    }

    return $processor->get_updated_html();
}, 10, 3);
