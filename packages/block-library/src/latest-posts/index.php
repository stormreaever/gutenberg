<?php
/**
 * Server-side rendering of the `core/latest-posts` block.
 *
 * @package WordPress
 */

/**
 * The excerpt length set by the Latest Posts core block
 * set at render time and used by the block itself.
 *
 * @var int
 */
$block_core_latest_posts_excerpt_length = 0;

/**
 * Callback for the excerpt_length filter used by
 * the Latest Posts block at render time.
 *
 * @return int Returns the global $block_core_latest_posts_excerpt_length variable
 *             to allow the excerpt_length filter respect the Latest Block setting.
 */
function block_core_latest_posts_get_excerpt_length() {
	global $block_core_latest_posts_excerpt_length;
	return $block_core_latest_posts_excerpt_length;
}

/**
 * Renders the `core/latest-posts` block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with latest posts added.
 */
function render_block_core_latest_posts( $attributes ) {
	global $post, $block_core_latest_posts_excerpt_length;

	$args = array(
		'posts_per_page'   => $attributes['postsToShow'],
		'post_status'      => 'publish',
		'order'            => $attributes['order'],
		'orderby'          => $attributes['orderBy'],
		'suppress_filters' => false,
	);

	$block_core_latest_posts_excerpt_length = $attributes['excerptLength'];
	add_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

	if ( isset( $attributes['categories'] ) ) {
		$args['category__in'] = array_column( $attributes['categories'], 'id' );
	}
	if ( isset( $attributes['selectedAuthor'] ) ) {
		$args['author'] = $attributes['selectedAuthor'];
	}

	$recent_posts = get_posts( $args );

	$list_items_markup = '';

	foreach ( $recent_posts as $post ) {
		$list_items_markup .= '<li class="latest-post-item">';

		if ( isset( $attributes['displayPostThumbnail'] ) && $attributes['displayPostThumbnail'] && has_post_thumbnail($post) ) {
			$list_items_markup .= '<div class="post-image">' . get_the_post_thumbnail($post, 'thumbnail') . '</div>';
		}

		$list_items_markup .= '<div class="post-content">';

		if ( $attributes['displayFeaturedImage'] && has_post_thumbnail( $post ) && false ) {
			$image_style = '';
			if ( isset( $attributes['featuredImageSizeWidth'] ) ) {
				$image_style .= sprintf( 'max-width:%spx;', $attributes['featuredImageSizeWidth'] );
			}
			if ( isset( $attributes['featuredImageSizeHeight'] ) ) {
				$image_style .= sprintf( 'max-height:%spx;', $attributes['featuredImageSizeHeight'] );
			}

			$image_classes = 'wp-block-latest-posts__featured-image';
			if ( isset( $attributes['featuredImageAlign'] ) ) {
				$image_classes .= ' align' . $attributes['featuredImageAlign'];
			}

			$featured_image = get_the_post_thumbnail(
				$post,
				$attributes['featuredImageSizeSlug'],
				array(
					'style' => $image_style,
				)
			);
			if ( $attributes['addLinkToFeaturedImage'] ) {
				$featured_image = sprintf(
					'<a href="%1$s">%2$s</a>',
					$post_link,
					$featured_image
				);
			}
			$list_items_markup .= sprintf(
				'<div class="%1$s">%2$s</div>',
				$image_classes,
				$featured_image
			);
		}

		$title = get_the_title( $post );
		if ( ! $title ) {
			$title = __( '(no title)' );
		}
		$list_items_markup .= sprintf(
			'<h3 class="post-list-title"><a href="%1$s">%2$s</a></h3>',
			esc_url( get_permalink( $post ) ),
			$title
		);

		if (
			( isset( $attributes['displayPostCategories'] ) && $attributes['displayPostCategories'] ) ||
			( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] )
		) {
			$showing_post_meta = true;
		} else {
			$showing_post_meta = false;
		}

		if ($showing_post_meta) {
			$list_items_markup .= '<div class="post-meta">';
		}

		if ( isset( $attributes['displayPostCategories'] ) && $attributes['displayPostCategories'] ) {
			$categories = get_the_category($post);
			$category_links = [];
			foreach($categories as $category) {
				$category_link = sprintf(
					'<a href="%1$s" alt="%2$s">%3$s</a>',
					esc_url( get_category_link( $category->term_id ) ),
					esc_attr( sprintf( __( 'View all posts in %s', 'textdomain' ), $category->name ) ),
					esc_html( $category->name )
				);
				$category_links[] = $category_link;
			}
			$list_items_markup .= sprintf('<span class="cat-links">%1$s</span>',
				implode(', ', $category_links)
			);
		}

		if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
			$list_items_markup .= sprintf(
				'<time datetime="%1$s" class="wp-block-latest-posts__post-date">%2$s</time>',
				esc_attr( get_the_date( 'c', $post ) ),
				esc_html( get_the_date( '', $post ) )
			);
		}

		if ($showing_post_meta) {
			$list_items_markup .= '</div><!-- /.post-meta -->';
		}

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
			&& isset( $attributes['displayPostContentRadio'] ) && 'excerpt' === $attributes['displayPostContentRadio'] ) {

			$trimmed_excerpt = get_the_excerpt( $post );

			$list_items_markup .= sprintf(
				'<div class="wp-block-latest-posts__post-excerpt">%1$s</div>',
				$trimmed_excerpt
			);
		}

		if ( isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent']
			&& isset( $attributes['displayPostContentRadio'] ) && 'full_post' === $attributes['displayPostContentRadio'] ) {
			$list_items_markup .= sprintf(
				'<div class="wp-block-latest-posts__post-full-content">%1$s</div>',
				wp_kses_post( html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) ) )
			);
		}

		$list_items_markup .= '</div><!-- /.post-content -->';

		$list_items_markup .= "</li>\n";
	}

	remove_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

	$class = 'wp-block-latest-posts__list';

	if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
		$class .= ' is-grid';
	}

	if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
		$class .= ' columns-' . $attributes['columns'];
	}

	if ( isset( $attributes['displayPostCategories'] ) && $attributes['displayPostCategories'] ) {
		$class .= ' has-categories';
	}

	if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
		$class .= ' has-dates';
	}

	if ( isset( $attributes['displayPostThumbnail'] ) && $attributes['displayPostThumbnail'] ) {
		$class .= ' has-thumbnail';
	}

	if ( isset( $attributes['className'] ) ) {
		$class .= ' ' . $attributes['className'];
	}

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $class ) );

	return sprintf(
		'<ul %1$s>%2$s</ul>',
		$wrapper_attributes,
		$list_items_markup
	);
}

