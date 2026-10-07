<?php
/**
 * Shared render helpers for ACF blocks and template parts.
 *
 * Everything that repeats across the design — block chrome, the pill button,
 * the section header, images, icons, term links — is built here exactly once so
 * every render.php stays declarative.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_block_attributes' ) ) {
	/**
	 * Build the outer attribute string for an ACF block wrapper.
	 *
	 * Handles the block's anchor, the editor's className/align values and the
	 * shared "Block Options" tab. Nothing here forces a value: a block whose
	 * options tab is untouched keeps the defaults baked into its own markup.
	 *
	 * @param array  $block         The block settings and attributes.
	 * @param string $extra_classes Space separated block specific classes.
	 * @return string Attribute string, already escaped, with a leading space.
	 */
	function upskill_block_attributes( $block, $extra_classes = '' ) {
		$attributes = '';

		if ( ! empty( $block['anchor'] ) ) {
			$attributes .= ' id="' . esc_attr( $block['anchor'] ) . '"';
		}

		$classes  = array( 'acf-block', $extra_classes );
		$settings = upskill_block_settings();

		if ( ! empty( $block['className'] ) ) {
			$classes[] = $block['className'];
		}

		if ( ! empty( $block['align'] ) ) {
			$classes[] = 'align' . $block['align'];
		}

		$classes[]   = $settings['classes'];
		$attributes .= ' class="' . esc_attr( trim( implode( ' ', array_filter( $classes ) ) ) ) . '"';

		if ( $settings['style'] ) {
			$attributes .= ' style="' . esc_attr( $settings['style'] ) . '"';
		}

		return $attributes;
	}
}

if ( ! function_exists( 'upskill_block_settings' ) ) {
	/**
	 * Resolve the shared "Block Options" tab into classes and inline custom
	 * properties.
	 *
	 * Every control on that tab sits behind an explicit "Override" switch; with
	 * it off nothing is emitted, so the block keeps its designed spacing and
	 * surface. Values are written as custom properties and applied by
	 * src/global/scss/layout/_block-settings.scss at the right breakpoint.
	 *
	 * @return array{classes:string,style:string}
	 */
	function upskill_block_settings() {
		$classes = array();
		$style   = '';
		$spacing = get_field( 'spacing' );

		foreach ( array( 'desktop', 'mobile' ) as $device ) {
			if ( empty( $spacing[ $device ]['status'] ) ) {
				continue;
			}

			foreach ( array( 'top', 'bottom' ) as $side ) {
				$value = isset( $spacing[ $device ][ $side ] ) ? $spacing[ $device ][ $side ] : '';

				// 0 is a meaningful override (it butts two sections together),
				// so only a genuinely empty field is skipped.
				if ( '' === $value || null === $value ) {
					continue;
				}

				$classes[] = sprintf( 'has-p%s-%s', $side[0], $device );
				$style    .= sprintf( '--upskill-p%s-%s:%dpx;', $side[0], $device, (int) $value );
			}
		}

		$background = get_field( 'background' );

		if ( ! empty( $background['status'] ) ) {
			// Colour pickers can return rgba() as well as hex, so validate the
			// shape rather than relying on sanitize_hex_color() alone.
			$color = isset( $background['color'] ) ? trim( (string) $background['color'] ) : '';

			if ( $color && preg_match( '/^(#[0-9a-f]{3,8}|rgba?\([\d\s.,%]+\))$/i', $color ) ) {
				$classes[] = 'has-background-color';
				$style    .= '--upskill-block-background:' . $color . ';';
			}

			if ( ! empty( $background['scheme'] ) ) {
				$classes[] = 'is-' . sanitize_html_class( $background['scheme'] );
			}
		}

		return array(
			'classes' => implode( ' ', $classes ),
			'style'   => $style,
		);
	}
}

