<?php
/**
 * Title: Featured Vendors Banner
 * Slug: siff-theme/featured-vendors-banner
 * Categories: featured
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"tagName":"section","metadata":{"categories":["featured"],"name":"Featured Vendors"},"align":"full","className":"siff-featured-vendors-banner","style":{"elements":{"link":{"color":{"text":"var:preset|color|custom-accents"}}},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"backgroundColor":"custom-panel","textColor":"custom-text","layout":{"type":"constrained","contentSize":"1000px"}} -->
<section class="wp-block-group alignfull has-custom-text-color has-custom-panel-background-color siff-featured-vendors-banner has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)"><!-- wp:heading {"textColor":"custom-text","style":{"typography":{"fontSize":"1.375rem"}}} -->
<h2 class="wp-block-heading has-custom-text-color has-text-color" style="font-size:1.375rem">Featured vendors</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":".875rem"},"color":{"text":"#d5d8dc"},"elements":{"link":{"color":{"text":"#d5d8dc"}}}}} -->
<h3 class="wp-block-heading has-text-color has-link-color" style="color:#d5d8dc;font-size:.875rem">Platinum vendors</h3>
<!-- /wp:heading -->

<?php
$tier = get_term_by('slug', 'platinum', 'siff-vendor-tier');
if ($tier && !is_wp_error($tier)) :
    $query = array(
        'queryId' => 3101,
        'query' => array(
            'perPage' => 100,
            'pages' => 0,
            'offset' => 0,
            'postType' => 'siff-vendor',
            'order' => 'asc',
            'orderBy' => 'title',
            'inherit' => false,
            'excludeCurrent' => null,
        ),
    );
    $query['query']['taxQuery'] = array('siff-vendor-tier' => array((int) $tier->term_id));
?>
<!-- wp:query <?php echo wp_json_encode($query); ?> -->
<div class="wp-block-query"><!-- wp:post-template {"className":"siff-featured-vendors__grid","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"200px"}} -->
<!-- wp:post-featured-image {"width":"100%","height":"140px","scale":"contain","sizeSlug":"full","style":{"spacing":{"padding":{"top":"4px","bottom":"4px","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->
<?php endif; ?>

<!-- wp:separator {"className":"is-style-wide","style":{"color":{"background":"#353A40"}}} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#353A40;color:#353A40"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":".875rem"},"color":{"text":"#d8bb78"},"elements":{"link":{"color":{"text":"#d8bb78"}}}}} -->
<h3 class="wp-block-heading has-text-color has-link-color" style="color:#d8bb78;font-size:.875rem">Gold vendors</h3>
<!-- /wp:heading -->

<?php
$tier = get_term_by('slug', 'gold', 'siff-vendor-tier');
if ($tier && !is_wp_error($tier)) :
    $query = array(
        'queryId' => 3102,
        'query' => array(
            'perPage' => 100,
            'pages' => 0,
            'offset' => 0,
            'postType' => 'siff-vendor',
            'order' => 'asc',
            'orderBy' => 'title',
            'inherit' => false,
            'excludeCurrent' => null,
        ),
    );
    $query['query']['taxQuery'] = array('siff-vendor-tier' => array((int) $tier->term_id));
?>
<!-- wp:query <?php echo wp_json_encode($query); ?> -->
<div class="wp-block-query"><!-- wp:post-template {"className":"siff-featured-vendors__grid","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"200px"}} -->
<!-- wp:post-featured-image {"width":"100%","height":"100px","scale":"contain","sizeSlug":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->
<?php endif; ?></section>
<!-- /wp:group -->
