<?php

// Register the vendor card block.
function siff_register_vendor_card()
{
    register_block_type(
        get_theme_file_path('blocks/vendor-card'),
        array(
            'render_callback' => 'siff_render_vendor_card',
        )
    );
}

add_action('init', 'siff_register_vendor_card');

function siff_render_vendor_card($attributes, $content, $block)
{
    // Get the vendor ID from the Query Loop.
    $vendor_id = absint($block->context['postId'] ?? 0);

    if (! $vendor_id || 'siff-vendor' !== get_post_type($vendor_id) || 'publish' !== get_post_status($vendor_id)) {
        return '';
    }

    ob_start();
    include get_theme_file_path('blocks/vendor-card/render.php');
    return ob_get_clean();
}



function siff_register_event_date_block()
{
    wp_register_script(
        'siff-event-date-editor',
        get_theme_file_uri('/blocks/event-date/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor'),
        filemtime(get_theme_file_path('/blocks/event-date/editor.js')),
        true
    );

    register_block_type(
        get_theme_file_path('/blocks/event-date')
    );
}

add_action('init', 'siff_register_event_date_block');



// Register the Event Map Locator Block.
function siff_register_event_locator()
{
    register_block_type(
        'siff/event-locator',
        [
            'title' => 'Event Locator Map',
            'category' => 'theme',
            'icon' => 'location-alt',
            'description' => 'Display a location of the event in the map.',
            'render_callback' => function () {
                return sprintf(
                    '<div id="siff-event-locator"></div>'
                );
            },
            'supports' => [
                'autoRegister' => true,
            ]
        ]
    );
}

add_action('init', 'siff_register_event_locator');



// Registering custom ACF block

function siff_register_festival_highlights_block()
{
    if (function_exists('acf_register_block_type')) {
        acf_register_block_type(array(
            'name'            => 'festival-highlights',
            'title'           => 'Festival Highlights Slider',
            'description'     => 'A featured slider for SIFF festival highlights.',
            'render_template' => 'blocks/festival-highlights/render.php',
            'category'        => 'design',
            'icon'            => 'format-gallery',
            'keywords'        => array(
                'festival',
                'highlights',
                'slider'
            ),
            'mode'            => 'preview',
            'supports'        => array(
                'align' => array('wide', 'full'),
                'anchor' => true
            ),
            'enqueue_assets'  => 'siff_enqueue_festival_highlights_assets'
        ));
    }
}
add_action('acf/init', 'siff_register_festival_highlights_block');