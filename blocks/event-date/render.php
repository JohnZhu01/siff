<?php

if (!defined('ABSPATH')) {
    exit;
}

$event_id = absint($block->context['postId'] ?? 0);

if (
    !$event_id ||
    get_post_type($event_id) !== 'siff-event' ||
    !function_exists('get_field')
) {
    return;
}

$date = get_field('screening_date_and_time', $event_id)
    ?: get_field('date_and_time', $event_id);

if (!$date) {
    return;
}
?>

<p <?php echo get_block_wrapper_attributes(array('class' => 'siff-event-date')); ?>>
    <?php echo esc_html($date); ?>
</p>