<?php
$slides = get_field('festival_highlights');

if (empty($slides)) {
    if (is_admin()) {
        echo '<p>Add slides in the Festival Highlights Slider block settings.</p>';
    }
    return;
}

$block_id = 'siff-highlights-' . (
    !empty($block['id']) ? $block['id'] : wp_unique_id()
);
?>

<section
    id="<?php echo esc_attr($block_id); ?>"
    class="siff-highlights-slider swiper"
    aria-label="Festival highlights">
    <div class="swiper-wrapper">

        <?php foreach ($slides as $slide) :
            $image = $slide['slide_image'] ?? null;
            $title = $slide['slide_title'] ?? '';
            $description = $slide['slide_description'] ?? '';
            $button_text = $slide['slide_button_text'] ?? '';
            $button_url = $slide['slide_button_url'] ?? '';
        ?>

            <article class="swiper-slide siff-highlight-slide">

                <?php if (!empty($image['url'])) : ?>
                    <img
                        class="siff-highlight-image"
                        src="<?php echo esc_url($image['url']); ?>"
                        alt="<?php echo esc_attr($image['alt'] ?? ''); ?>">
                <?php endif; ?>

                <div class="siff-highlight-overlay">
                    <div class="siff-highlight-content">

                        <?php if ($title) : ?>
                            <h2>
                                <?php echo esc_html($title); ?>
                            </h2>
                        <?php endif; ?>

                        <?php if ($description) : ?>
                            <p>
                                <?php echo esc_html($description); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($button_text && $button_url) : ?>
                            <a
                                class="siff-highlight-button"
                                href="<?php echo esc_url($button_url); ?>">
                                <?php echo esc_html($button_text); ?>
                            </a>
                        <?php endif; ?>

                    </div>
                </div>

            </article>

        <?php endforeach; ?>

    </div>

    <div class="siff-highlight-controls">
        <button
            type="button"
            class="siff-highlight-prev"
            aria-label="Previous highlight">&#8592;</button>

        <div class="siff-highlight-pagination"></div>

        <button
            type="button"
            class="siff-highlight-next"
            aria-label="Next highlight">&#8594;</button>
    </div>
</section>