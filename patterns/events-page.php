<?php
/**
 * Title: Events Page
 * Slug: siff-theme/events-page
 * Categories: featured
 * Description: The Events page with guests, event search, filters, and featured vendors.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:post-featured-image /-->
<!-- wp:paragraph -->
<p><br>Join us for a weekend of exciting events, unique experiences, and opportunities to connect with the community. Whether you’re coming for the main attractions or exploring something new, there’s something for everyone to enjoy.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons">
    <!-- wp:button -->
    <div class="wp-block-button">
        <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(home_url('/buy-tickets/')); ?>">Buy Tickets</a>
    </div>
    <!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Special Guests</h2>
<!-- /wp:heading -->
<!-- wp:pattern {"slug":"siff-theme/special-guests"} /-->
<!-- wp:pattern {"slug":"siff-theme/events-grid"} /-->
<!-- wp:pattern {"slug":"siff-theme/featured-vendors-banner"} /-->
