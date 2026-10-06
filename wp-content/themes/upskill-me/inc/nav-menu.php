<?php
/**
 * Navigation rendering.
 *
 * The primary menu is not a plain list: a top level item with children opens a
 * mega panel (desktop) or an accordion (mobile drawer). The panel is a sibling
 * of its trigger rather than a nested <ul>, so the markup is built from the menu
 * item tree directly instead of through a Walker.
 *
 * Panel content comes from Appearance > Menus:
 * - the parent item's Description is the panel intro, its "Panel image" (ACF)
 *   the picture beneath it, and its URL the "see all" arrow;
 * - each child's title and Description form one row;
 * - the promo card is global (Site Settings > Header > Menu promo).
 *
 * The desktop panel is not drawn in the Figma file; its structure follows the
 * designer's HTML reference, using the Figma tokens.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_menu_tree' ) ) {
	/**
	 * Fetch a menu location as a top-level tree.
	 *
	 * @param string $location Registered nav menu location.
	 * @return WP_Post[] Top level items, each with a `children` property.
	 */
	function upskill_menu_tree( $location ) {
		$locations = get_nav_menu_locations();

		if ( empty( $locations[ $location ] ) ) {
			return array();
		}

		$items = wp_get_nav_menu_items( $locations[ $location ] );

		if ( ! $items ) {
			return array();
		}

		// Adds the current-menu-item classes core's walker would have added.
		_wp_menu_item_classes_by_context( $items );

		$by_parent = array();

		foreach ( $items as $item ) {
			$by_parent[ (int) $item->menu_item_parent ][] = $item;
		}

		$tree = isset( $by_parent[0] ) ? $by_parent[0] : array();

		foreach ( $tree as $item ) {
			$item->children = isset( $by_parent[ $item->ID ] ) ? $by_parent[ $item->ID ] : array();
		}

		return $tree;
	}
}

if ( ! function_exists( 'upskill_menu_name' ) ) {
	/**
	 * The editable name of the menu assigned to a location.
	 *
	 * Footer columns use it as their heading, so renaming the menu renames the
	 * column without touching code.
	 *
	 * @param string $location Registered nav menu location.
	 * @return string
	 */
	function upskill_menu_name( $location ) {
		$locations = get_nav_menu_locations();
		$menu      = empty( $locations[ $location ] ) ? null : wp_get_nav_menu_object( $locations[ $location ] );

		return $menu ? $menu->name : '';
	}
}

if ( ! function_exists( 'upskill_menu_item_is_current' ) ) {
	/**
	 * Is this item, or one of its children, the page being viewed?
	 *
	 * @param WP_Post $item Menu item.
	 * @return bool
	 */
	function upskill_menu_item_is_current( $item ) {
		$current = array( 'current-menu-item', 'current-menu-ancestor', 'current-menu-parent', 'current_page_item', 'current_page_ancestor' );

		return (bool) array_intersect( (array) $item->classes, $current );
	}
}

if ( ! function_exists( 'upskill_menu_item_link_atts' ) ) {
	/**
	 * Every attribute a menu item's anchor should carry.
	 *
	 * The menus are rendered by hand, so this is the one place that reads the
	 * fields Appearance > Menus writes onto an item: CSS Classes, Link Target,
	 * Title Attribute and Link Relationship.
	 *
	 * @param WP_Post $item    Menu item.
	 * @param string  $classes Base classes the template wants on the anchor.
	 * @return string Escaped attribute string with a leading space.
	 */
	function upskill_menu_item_link_atts( $item, $classes = '' ) {
		$item_classes = array_filter(
			(array) $item->classes,
			static function ( $class ) {
				// Drop core's state classes; aria-current carries the state.
				return $class && 0 !== strpos( $class, 'menu-item' ) && 0 !== strpos( $class, 'current' ) && 0 !== strpos( $class, 'page_item' ) && 0 !== strpos( $class, 'page-item' );
			}
		);

		$atts  = ' href="' . esc_url( $item->url ) . '"';
		$class = trim( $classes . ' ' . implode( ' ', $item_classes ) );

		if ( '' !== $class ) {
			$atts .= ' class="' . esc_attr( $class ) . '"';
		}

		if ( ! empty( $item->attr_title ) ) {
			$atts .= ' title="' . esc_attr( $item->attr_title ) . '"';
		}

		$rel = (string) $item->xfn;

		if ( ! empty( $item->target ) ) {
			$atts .= ' target="' . esc_attr( $item->target ) . '"';

			if ( '_blank' === $item->target ) {
				$rel = trim( $rel . ' noopener' );
			}
		}

		if ( '' !== $rel ) {
			$atts .= ' rel="' . esc_attr( $rel ) . '"';
		}

		// A link to an anchor on the current page is not the current page.
		if ( in_array( 'current-menu-item', (array) $item->classes, true ) && false === strpos( (string) $item->url, '#' ) ) {
			$atts .= ' aria-current="page"';
		}

		return $atts;
	}
}

