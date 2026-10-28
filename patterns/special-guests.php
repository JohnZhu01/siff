<?php

/**
 * Title: Special Guests
 * Slug: siff-theme/special-guests
 * Categories: featured
 * Description: Six guests in three columns with roles and load more controls.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:query {"queryId":14,"query":{"perPage":6,"pages":0,"offset":0,"postType":"guest","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[],"format":[],"excludeCurrent":null},"layout":{"type":"constrained"},"className":"siff-guests"} -->
<div class="wp-block-query siff-guests">
    <!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
    <!-- wp:post-featured-image /-->
    <!-- wp:post-title /-->
    <!-- wp:paragraph {"className":"siff-guest-role","metadata":{"bindings":{"content":{"source":"acf/field","args":{"key":"role"}}}}} -->
    <p class="siff-guest-role"></p>
    <!-- /wp:paragraph -->
    <!-- /wp:post-template -->
    <!-- wp:query-no-results -->
    <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when the query returns no results."} -->
    <p></p>
    <!-- /wp:paragraph -->
    <!-- /wp:query-no-results -->
    <!-- wp:query-pagination {"loadMore":true,"loadMoreText":"See more guests","loadingText":"Loading…","infiniteScroll":false,"updateUrl":false} -->
    <!-- wp:query-pagination-next /-->
    <!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->