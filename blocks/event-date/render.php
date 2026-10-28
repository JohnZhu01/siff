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
    <?php if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{2}-\d{2})[,\sT]*(\d{1,2}:\d{2}(?::\d{2})?\s*(?:am|pm)?)$/i', $date, $date_parts)) : ?>
        <?php
        $date_format = strpos($date_parts[1], '/') !== false ? '!d/m/Y' : '!Y-m-d';
        $event_date = DateTimeImmutable::createFromFormat($date_format, $date_parts[1], wp_timezone());
        $display_date = $event_date
            ? wp_date('l, F j', $event_date->getTimestamp(), wp_timezone())
            : $date_parts[1];
        ?>
        <span><strong class="siff-event-date-label">Date:</strong> <?php echo esc_html($display_date); ?></span>
        <span><strong class="siff-event-date-label">Time:</strong> <?php echo esc_html($date_parts[2]); ?></span>
    <?php else : ?>
        <?php echo esc_html($date); ?>
    <?php endif; ?>
</p>
