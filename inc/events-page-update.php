<?php

// Read the current guest and event patterns.
function siff_events_page_patterns()
{
    $patterns = array();
    foreach (array('guest' => 'special-guests', 'siff-event' => 'events-grid') as $type => $name) {
        ob_start();
        include get_theme_file_path('/patterns/' . $name . '.php');
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

// Run once when an administrator opens WordPress.
function siff_update_events_page()
{
    if (!current_user_can('manage_options') || wp_doing_ajax()) {
        return;
    }
    $page = get_page_by_path('events');
    if (!$page || !current_user_can('edit_post', $page->ID)
        || get_post_meta($page->ID, '_siff_events_blocks_version', true) === '1') {
        return;
    }
    if (function_exists('wp_check_post_lock') && wp_check_post_lock($page->ID)) {
        return;
    }
    $patterns = siff_events_page_patterns();
    if (count($patterns) !== 2) {
        return;
    }
    $changed = false;
    $blocks = siff_update_events_blocks(parse_blocks($page->post_content), $patterns, $changed);
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
    update_post_meta($page->ID, '_siff_events_blocks_version', '1');
}
add_action('admin_init', 'siff_update_events_page');