if ( ! function_exists( 'upskill_inline_kses' ) ) {
	/**
	 * The inline tags an editor may use inside a heading or short text field.
	 *
	 * `<em>` is how a heading marks its highlighted phrase — "Get a
	 * <em>head start.</em>" — which the design draws in the brand colour.
	 *
	 * @return array
	 */
	function upskill_inline_kses() {
		return array(
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'span'   => array( 'class' => array() ),
		);
	}
}

if ( ! function_exists( 'upskill_heading' ) ) {
	/**
	 * Render a heading, skipping it entirely when the field is empty.
	 *
	 * @param string $text    Heading text; see upskill_inline_kses().
	 * @param string $tag     Heading tag.
	 * @param string $classes Classes for the heading.
	 * @return void
	 */
	function upskill_heading( $text, $tag = 'h2', $classes = '' ) {
		if ( ! $text ) {
			return;
		}

		$tag = in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' ), true ) ? $tag : 'h2';

		printf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			esc_attr( $tag ),
			esc_attr( trim( 'heading ' . $classes ) ),
			wp_kses( $text, upskill_inline_kses() )
		);
	}
}

if ( ! function_exists( 'upskill_get_icon' ) ) {
	/**
	 * Return an inline SVG icon from images/icons.
	 *
	 * The icons have had their colours rewritten to currentColor, so an icon
	 * inherits the colour of whatever it sits in. Memoised per request.
	 *
	 * @param string $name    Icon file name, without the extension.
	 * @param string $classes Classes applied to the <svg>.
	 * @return string SVG markup, or '' when the icon does not exist.
	 */
	function upskill_get_icon( $name, $classes = '' ) {
		static $cache = array();

		$name = sanitize_file_name( str_replace( '.svg', '', (string) $name ) );

		if ( ! isset( $cache[ $name ] ) ) {
			$path = get_template_directory() . '/images/icons/' . $name . '.svg';

			$cache[ $name ] = is_readable( $path ) ? trim( (string) file_get_contents( $path ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		}

		if ( ! $cache[ $name ] ) {
			return '';
		}

		// Icons are always decorative here: the accessible name comes from the
		// surrounding link or button.
		$attrs = 'aria-hidden="true" focusable="false"';

		if ( $classes ) {
			$attrs .= ' class="' . esc_attr( $classes ) . '"';
		}

		return preg_replace( '/^<svg/', '<svg ' . $attrs, $cache[ $name ], 1 );
	}
}

if ( ! function_exists( 'upskill_icon' ) ) {
	/**
	 * Echo an inline SVG icon.
	 *
	 * @param string $name    Icon file name.
	 * @param string $classes Classes applied to the <svg>.
	 * @return void
	 */
	function upskill_icon( $name, $classes = '' ) {
		echo upskill_get_icon( $name, $classes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file.
	}
}

if ( ! function_exists( 'upskill_link_attributes' ) ) {
	/**
	 * The href/target/rel attributes for an ACF link array.
	 *
	 * @param array $link ACF link array.
	 * @return string Escaped attribute string with a leading space.
	 */
	function upskill_link_attributes( $link ) {
		$attributes = ' href="' . esc_url( $link['url'] ) . '"';

		if ( ! empty( $link['target'] ) ) {
			$attributes .= ' target="' . esc_attr( $link['target'] ) . '"';

			if ( '_blank' === $link['target'] ) {
				$attributes .= ' rel="noopener"';
			}
		}

		return $attributes;
	}
}

if ( ! function_exists( 'upskill_button' ) ) {
	/**
	 * Render an ACF link as the site's pill button.
	 *
	 * Figma draws every call to action as a pill holding the label and a small
	 * round "nest" carrying an icon. Both live inside one link.
	 *
	 * @param array|false $link ACF link array (url/title/target).
	 * @param array       $args {
	 *     @type string $variant primary|light|glass|outline.
	 *     @type string $size    sm|md|lg.
	 *     @type string $icon    Icon name for the nest.
	 *     @type string $class   Extra classes.
	 * }
	 * @return void
	 */
	function upskill_button( $link, $args = array() ) {
		if ( empty( $link['url'] ) ) {
			return;
		}

		$args = wp_parse_args(
			$args,
			array(
				'variant' => 'primary',
				'size'    => 'md',
				'icon'    => 'arrow-up-right',
				'class'   => '',
			)
		);

		$classes = sprintf(
			'btn btn--%1$s btn--%2$s%3$s %4$s',
			sanitize_html_class( $args['variant'] ),
			sanitize_html_class( $args['size'] ),
			// The play triangle keeps still on hover (see .btn--play).
			'play' === $args['icon'] ? ' btn--play' : '',
			$args['class']
		);

		printf(
			'<a class="%1$s"%2$s><span class="btn__label">%3$s</span><span class="btn__nest" aria-hidden="true">%4$s</span></a>',
			esc_attr( trim( $classes ) ),
			upskill_link_attributes( $link ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			esc_html( ! empty( $link['title'] ) ? $link['title'] : __( 'Find out more', 'upskill-me' ) ),
			upskill_get_icon( $args['icon'], 'btn__icon' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file.
		);
	}
}

if ( ! function_exists( 'upskill_image' ) ) {
	/**
	 * Render an ACF image field (array, ID or URL) through the attachment API,
	 * so srcset, sizes, alt text and lazy loading come from WordPress.
	 *
	 * @param array|int|string|false $image   ACF image field value.
	 * @param string                 $size    Registered image size.
	 * @param array                  $attr    Attributes; `class` included.
	 * @return void
	 */
	function upskill_image( $image, $size = 'large', $attr = array() ) {
		if ( is_array( $image ) && ! empty( $image['ID'] ) ) {
			$image = (int) $image['ID'];
		}

		if ( is_numeric( $image ) ) {
			echo wp_get_attachment_image( (int) $image, $size, false, $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core escapes.
			return;
		}

		if ( ! $image || ! is_string( $image ) ) {
			return;
		}

		printf(
			'<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy" decoding="async">',
			esc_attr( isset( $attr['class'] ) ? $attr['class'] : '' ),
			esc_url( $image ),
			esc_attr( isset( $attr['alt'] ) ? $attr['alt'] : '' )
		);
	}
}

if ( ! function_exists( 'upskill_theme_image' ) ) {
	/**
	 * URL of a file in the theme's images directory.
	 *
	 * @param string $file File name relative to /images.
	 * @return string
	 */
	function upskill_theme_image( $file ) {
		return get_template_directory_uri() . '/images/' . ltrim( $file, '/' );
	}
}

if ( ! function_exists( 'upskill_option' ) ) {
	/**
	 * Read a field from the Site Settings options pages.
	 *
	 * Global content is never read from the current post, so this is the only
	 * way blocks and template parts reach it.
	 *
	 * @param string $name    Field name.
	 * @param mixed  $default Value returned when the field is empty.
	 * @return mixed
	 */
	function upskill_option( $name, $default = '' ) {
		if ( ! function_exists( 'get_field' ) ) {
			return $default;
		}

		$value = get_field( $name, 'option' );

		return ( '' === $value || null === $value || false === $value || array() === $value ) ? $default : $value;
	}
}

if ( ! function_exists( 'upskill_eyebrow' ) ) {
	/**
	 * The pill label that opens most sections: an icon and an uppercase line.
	 *
	 * @param string $text  Label text.
	 * @param string $icon  Icon name from images/icons.
	 * @param string $class Extra classes (e.g. `eyebrow--dark`).
	 * @return void
	 */
	function upskill_eyebrow( $text, $icon = '', $class = '' ) {
		if ( ! $text ) {
			return;
		}
		?>
		<p class="eyebrow <?php echo esc_attr( $class ); ?>">
			<?php upskill_icon( $icon, 'eyebrow__icon' ); ?>
			<span><?php echo esc_html( $text ); ?></span>
		</p>
		<?php
	}
}

if ( ! function_exists( 'upskill_section_header' ) ) {
	/**
	 * The section header drawn above almost every module: eyebrow, heading with
	 * its highlighted phrase, intro copy and an optional button.
	 *
	 * Reads the fields cloned from "Component: Section Header" on the current
	 * block, so every block names them identically.
	 *
	 * @param array $args {
	 *     @type string $layout split (heading left, copy right) | center | stack.
	 *     @type bool   $dark   True on dark sections.
	 *     @type string $tag    Heading tag.
	 *     @type string $class  Extra wrapper classes.
	 *     @type bool   $button False when the block draws the button elsewhere.
	 *     @type bool   $lead   True for the 20px intro some split headers use.
	 *     @type string $button_variant light for the white pill (Resources rows).
	 *     @type bool   $reveal True to fade the eyebrow and intro in on scroll, as the designer template does.
	 * }
	 * @return void
	 */
	function upskill_section_header( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'layout' => 'split',
				'dark'   => false,
				'tag'    => 'h2',
				'class'  => '',
				'button' => true,
				'reveal' => false,
			)
		);

		$eyebrow = get_field( 'eyebrow' );
		$heading = get_field( 'heading' );
		$intro   = get_field( 'intro' );
		$button  = $args['button'] ? get_field( 'button' ) : false;

		if ( ! $eyebrow && ! $heading && ! $intro ) {
			return;
		}

		$classes = 'section-header section-header--' . sanitize_html_class( $args['layout'] );

		if ( $args['dark'] ) {
			$classes .= ' is-dark';
		}

		// The 20px intro some pages pair with a split header (Case Studies).
		if ( ! empty( $args['lead'] ) ) {
			$classes .= ' section-header--lead';
		}
		?>
		<header class="<?php echo esc_attr( trim( $classes . ' ' . $args['class'] ) ); ?>">
			<div class="section-header__title">
				<?php
				upskill_eyebrow( $eyebrow, (string) get_field( 'eyebrow_icon' ), trim( ( $args['dark'] ? 'eyebrow--dark' : '' ) . ( $args['reveal'] ? ' reveal' : '' ) ) );
				upskill_heading( $heading, $args['tag'], 'section-header__heading text-h3' );
				?>
			</div>
			<?php if ( $intro || ! empty( $button['url'] ) ) : ?>
				<div class="section-header__aside<?php echo $args['reveal'] ? ' reveal' : ''; ?>"<?php echo $args['reveal'] ? ' style="--i:1"' : ''; ?>>
					<?php upskill_paragraphs( $intro, 'section-header__intro' ); ?>
					<?php upskill_button( $button, empty( $args['button_variant'] ) ? array() : array( 'variant' => $args['button_variant'], 'class' => 'min-h-[50px]' ) ); ?>
				</div>
			<?php endif; ?>
		</header>
		<?php
	}
}

if ( ! function_exists( 'upskill_is_block_composed' ) ) {
	/**
	 * Is this post laid out with the theme's ACF blocks?
	 *
	 * `has_blocks()` cannot answer it: a page typed into the block editor is full
	 * of core paragraphs and would claim to be composed.
	 *
	 * @param int|WP_Post|null $post Post to test. Defaults to the current one.
	 * @return bool
	 */
	function upskill_is_block_composed( $post = null ) {
		$post = get_post( $post );

		if ( ! $post || ! has_blocks( $post->post_content ) ) {
			return false;
		}

		return false !== strpos( $post->post_content, '<!-- wp:acf-block/' );
	}
}

if ( ! function_exists( 'upskill_term_url' ) ) {
	/**
	 * Where a training term should take a visitor: its landing page when one is
	 * chosen, otherwise its archive (see upskill_training_term_link()).
	 *
	 * @param WP_Term $term Term.
	 * @return string
	 */
	function upskill_term_url( $term ) {
		$link = get_term_link( $term );

		return is_wp_error( $link ) ? '' : $link;
	}
}

if ( ! function_exists( 'upskill_term_session_count' ) ) {
	/**
	 * The "76 sessions" figure shown beside an industry, category or cohort.
	 *
	 * For now it is the term's own post count (training courses tagged with it).
	 * Whether a "session" is a LearnDash course or a lesson is confirmed once
	 * LearnDash is installed; the filter is the single place to change that.
	 *
	 * @param WP_Term $term Term.
	 * @return int
	 */
	function upskill_term_session_count( $term ) {
		return (int) apply_filters( 'upskill_term_session_count', (int) $term->count, $term );
	}
}

if ( ! function_exists( 'upskill_session_chip' ) ) {
	/**
	 * The dashed count chip followed by "sessions". Nothing is drawn for zero:
	 * "0 sessions" next to a live category reads as broken.
	 *
	 * @param int    $count Number of sessions.
	 * @param string $class Extra classes (`session-chip--lg` for the list rows).
	 * @return void
	 */
	function upskill_session_chip( $count, $class = '' ) {
		if ( $count < 1 ) {
			return;
		}

		printf(
			'<span class="session-chip %1$s"><span class="session-chip__count">%2$s</span> %3$s</span>',
			esc_attr( $class ),
			esc_html( number_format_i18n( $count ) ),
			esc_html( _n( 'session', 'sessions', $count, 'upskill-me' ) )
		);
	}
}

if ( ! function_exists( 'upskill_selected_terms' ) ) {
	/**
	 * Normalise an ACF taxonomy field value to WP_Term objects, in the order the
	 * editor arranged them.
	 *
	 * @param mixed  $value    Field value (IDs or terms).
	 * @param string $taxonomy Taxonomy the IDs belong to.
	 * @return WP_Term[]
	 */
	function upskill_selected_terms( $value, $taxonomy ) {
		$terms = array();

		foreach ( (array) $value as $item ) {
			$term = $item instanceof WP_Term ? $item : get_term( (int) $item, $taxonomy );

			if ( $term instanceof WP_Term ) {
				$terms[] = $term;
			}
		}

		// One query for every term's meta, rather than one per get_field() call.
		if ( $terms ) {
			update_termmeta_cache( wp_list_pluck( $terms, 'term_id' ) );
		}

		return $terms;
	}
}

if ( ! function_exists( 'upskill_paragraphs' ) ) {
	/**
	 * Echo plain text as paragraphs, one per blank-line separated block.
	 *
	 * Textareas store raw text; editors separate paragraphs with an empty line.
	 *
	 * @param string $text  Plain text.
	 * @param string $class Class for each paragraph.
	 * @return void
	 */
	function upskill_paragraphs( $text, $class = '' ) {
		foreach ( preg_split( '/\R\s*\R/', trim( (string) $text ) ) as $paragraph ) {
			if ( '' === trim( $paragraph ) ) {
				continue;
			}

			printf( '<p class="%1$s">%2$s</p>', esc_attr( $class ), esc_html( trim( $paragraph ) ) );
		}
	}
}

if ( ! function_exists( 'upskill_text_link' ) ) {
	/**
	 * The small "Read Article ↗" text link used on cards.
	 *
	 * @param array  $link  ACF-style link array (url/title/target).
	 * @param string $class Classes for the link (colour, size).
	 * @return void
	 */
	function upskill_text_link( $link, $class = '' ) {
		if ( empty( $link['url'] ) || empty( $link['title'] ) ) {
			return;
		}

		printf(
			'<a class="group/link inline-flex items-center gap-[7px] text-sm font-semibold %1$s"%2$s>%3$s%4$s</a>',
			esc_attr( $class ),
			upskill_link_attributes( $link ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			esc_html( $link['title'] ),
			upskill_get_icon( 'arrow-up-right', 'size-3 transition-transform duration-300 group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file.
		);
	}
}

if ( ! function_exists( 'upskill_post_type_label' ) ) {
	/**
	 * The resource type a card is labelled with: Blog, Case study or Guide.
	 *
	 * @param int|WP_Post $post Post.
	 * @return string
	 */
	function upskill_post_type_label( $post ) {
		$labels = array(
			'post'       => __( 'Blog', 'upskill-me' ),
			'case_study' => __( 'Case study', 'upskill-me' ),
			'guide'      => __( 'Guide', 'upskill-me' ),
		);
		$type   = get_post_type( $post );

		return isset( $labels[ $type ] ) ? $labels[ $type ] : '';
	}
}

if ( ! function_exists( 'upskill_read_label' ) ) {
	/**
	 * The call to action under a resource card ("Read Article", "Read Case Study").
	 *
	 * @param int|WP_Post $post Post.
	 * @return string
	 */
	function upskill_read_label( $post ) {
		$labels = array(
			'post'       => __( 'Read Article', 'upskill-me' ),
			'case_study' => __( 'Read Case Study', 'upskill-me' ),
			'guide'      => __( 'Read Guide', 'upskill-me' ),
		);
		$type   = get_post_type( $post );

		return isset( $labels[ $type ] ) ? $labels[ $type ] : __( 'Read more', 'upskill-me' );
	}
}

if ( ! function_exists( 'upskill_reading_time' ) ) {
	/**
	 * Minutes to read a post, at 200 words a minute.
	 *
	 * @param int|WP_Post $post Post.
	 * @return int
	 */
	function upskill_reading_time( $post ) {
		$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post ) ) );

		return max( 1, (int) ceil( $words / 200 ) );
	}
}

if ( ! function_exists( 'upskill_primary_category' ) ) {
	/**
	 * The first category of a post (the topic chip on blog cards), skipping
	 * the default "Uncategorised".
	 *
	 * @param int|WP_Post $post Post.
	 * @return WP_Term|null
	 */
	function upskill_primary_category( $post ) {
		foreach ( (array) get_the_category( is_object( $post ) ? $post->ID : $post ) as $term ) {
			if ( (int) get_option( 'default_category' ) !== (int) $term->term_id ) {
				return $term;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'upskill_pagination' ) ) {
	/**
	 * Previous / numbered / next pagination for a listing query.
	 *
	 * @param WP_Query $query The listing query.
	 * @return void
	 */
	function upskill_pagination( $query ) {
		$total = (int) $query->max_num_pages;

		if ( $total < 2 ) {
			return;
		}

		$current = max( 1, (int) get_query_var( 'paged' ) );
		$links   = paginate_links(
			array(
				'current'   => $current,
				'total'     => $total,
				'type'      => 'array',
				'mid_size'  => 1,
				'end_size'  => 3,
				'prev_next' => false,
			)
		);
		$arrow   = upskill_get_icon( 'arrow-right', 'size-[15px]' );
		?>
		<nav class="pagination mt-8 flex items-center gap-3 border-t border-[#e9eaeb] pt-5" aria-label="<?php esc_attr_e( 'Pagination', 'upskill-me' ); ?>">
			<div class="flex flex-1">
				<?php if ( $current > 1 ) : ?>
					<a class="pagination__step inline-flex items-center gap-2 text-sm font-medium text-brand-900" href="<?php echo esc_url( get_pagenum_link( $current - 1 ) ); ?>" rel="prev">
						<span class="rotate-180"><?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file. ?></span>
						<?php esc_html_e( 'Previous', 'upskill-me' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<ul class="flex items-center gap-0.5">
				<?php foreach ( (array) $links as $link ) : ?>
					<li class="pagination__number"><?php echo wp_kses_post( $link ); ?></li>
				<?php endforeach; ?>
			</ul>
			<div class="flex flex-1 justify-end">
				<?php if ( $current < $total ) : ?>
					<a class="pagination__step inline-flex items-center gap-2 text-sm font-medium text-brand-900" href="<?php echo esc_url( get_pagenum_link( $current + 1 ) ); ?>" rel="next">
						<?php esc_html_e( 'Next', 'upskill-me' ); ?>
						<?php echo $arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned SVG file. ?>
					</a>
				<?php endif; ?>
			</div>
		</nav>
		<?php
	}
}
