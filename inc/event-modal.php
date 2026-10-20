<?php

function siff_page_uses_event_modal() {
    return is_page(array('events', 'schedule'));
}

// Event modal.
function siff_render_event_modal() {
    if (!siff_page_uses_event_modal()) {
        return;
    }

    ?>
    <dialog id="siff-event-modal" aria-labelledby="siff-modal-title">
        <button type="button" id="siff-modal-close" aria-label="Close event details">
            &times;
        </button>

        <div class="siff-modal-layout">
            <h2 id="siff-modal-title">Event details</h2>

            <div class="siff-modal-image">
                <img id="siff-modal-image" alt="" hidden>
            </div>

            <div class="siff-modal-info">
                <div id="siff-modal-meta"></div>

                <p id="siff-modal-person" hidden></p>
            </div>

            <div class="siff-modal-description">
                <h3 id="siff-modal-description-title">Description</h3>

                <div id="siff-modal-content"></div>
            </div>
        </div>
    </dialog>
    <?php
}

add_action('wp_footer', 'siff_render_event_modal');

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

function siff_localize_event_modal()
{
    if (!siff_page_uses_event_modal()) {
        return;
    }

    $events = array();

    if (is_page('events')) {
        $event_ids = get_posts(array(
            'post_type' => 'siff-event',
            'post_status' => 'publish',
            'numberposts' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
        ));

        foreach ($event_ids as $event_id) {
            $details = siff_event_details_data($event_id);

            if ($details) {
                $events[$event_id] = $details;
            }
        }
    }

    wp_localize_script('siff-event-modal', 'siffModal', array(
        'restUrl' => rest_url('siff/v1/events/'),
        'events' => $events,
    ));
}

function siff_register_event_routes()
{
    register_rest_route('siff/v1', '/events/(?P<id>[1-9]\d*)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'siff_rest_event_details',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'siff_register_event_routes');

function siff_rest_event_details($request)
{
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

    $event_types = wp_get_post_terms(
        $event_id,
        'siff-event-types',
        array('fields' => 'slugs')
    );

    if (is_wp_error($event_types)) {
        $event_types = array();
    }
    return array(
        'types' => $event_types,
        'instructor' => get_field('instructor', $event_id),
        'instructor_profession' => get_field('instructor_profession', $event_id),
        'speaker' => get_field('speaker', $event_id),
        'speaker_profession' => get_field('speaker_profession', $event_id),
        'image' => get_the_post_thumbnail_url($event_id, 'large') ?: '',
        'title' => html_entity_decode(
            get_the_title($event_id),
            ENT_QUOTES,
            get_bloginfo('charset')
        ),
        'description' => get_field('synopsis', $event_id)
            ?: get_field('short_description', $event_id),
        'date' => get_field('screening_date_and_time', $event_id)
            ?: get_field('date_and_time', $event_id),
        'filmmaker' => get_field('filmmaker', $event_id),
        'director' => get_field('director', $event_id),
        'venue' => get_field('venue', $event_id),
        'duration' => get_field('runtime', $event_id)
            ?: get_field('duration', $event_id),
    );
}

// Connect event titles to the modal.
add_filter('render_block_core/post-title', function ($html, $parsed_block, $block) {
    $classes = preg_split('/\s+/', trim($parsed_block['attrs']['className'] ?? ''));
    $event_id = absint($block->context['postId'] ?? 0);

    if (!in_array('siff-event-trigger', $classes, true)
        || get_post_type($event_id) !== 'siff-event') {
        return $html;
    }

    $processor = new WP_HTML_Tag_Processor($html);
    if ($processor->next_tag('A')) {
        $processor->set_attribute('data-event-id', $event_id);
        $processor->set_attribute('aria-haspopup', 'dialog');
        $processor->set_attribute('aria-controls', 'siff-event-modal');
    }

    return $processor->get_updated_html();
}, 10, 3);


