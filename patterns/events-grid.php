<?php
/**
 * Title: Events Grid
 * Slug: siff-theme/events-grid
 * Categories: featured
 * Description: Searchable event cards with category filters and the details modal.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:query {"align":"full","queryId":2,"query":{"perPage":50,"pages":0,"offset":0,"postType":"siff-event","order":"desc","orderBy":"date","inherit":false,"excludeCurrent":null},"className":"siff-events-section","metadata":{"categories":["featured"],"patternName":"siff-theme/events-grid","name":"Events Grid"}} -->
<div class="wp-block-query alignfull siff-events-section">
    <!-- wp:group {"className":"siff-event-filters","layout":{"type":"flex","flexWrap":"wrap"}} -->
    <div class="wp-block-group siff-event-filters">
        <!-- wp:buttons -->
        <div class="wp-block-buttons">
            <!-- wp:button {"className":"siff-filter-all"} -->
            <div class="wp-block-button siff-filter-all">
                <a class="wp-block-button__link wp-element-button">All</a>
            </div>
            <!-- /wp:button -->
            <!-- wp:button {"className":"siff-filter-film"} -->
            <div class="wp-block-button siff-filter-film">
                <a class="wp-block-button__link wp-element-button">Films</a>
            </div>
            <!-- /wp:button -->
            <!-- wp:button {"className":"siff-filter-workshop"} -->
            <div class="wp-block-button siff-filter-workshop">
                <a class="wp-block-button__link wp-element-button">Workshops</a>
            </div>
            <!-- /wp:button -->
            <!-- wp:button {"className":"siff-filter-panel"} -->
            <div class="wp-block-button siff-filter-panel">
                <a class="wp-block-button__link wp-element-button">Panels</a>
            </div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
        <!-- wp:search {"label":"Search","showLabel":false,"width":100,"widthUnit":"%","buttonText":"Search","className":"siff-event-search"} /-->
    </div>
    <!-- /wp:group -->
    <!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
    <!-- wp:group {"className":"siff-event-card","layout":{"type":"constrained"}} -->
    <div class="wp-block-group siff-event-card">
        <!-- wp:post-title {"isLink":true,"className":"siff-event-trigger"} /-->
        <!-- wp:post-featured-image {"aspectRatio":"3/4","scale":"contain","sizeSlug":"large"} /-->
        <!-- wp:paragraph {"className":"siff-event-excerpt-label"} -->
        <p class="siff-event-excerpt-label">Synopsis</p>
        <!-- /wp:paragraph -->
        <!-- wp:post-excerpt {"excerptLength":20,"moreText":""} /-->
        <!-- wp:siff/event-date /-->
        <!-- wp:post-terms {"term":"siff-event-types"} /-->
    </div>
    <!-- /wp:group -->
    <!-- /wp:post-template -->
    <!-- wp:query-no-results -->
    <!-- wp:paragraph -->
    <p>No events are currently available.</p>
    <!-- /wp:paragraph -->
    <!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
