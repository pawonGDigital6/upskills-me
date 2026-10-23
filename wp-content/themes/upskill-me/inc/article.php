<?php
/**
 * Article helpers for single blog posts.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_article_contents' ) ) {
	/**
	 * Give every h2 in an article an id and list them for "In this article".
	 *
	 * A heading may name itself differently in the list through a
	 * data-toc-label attribute (the Key Takeaways panel lists as its label).
	 *
	 * @param string $html Rendered post content.
	 * @return array{html:string,items:array<int,array{id:string,label:string}>}
	 */
	function upskill_article_contents( $html ) {
		$items = array();
		$used  = array();

		$html = preg_replace_callback(
			'/<h2([^>]*)>(.*?)<\/h2>/is',
			static function ( $match ) use ( &$items, &$used ) {
				$attrs = $match[1];
				$label = trim( wp_strip_all_tags( $match[2] ) );

				if ( preg_match( '/data-toc-label="([^"]+)"/', $attrs, $custom ) ) {
					$label = html_entity_decode( $custom[1], ENT_QUOTES );
				}

				if ( preg_match( '/\sid="([^"]+)"/', $attrs, $existing ) ) {
					$id = $existing[1];
				} else {
					$id    = sanitize_title( $label );
					$base  = $id;
					$count = 2;

					while ( isset( $used[ $id ] ) ) {
						$id = $base . '-' . $count++;
					}

					$attrs .= ' id="' . esc_attr( $id ) . '"';
				}

				$used[ $id ] = true;
				$items[]     = array(
					'id'    => $id,
					'label' => $label,
				);

				return '<h2' . $attrs . '>' . $match[2] . '</h2>';
			},
			$html
		);

		return array(
			'html'  => $html,
			'items' => $items,
		);
	}
}

if ( ! function_exists( 'upskill_contain_editor_text' ) ) {
	/**
	 * Wrap the editor's own text in the reading container, leaving the theme's
	 * ACF blocks free to run full width.
	 *
	 * Works on the raw post content, before `the_content`, so the filters run
	 * once: each run of consecutive core blocks (paragraphs, headings, lists,
	 * images…) is wrapped in $open/$close as plain HTML between blocks, which
	 * the block parser passes through untouched. ACF blocks are full-width
	 * sections with their own container and are left as they are, except Key
	 * Takeaways, which is drawn inside an article body and stays with the text.
	 * Synced patterns (core/block) hold such sections too and are left alone.
	 *
	 * @param string $content Raw post content.
	 * @param string $open    Markup that opens the text container.
	 * @param string $close   Markup that closes it.
	 * @return string Post content, ready for `the_content`.
	 */
	function upskill_contain_editor_text( $content, $open, $close ) {
		if ( ! has_blocks( $content ) ) {
			return '' === trim( $content ) ? '' : $open . $content . $close;
		}

		$inline = array( 'acf-block/key-takeaways' );
		$output = '';
		$text   = '';

		foreach ( parse_blocks( $content ) as $block ) {
			$name = (string) $block['blockName'];

			$section = 'core/block' === $name || ( 0 === strpos( $name, 'acf-block/' ) && ! in_array( $name, $inline, true ) );

			if ( ! $section ) {
				$text .= serialize_block( $block );
				continue;
			}

			if ( '' !== trim( $text ) ) {
				$output .= $open . $text . $close;
			}

			$text    = '';
			$output .= serialize_block( $block );
		}

		if ( '' !== trim( $text ) ) {
			$output .= $open . $text . $close;
		}

		return $output;
	}
}

if ( ! function_exists( 'upskill_listing_page_url' ) ) {
	/**
	 * The page that lists a post type (Blogs, Case Studies, Guides).
	 *
	 * Those listings are pages under Resources; the posts page setting wins for
	 * blogs when it is set.
	 *
	 * @param string $post_type Post type.
	 * @return string
	 */
	function upskill_listing_page_url( $post_type = 'post' ) {
		$paths = array(
			'post'       => 'resources/blogs',
			'case_study' => 'resources/case-studies',
			'guide'      => 'resources/guides',
		);

		if ( 'post' === $post_type && get_option( 'page_for_posts' ) ) {
			return get_permalink( (int) get_option( 'page_for_posts' ) );
		}

		$page = isset( $paths[ $post_type ] ) ? get_page_by_path( $paths[ $post_type ] ) : null;

		return $page ? get_permalink( $page ) : home_url( '/' );
	}
}
