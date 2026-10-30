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
$date_parts = siff_event_date_parts($date);
?>

<p <?php echo get_block_wrapper_attributes(array('class' => 'siff-event-date')); ?>>
    <?php if ($date_parts) : ?>
        <span><strong class="siff-event-date-label">Date:</strong> <?php echo esc_html($date_parts['card_date']); ?></span>
        <span><strong class="siff-event-date-label">Time:</strong> <?php echo esc_html($date_parts['time']); ?></span>
    <?php else : ?>
        <?php echo esc_html($date); ?>
    <?php endif; ?>
</p>