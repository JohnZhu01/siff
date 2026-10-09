<?php
/**
 * Title: Featured Vendors Banner
 * Slug: siff-theme/featured-vendors-banner
 * Categories: 
 */
?>
<!-- wp:group {"tagName":"section","metadata":{"categories":["featured"],"name":"Featured Vendors"},"align":"full","style":{"elements":{"link":{"color":{"text":"var:preset|color|custom-deep-blue"}}},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}},"backgroundColor":"white","textColor":"custom-deep-blue","layout":{"type":"constrained","contentSize":"1000px"}} -->
<section class="wp-block-group alignfull has-custom-deep-blue-color has-white-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)"><!-- wp:heading {"style":{"typography":{"fontSize":"22px"}}} -->
<h2 class="wp-block-heading" style="font-size:22px">Featured vendors</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"14px"},"color":{"text":"#58636b"},"elements":{"link":{"color":{"text":"#58636b"}}}}} -->
<h3 class="wp-block-heading has-text-color has-link-color" style="color:#58636b;font-size:14px">Platinum vendors</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":3101,"query":{"perPage":100,"pages":0,"offset":0,"postType":"siff-vendor","order":"asc","orderBy":"title","inherit":false,"taxQuery":{"include":{"siff-vendor-tier":[25]}},"excludeCurrent":null}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"siff-featured-vendors__grid","backgroundColor":"white","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"200px"}} -->
<!-- wp:post-featured-image {"width":"100%","height":"140px","scale":"contain","sizeSlug":"full","style":{"spacing":{"padding":{"top":"4px","bottom":"4px","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->

<!-- wp:separator {"className":"is-style-wide","style":{"color":{"background":"#dce3e7"}}} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#dce3e7;color:#dce3e7"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"14px"},"color":{"text":"#84672f"},"elements":{"link":{"color":{"text":"#84672f"}}}}} -->
<h3 class="wp-block-heading has-text-color has-link-color" style="color:#84672f;font-size:14px">Gold vendors</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":3102,"query":{"perPage":100,"pages":0,"offset":0,"postType":"siff-vendor","order":"asc","orderBy":"title","inherit":false,"taxQuery":{"include":{"siff-vendor-tier":[24]}},"excludeCurrent":null}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"siff-featured-vendors__grid","backgroundColor":"white","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"200px"}} -->
<!-- wp:post-featured-image {"width":"100%","height":"100px","scale":"contain","sizeSlug":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}}} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></section>
<!-- /wp:group -->