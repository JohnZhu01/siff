<?php

// Events uses the Pages template, not a saved Events override.
function siff_events_page_template($templates)
{
    if (is_page('events')
        && in_array(get_post_meta(get_queried_object_id(), '_siff_events_blocks_version', true), array('2', '3'), true)) {
        return array('page.php');
    }
    return $templates;
}
add_filter('page_template_hierarchy', 'siff_events_page_template');

// Read the current guest and event patterns.
function siff_events_page_patterns()
{
    $patterns = array();
    foreach (array('guest' => 'special-guests', 'siff-event' => 'events-grid') as $type => $name) {
        $file = get_theme_file_path('/patterns/' . $name . '.php');
        if (!is_readable($file)) {
            return array();
        }
        ob_start();
        include $file;
        $blocks = parse_blocks(ob_get_clean());
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'core/query') {
                $patterns[$type] = $block;
                break;
            }
        }
    }
    return $patterns;
}

// Update query loops, including ones inside synced patterns.
function siff_update_events_blocks($blocks, $patterns, &$changed, $seen = array())
{
    foreach ($blocks as &$block) {
        $type = $block['attrs']['query']['postType'] ?? '';
        if ($block['blockName'] === 'core/query' && isset($patterns[$type])) {
            if (serialize_block($block) !== serialize_block($patterns[$type])) {
                $block = $patterns[$type];
                $changed = true;
            }
        } elseif ($block['blockName'] === 'core/block') {
            $ref = absint($block['attrs']['ref'] ?? 0);
            if (!$ref || in_array($ref, $seen, true)) {
                continue;
            }
            $pattern = get_post($ref);
            if (!$pattern || $pattern->post_type !== 'wp_block') {
                continue;
            }
            $pattern_changed = false;
            $updated = siff_update_events_blocks(
                parse_blocks($pattern->post_content), $patterns, $pattern_changed, array_merge($seen, array($ref))
            );
            if ($pattern_changed) {
                // Detach this copy so other pages keep their synced pattern.
                $wrapper = '<!-- wp:group --><div class="wp-block-group">'
                    . serialize_blocks($updated) . '</div><!-- /wp:group -->';
                $block = parse_blocks($wrapper)[0];
                $changed = true;
            }
        } elseif (!empty($block['innerBlocks'])) {
            $block['innerBlocks'] = siff_update_events_blocks($block['innerBlocks'], $patterns, $changed, $seen);
        }
    }
    return $blocks;
}

// Ignore card images when checking for the page image.
function siff_events_page_has_image($blocks)
{
    foreach ($blocks as $block) {
        if ($block['blockName'] === 'core/query') {
            continue;
        }
        if ($block['blockName'] === 'core/post-featured-image') {
            return true;
        }
        if (!empty($block['innerBlocks']) && siff_events_page_has_image($block['innerBlocks'])) {
            return true;
        }
    }
    return false;
}

// Run once when an administrator opens WordPress.
function siff_update_events_page()
{
    if (!current_user_can('manage_options') || wp_doing_ajax()) {
        return;
    }
    $page = get_page_by_path('events');
    $version = $page ? get_post_meta($page->ID, '_siff_events_blocks_version', true) : '';
    if (!$page || trim($page->post_content) === '' || !current_user_can('edit_post', $page->ID)
        || $version === '3') {
        return;
    }
    if (function_exists('wp_check_post_lock') && wp_check_post_lock($page->ID)) {
        return;
    }
    $changed = false;
    $blocks = parse_blocks($page->post_content);
    if (!in_array($version, array('1', '2'), true)) {
        $patterns = siff_events_page_patterns();
        if (count($patterns) !== 2) {
            return;
        }
        $blocks = siff_update_events_blocks($blocks, $patterns, $changed);
    }
    if (!siff_events_page_has_image($blocks)) {
        array_unshift($blocks, parse_blocks('<!-- wp:post-featured-image /-->')[0]);
        $changed = true;
    }
    if ($changed) {
        if (!metadata_exists('post', $page->ID, '_siff_events_blocks_backup')) {
            update_post_meta($page->ID, '_siff_events_blocks_backup', wp_slash($page->post_content));
        }
        wp_save_post_revision($page->ID);
        $result = wp_update_post(array('ID' => $page->ID, 'post_content' => wp_slash(serialize_blocks($blocks))), true);
        if (is_wp_error($result)) {
            return;
        }
    }
    $assigned = get_page_template_slug($page->ID);
    if ($assigned) {
        update_post_meta($page->ID, '_siff_events_template_backup', $assigned);
    }
    update_post_meta($page->ID, '_wp_page_template', 'default');
    update_post_meta($page->ID, '_siff_events_blocks_version', '3');
}
add_action('admin_init', 'siff_update_events_page');