/**
 * Registers the `core/latest-posts` block on server.
 */
function register_block_core_latest_posts() {
	register_block_type_from_metadata(
		__DIR__ . '/latest-posts',
		array(
			'attributes'      => array(
				'align'                   => array(
					'type' => 'string',
					'enum' => array( 'left', 'center', 'right', 'wide', 'full' ),
				),
				'className'               => array(
					'type' => 'string',
				),
				'categories'              => array(
					'type' => 'string',
				),
				'postsToShow'             => array(
					'type'    => 'number',
					'default' => 5,
				),
				'displayPostContent'      => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'displayPostContentRadio' => array(
					'type'    => 'string',
					'default' => 'excerpt',
				),
				'excerptLength'           => array(
					'type'    => 'number',
					'default' => 55,
				),
				'displayPostCategories'   => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'displayPostDate'         => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'displayPostThumbnail'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'postLayout'              => array(
					'type'    => 'string',
					'default' => 'list',
				),
				'columns'                 => array(
					'type'    => 'number',
					'default' => 3,
				),
				'order'                   => array(
					'type'    => 'string',
					'default' => 'desc',
				),
				'orderBy'                 => array(
					'type'    => 'string',
					'default' => 'date',
				),
				'displayFeaturedImage'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'featuredImageAlign'      => array(
					'type' => 'string',
					'enum' => array( 'left', 'center', 'right' ),
				),
				'featuredImageSizeSlug'   => array(
					'type'    => 'string',
					'default' => 'thumbnail',
				),
				'featuredImageSizeWidth'  => array(
					'type'    => 'number',
					'default' => null,
				),
				'featuredImageSizeHeight' => array(
					'type'    => 'number',
					'default' => null,
				),
			),
			'render_callback' => 'render_block_core_latest_posts',
		)
	);
}
add_action( 'init', 'register_block_core_latest_posts' );

/**
 * Handles outdated versions of the `core/latest-posts` block by converting
 * attribute `categories` from a numeric string to an array with key `id`.
 *
 * This is done to accommodate the changes introduced in #20781 that sought to
 * add support for multiple categories to the block. However, given that this
 * block is dynamic, the usual provisions for block migration are insufficient,
 * as they only act when a block is loaded in the editor.
 *
 * TODO: Remove when and if the bottom client-side deprecation for this block
 * is removed.
 *
 * @param array $block A single parsed block object.
 *
 * @return array The migrated block object.
 */
function block_core_latest_posts_migrate_categories( $block ) {
	if (
		'core/latest-posts' === $block['blockName'] &&
		! empty( $block['attrs']['categories'] ) &&
		is_string( $block['attrs']['categories'] )
	) {
		$block['attrs']['categories'] = array(
			array( 'id' => absint( $block['attrs']['categories'] ) ),
		);
	}

	return $block;
}
add_filter( 'render_block_data', 'block_core_latest_posts_migrate_categories' );