if ( ! function_exists( 'upskill_menu_promo' ) ) {
	/**
	 * The promo card shared by the mega panels and the mobile drawer.
	 *
	 * @param string $context mega|drawer.
	 * @return void
	 */
	function upskill_menu_promo( $context = 'mega' ) {
		$promo = upskill_option( 'menu_promo', array() );

		if ( empty( $promo['title'] ) ) {
			return;
		}
		?>
		<aside class="menu-promo menu-promo--<?php echo esc_attr( $context ); ?>">
			<p class="menu-promo__title"><?php echo esc_html( $promo['title'] ); ?></p>
			<?php if ( ! empty( $promo['text'] ) ) : ?>
				<p class="menu-promo__text"><?php echo esc_html( $promo['text'] ); ?></p>
			<?php endif; ?>
			<?php upskill_button( isset( $promo['link'] ) ? $promo['link'] : array(), array( 'class' => 'menu-promo__button' ) ); ?>
		</aside>
		<?php
	}
}

if ( ! function_exists( 'upskill_render_primary_nav' ) ) {
	/**
	 * The desktop primary navigation with its mega panels.
	 *
	 * A parent opens its panel from a real <button> (Figma draws Industries,
	 * Categories and Resources as triggers with a chevron, not as links); its
	 * own URL stays reachable as the arrow beside the panel heading.
	 *
	 * @return void
	 */
	function upskill_render_primary_nav() {
		$tree = upskill_menu_tree( 'primary' );

		if ( ! $tree ) {
			return;
		}
		?>
		<ul class="site-nav" data-mega-nav>
			<?php foreach ( $tree as $item ) : ?>
				<li class="site-nav__item">
					<?php if ( $item->children ) : ?>
						<?php $panel_id = 'mega-' . $item->ID; ?>
						<button class="site-nav__link<?php echo upskill_menu_item_is_current( $item ) ? ' is-current' : ''; ?>" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-mega-trigger>
							<?php echo esc_html( $item->title ); ?>
							<?php upskill_icon( 'chevron-down', 'site-nav__chevron' ); ?>
						</button>
						<?php upskill_render_mega_panel( $item, $panel_id ); ?>
					<?php else : ?>
						<a<?php echo upskill_menu_item_link_atts( $item, 'site-nav__link' . ( upskill_menu_item_is_current( $item ) ? ' is-current' : '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $item->title ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}

if ( ! function_exists( 'upskill_render_mega_panel' ) ) {
	/**
	 * One desktop mega panel: intro, rows, promo.
	 *
	 * @param WP_Post $item     Top level menu item.
	 * @param string  $panel_id DOM id the trigger controls.
	 * @return void
	 */
	function upskill_render_mega_panel( $item, $panel_id ) {
		$image = function_exists( 'get_field' ) ? get_field( 'mega_image', $item->ID ) : false;
		?>
		<div class="mega" id="<?php echo esc_attr( $panel_id ); ?>" hidden data-mega-panel>
			<div class="mega__panel">
				<div class="mega__intro">
					<div class="mega__head">
						<p class="mega__title"><?php echo esc_html( $item->title ); ?></p>
						<?php if ( $item->url && '#' !== $item->url ) : ?>
							<a class="mega__go" href="<?php echo esc_url( $item->url ); ?>">
								<span class="screen-reader-text">
									<?php
									/* translators: %s: menu item title, e.g. Industries. */
									printf( esc_html__( 'All %s', 'upskill-me' ), esc_html( strtolower( $item->title ) ) );
									?>
								</span>
								<?php upskill_icon( 'arrow-up-right' ); ?>
							</a>
						<?php endif; ?>
					</div>
					<?php if ( $item->description ) : ?>
						<p class="mega__text"><?php echo esc_html( $item->description ); ?></p>
					<?php endif; ?>
					<?php if ( $image ) : ?>
						<figure class="mega__figure">
							<?php upskill_image( $image, 'medium_large', array( 'class' => 'mega__image', 'loading' => 'lazy' ) ); ?>
						</figure>
					<?php endif; ?>
				</div>
				<ul class="mega__list">
					<?php foreach ( $item->children as $child ) : ?>
						<li>
							<a<?php echo upskill_menu_item_link_atts( $child, 'mega__row' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
								<span class="mega__row-text">
									<span class="mega__row-title"><?php echo esc_html( $child->title ); ?></span>
									<?php if ( $child->description ) : ?>
										<span class="mega__row-desc"><?php echo esc_html( $child->description ); ?></span>
									<?php endif; ?>
								</span>
								<span class="mega__go" aria-hidden="true"><?php upskill_icon( 'arrow-up-right' ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php upskill_menu_promo( 'mega' ); ?>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'upskill_render_mobile_nav' ) ) {
	/**
	 * The mobile drawer, per Figma 49475:48697 / 49475:48748: large rows, parents
	 * expand in place, the promo card, then the account actions pinned to the
	 * bottom of the sheet.
	 *
	 * @return void
	 */
	function upskill_render_mobile_nav() {
		$tree    = upskill_menu_tree( 'primary' );
		$actions = array_filter(
			array(
				upskill_option( 'learner_signin' ),
				upskill_option( 'organisation_signin' ),
			)
		);
		?>
		<div class="drawer" id="site-drawer" hidden data-drawer>
			<nav class="drawer__body" aria-label="<?php esc_attr_e( 'Mobile', 'upskill-me' ); ?>">
				<ul class="drawer__list">
					<?php foreach ( $tree as $item ) : ?>
						<li class="drawer__item">
							<?php if ( $item->children ) : ?>
								<?php $sub_id = 'drawer-sub-' . $item->ID; ?>
								<button class="drawer__link" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $sub_id ); ?>" data-disclosure>
									<?php echo esc_html( $item->title ); ?>
									<?php upskill_icon( 'chevron-down', 'drawer__chevron' ); ?>
								</button>
								<div class="collapse" id="<?php echo esc_attr( $sub_id ); ?>">
									<ul class="drawer__sub">
										<?php foreach ( $item->children as $child ) : ?>
											<li><a<?php echo upskill_menu_item_link_atts( $child, 'drawer__sublink' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $child->title ); ?></a></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php else : ?>
								<a<?php echo upskill_menu_item_link_atts( $item, 'drawer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $item->title ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php upskill_menu_promo( 'drawer' ); ?>
			</nav>
			<div class="drawer__foot">
				<?php upskill_button( upskill_option( 'header_cta' ), array( 'class' => 'drawer__cta' ) ); ?>
				<?php if ( $actions ) : ?>
					<div class="drawer__actions">
						<?php foreach ( $actions as $action ) : ?>
							<?php upskill_button( $action, array( 'variant' => 'light' ) ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'upskill_render_footer_menu' ) ) {
	/**
	 * One footer link column. The column heading is the menu's own name.
	 *
	 * @param string $location Registered nav menu location.
	 * @param string $class    Extra classes for the column.
	 * @return void
	 */
	function upskill_render_footer_menu( $location, $class = '' ) {
		$tree = upskill_menu_tree( $location );

		if ( ! $tree ) {
			return;
		}

		$heading_id = 'footer-nav-' . $location;
		?>
		<nav class="footer-nav <?php echo esc_attr( $class ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
			<h2 class="footer-nav__title" id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( upskill_menu_name( $location ) ); ?></h2>
			<ul class="footer-nav__list">
				<?php foreach ( $tree as $item ) : ?>
					<li><a<?php echo upskill_menu_item_link_atts( $item, 'footer-nav__link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $item->title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	}
}
