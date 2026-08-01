<?php

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package aster_storefront
 */

function aster_storefront_body_classes( $aster_storefront_classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$aster_storefront_classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$aster_storefront_classes[] = 'no-sidebar';
	}

	$aster_storefront_classes[] = aster_storefront_sidebar_layout();

	return $aster_storefront_classes;
}
add_filter( 'body_class', 'aster_storefront_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function aster_storefront_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'aster_storefront_pingback_header' );


/**
 * Get all posts for customizer Post content type.
 */
function aster_storefront_get_post_choices() {
	$aster_storefront_choices = array( '' => esc_html__( '--Select--', 'aster-storefront' ) );
	$aster_storefront_args    = array( 'numberposts' => -1 );
	$aster_storefront_posts   = get_posts( $aster_storefront_args );

	foreach ( $aster_storefront_posts as $aster_storefront_post ) {
		$aster_storefront_id             = $aster_storefront_post->ID;
		$aster_storefront_title          = $aster_storefront_post->post_title;
		$aster_storefront_choices[ $aster_storefront_id ] = $aster_storefront_title;
	}

	return $aster_storefront_choices;
}

/**
 * Get all pages for customizer Page content type.
 */
function aster_storefront_get_page_choices() {
	$aster_storefront_choices = array( '' => esc_html__( '--Select--', 'aster-storefront' ) );
	$aster_storefront_pages   = get_pages();

	foreach ( $aster_storefront_pages as $aster_storefront_page ) {
		$aster_storefront_choices[ $aster_storefront_page->ID ] = $aster_storefront_page->post_title;
	}

	return $aster_storefront_choices;
}

/**
 * Get all categories for customizer Category content type.
 */
function aster_storefront_get_post_cat_choices() {
	$aster_storefront_choices = array( '' => esc_html__( '--Select--', 'aster-storefront' ) );
	$aster_storefront_cats    = get_categories();

	foreach ( $aster_storefront_cats as $aster_storefront_cat ) {
		$aster_storefront_choices[ $aster_storefront_cat->term_id ] = $aster_storefront_cat->name;
	}

	return $aster_storefront_choices;
}

/**
 * Get all donation forms for customizer form content type.
 */
function aster_storefront_get_post_donation_form_choices() {
	$aster_storefront_choices = array( '' => esc_html__( '--Select--', 'aster-storefront' ) );
	$aster_storefront_posts   = get_posts(
		array(
			'post_type'   => 'give_forms',
			'numberposts' => -1,
		)
	);
	foreach ( $aster_storefront_posts as $aster_storefront_post ) {
		$aster_storefront_choices[ $aster_storefront_post->ID ] = $aster_storefront_post->post_title;
	}
	return $aster_storefront_choices;
}

if ( ! function_exists( 'aster_storefront_excerpt_length' ) ) :
	/**
	 * Excerpt length.
	 */
	function aster_storefront_excerpt_length( $aster_storefront_length ) {
		if ( is_admin() ) {
			return $aster_storefront_length;
		}

		return get_theme_mod( 'aster_storefront_excerpt_length', 20 );
	}
endif;
add_filter( 'excerpt_length', 'aster_storefront_excerpt_length', 999 );

if ( ! function_exists( 'aster_storefront_excerpt_more' ) ) :
	/**
	 * Excerpt more.
	 */
	function aster_storefront_excerpt_more( $aster_storefront_more ) {
		if ( is_admin() ) {
			return $aster_storefront_more;
		}

		return '&hellip;';
	}
endif;
add_filter( 'excerpt_more', 'aster_storefront_excerpt_more' );

if ( ! function_exists( 'aster_storefront_sidebar_layout' ) ) {
	/**
	 * Get sidebar layout.
	 */
	function aster_storefront_sidebar_layout() {
		$aster_storefront_sidebar_position      = get_theme_mod( 'aster_storefront_sidebar_position', 'right-sidebar' );
		$aster_storefront_sidebar_position_post = get_theme_mod( 'aster_storefront_post_sidebar_position', 'right-sidebar' );
		$aster_storefront_sidebar_position_page = get_theme_mod( 'aster_storefront_page_sidebar_position', 'right-sidebar' );

		if ( is_single() ) {
			$aster_storefront_sidebar_position = $aster_storefront_sidebar_position_post;
		} elseif ( is_page() ) {
			$aster_storefront_sidebar_position = $aster_storefront_sidebar_position_page;
		}

		return $aster_storefront_sidebar_position;
	}
}

if ( ! function_exists( 'aster_storefront_is_sidebar_enabled' ) ) {
	/**
	 * Check if sidebar is enabled.
	 */
	function aster_storefront_is_sidebar_enabled() {
		$aster_storefront_sidebar_position      = get_theme_mod( 'aster_storefront_sidebar_position', 'right-sidebar' );
		$aster_storefront_sidebar_position_post = get_theme_mod( 'aster_storefront_post_sidebar_position', 'right-sidebar' );
		$aster_storefront_sidebar_position_page = get_theme_mod( 'aster_storefront_page_sidebar_position', 'right-sidebar' );

		$aster_storefront_sidebar_enabled = true;
		if ( is_home() || is_archive() || is_search() ) {
			if ( 'no-sidebar' === $aster_storefront_sidebar_position ) {
				$aster_storefront_sidebar_enabled = false;
			}
		} elseif ( is_single() ) {
			if ( 'no-sidebar' === $aster_storefront_sidebar_position || 'no-sidebar' === $aster_storefront_sidebar_position_post ) {
				$aster_storefront_sidebar_enabled = false;
			}
		} elseif ( is_page() ) {
			if ( 'no-sidebar' === $aster_storefront_sidebar_position || 'no-sidebar' === $aster_storefront_sidebar_position_page ) {
				$aster_storefront_sidebar_enabled = false;
			}
		}
		return $aster_storefront_sidebar_enabled;
	}
}

if ( ! function_exists( 'aster_storefront_get_homepage_sections ' ) ) {
	/**
	 * Returns homepage sections.
	 */
	function aster_storefront_get_homepage_sections() {
		$aster_storefront_sections = array(
			'banner'  => esc_html__( 'Banner Section', 'aster-storefront' ),
			'trending-product' => esc_html__( 'Trending Product Section', 'aster-storefront' ),
		);
		return $aster_storefront_sections;
	}
}

/**
 * Renders customizer section link
 */
function aster_storefront_section_link( $aster_storefront_section_id ) {
	$aster_storefront_section_name      = str_replace( 'aster_storefront_', ' ', $aster_storefront_section_id );
	$aster_storefront_section_name      = str_replace( '_', ' ', $aster_storefront_section_name );
	$aster_storefront_starting_notation = '#';
	?>
	<span class="section-link">
		<span class="section-link-title"><?php echo esc_html( $aster_storefront_section_name ); ?></span>
	</span>
	<style type="text/css">
		<?php echo $aster_storefront_starting_notation . $aster_storefront_section_id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>:hover .section-link {
			visibility: visible;
		}
	</style>
	<?php
}

/**
 * Adds customizer section link css
 */
function aster_storefront_section_link_css() {
	if ( is_customize_preview() ) {
		?>
		<style type="text/css">
			.section-link {
				visibility: hidden;
				background-color: black;
				position: relative;
				top: 80px;
				z-index: 99;
				left: 40px;
				color: #fff;
				text-align: center;
				font-size: 20px;
				border-radius: 10px;
				padding: 20px 10px;
				text-transform: capitalize;
			}

			.section-link-title {
				padding: 0 10px;
			}

			.banner-section {
				position: relative;
			}

			.banner-section .section-link {
				position: absolute;
				top: 100px;
			}
		</style>
		<?php
	}
}
add_action( 'wp_head', 'aster_storefront_section_link_css' );

/**
 * Breadcrumb.
 */
function aster_storefront_breadcrumb( $aster_storefront_args = array() ) {
	if ( ! get_theme_mod( 'aster_storefront_enable_breadcrumb', true ) ) {
		return;
	}

	$aster_storefront_args = array(
		'show_on_front' => false,
		'show_title'    => true,
		'show_browse'   => false,
	);
	breadcrumb_trail( $aster_storefront_args );
}
add_action( 'aster_storefront_breadcrumb', 'aster_storefront_breadcrumb', 10 );

/**
 * Add separator for breadcrumb trail.
 */
function aster_storefront_breadcrumb_trail_print_styles() {
	$aster_storefront_breadcrumb_separator = get_theme_mod( 'aster_storefront_breadcrumb_separator', '/' );

	$aster_storefront_style = '
		.trail-items li::after {
			content: "' . $aster_storefront_breadcrumb_separator . '";
		}'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	$aster_storefront_style = apply_filters( 'aster_storefront_breadcrumb_trail_inline_style', trim( str_replace( array( "\r", "\n", "\t", '  ' ), '', $aster_storefront_style ) ) );

	if ( $aster_storefront_style ) {
		echo "\n" . '<style type="text/css" id="breadcrumb-trail-css">' . $aster_storefront_style . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'aster_storefront_breadcrumb_trail_print_styles' );

/**
 * Pagination for archive.
 */
function aster_storefront_render_posts_pagination() {
	$aster_storefront_is_pagination_enabled = get_theme_mod( 'aster_storefront_enable_pagination', true );
	if ( $aster_storefront_is_pagination_enabled ) {
		$aster_storefront_pagination_type = get_theme_mod( 'aster_storefront_pagination_type', 'default' );
		if ( 'default' === $aster_storefront_pagination_type ) :
			the_posts_navigation();
		else :
			the_posts_pagination();
		endif;
	}
}
add_action( 'aster_storefront_posts_pagination', 'aster_storefront_render_posts_pagination', 10 );

/**
 * Pagination for single post.
 */
function aster_storefront_render_post_navigation() {
	the_post_navigation(
		array(
			'prev_text' => '<span>&#10229;</span> <span class="nav-title">%title</span>',
			'next_text' => '<span class="nav-title">%title</span> <span>&#10230;</span>',
		)
	);
}
add_action( 'aster_storefront_post_navigation', 'aster_storefront_render_post_navigation' );

function aster_storefront_output_footer_copyright_content() {
    $aster_storefront_theme_data = wp_get_theme();
    $aster_storefront_copyright_text = get_theme_mod('aster_storefront_footer_copyright_text');

    if (!empty($aster_storefront_copyright_text)) {
        $aster_storefront_text = $aster_storefront_copyright_text;
    } else {
        $aster_storefront_default_text = '<a href="'. esc_url(__('https://asterthemes.com/products/free-storefront-wordpress-theme','aster-storefront')) . '" target="_blank"> ' . esc_html($aster_storefront_theme_data->get('Name')) . '</a>' . '&nbsp;' . esc_html__('by', 'aster-storefront') . '&nbsp;<a target="_blank" href="' . esc_url($aster_storefront_theme_data->get('AuthorURI')) . '">' . esc_html(ucwords($aster_storefront_theme_data->get('Author'))) . '</a>';
		/* translators: %s: WordPress.org URL */
        $aster_storefront_default_text .= sprintf(esc_html__(' | Powered by %s', 'aster-storefront'), '<a href="' . esc_url(__('https://wordpress.org/', 'aster-storefront')) . '" target="_blank">WordPress</a>. ');

        $aster_storefront_text = $aster_storefront_default_text;
    }
    ?>
    <span><?php echo wp_kses_post($aster_storefront_text); ?></span>
    <?php
}
add_action('aster_storefront_footer_copyright', 'aster_storefront_output_footer_copyright_content');

	
/**
 * GET START FUNCTION
*/

function aster_storefront_getpage_css($hook) {
	wp_enqueue_script( 'aster-storefront-admin-script', get_template_directory_uri() . '/resource/js/aster-storefront-admin-notice-script.js', array( 'jquery' ) );
    wp_localize_script( 'aster-storefront-admin-script', 'aster_storefront_ajax_object',
        array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
    );
    wp_enqueue_style( 'aster-storefront-notice-style', get_template_directory_uri() . '/resource/css/notice.css' );
}

add_action( 'admin_enqueue_scripts', 'aster_storefront_getpage_css' );

add_action('wp_ajax_aster_storefront_dismissable_notice', 'aster_storefront_dismissable_notice');
	function aster_storefront_switch_theme() {
	    delete_user_meta(get_current_user_id(), 'aster_storefront_dismissable_notice');
	}
	add_action('after_switch_theme', 'aster_storefront_switch_theme');
	function aster_storefront_dismissable_notice() {
	    update_user_meta(get_current_user_id(), 'aster_storefront_dismissable_notice', true);
	    die();
	}




function aster_storefront_deprecated_hook_admin_notice() {
    global $aster_storefront_pagenow;
    
    // Check if the current page is the one where you don't want the notice to appear
    if ( $aster_storefront_pagenow === 'themes.php' && isset( $_GET['page'] ) && $_GET['page'] === 'aster-storefront-getting-started' ) {
        return;
    }

    $dismissed = get_user_meta( get_current_user_id(), 'aster_storefront_dismissable_notice', true );
    if ( !$dismissed) { ?>
        <div class="getstrat updated notice notice-success is-dismissible notice-get-started-class">
            <div class="at-admin-content" >
                <h2><?php esc_html_e('Welcome to Aster Storefront', 'aster-storefront'); ?></h2>
                <p><?php _e('Explore the features of our Pro Theme and take your store journey to the next level.', 'aster-storefront'); ?></p>
                <p ><?php _e('Get Started With Theme By Clicking On Getting Started.', 'aster-storefront'); ?><p>
                <div style="display: flex; justify-content: center; align-items:center; flex-wrap: wrap; gap: 5px">
                    <a class="admin-notice-btn button button-primary button-hero" href="<?php echo esc_url( admin_url( 'themes.php?page=aster-storefront-getting-started' )); ?>"><?php esc_html_e( 'Get started', 'aster-storefront' ) ?></a>
                    <a  class="admin-notice-btn button button-primary button-hero" target="_blank" href="https://demo.asterthemes.com/aster-storefront"><?php esc_html_e('View Demo', 'aster-storefront') ?></a>
                    <a  class="admin-notice-btn button button-primary button-hero" target="_blank" href="https://asterthemes.com/products/storefront-wordpress-theme"><?php esc_html_e('Buy Now', 'aster-storefront') ?></a>
                    <a  class="admin-notice-btn button button-primary button-hero" target="_blank" href="<?php echo esc_url( ASTER_STOREFRONT_BUNDLE_PAGE ); ?>"><?php esc_html_e('Get Bundle', 'aster-storefront') ?></a>
                </div>
            </div>
            <div class="at-admin-image">
                <img style="width: 100%;max-width: 320px;line-height: 40px;display: inline-block;vertical-align: top;border: 2px solid #ddd;border-radius: 4px;" src="<?php echo esc_url(get_stylesheet_directory_uri()) .'/screenshot.png'; ?>" />
            </div>
        </div>
    <?php }
}

add_action( 'admin_notices', 'aster_storefront_deprecated_hook_admin_notice' );


//Admin Notice For Getstart
function aster_storefront_ajax_notice_handler() {
    if ( isset( $_POST['type'] ) ) {
        $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
        update_option( 'dismissed-' . $type, TRUE );
    }
}

if ( ! function_exists( 'aster_storefront_footer_widget' ) ) :
	function aster_storefront_footer_widget() {
		$aster_storefront_footer_widget_column = get_theme_mod('aster_storefront_footer_widget_column','4');

		$aster_storefront_column_class = '';
		if ($aster_storefront_footer_widget_column == '1') {
			$aster_storefront_column_class = 'one-column';
		} elseif ($aster_storefront_footer_widget_column == '2') {
			$aster_storefront_column_class = 'two-columns';
		} elseif ($aster_storefront_footer_widget_column == '3') {
			$aster_storefront_column_class = 'three-columns';
		} else {
			$aster_storefront_column_class = 'four-columns';
		}
	
		if($aster_storefront_footer_widget_column !== ''): 
		?>
		<div class="dt_footer-widgets <?php echo esc_attr($aster_storefront_column_class); ?> wow bounceInUp delay-1000" data-wow-duration="2s">
			<div class="footer-widgets-column">
				<?php
				$footer_widgets_active = false;

				// Loop to check if any footer widget is active
				for ($i = 1; $i <= $aster_storefront_footer_widget_column; $i++) {
					if (is_active_sidebar('aster-storefront-footer-widget-' . $i)) {
						$footer_widgets_active = true;
						break;
					}
				}

				if ($footer_widgets_active) {
					// Display active footer widgets
					for ($i = 1; $i <= $aster_storefront_footer_widget_column; $i++) {
						if (is_active_sidebar('aster-storefront-footer-widget-' . $i)) : ?>
							<div class="footer-one-column">
								<?php dynamic_sidebar('aster-storefront-footer-widget-' . $i); ?>
							</div>
						<?php endif;
					}
				} else {
				?>
				<div class="footer-one-column default-widgets">
					<aside id="search-2" class="widget widget_search default_footer_search">
						<div class="widget-header">
							<h4 class="widget-title"><?php esc_html_e('Search Here', 'aster-storefront'); ?></h4>
						</div>
						<?php get_search_form(); ?>
					</aside>
				</div>
				<div class="footer-one-column default-widgets">
					<aside id="recent-posts-2" class="widget widget_recent_entries">
						<h2 class="widget-title"><?php esc_html_e('Recent Posts', 'aster-storefront'); ?></h2>
						<ul>
							<?php
							$recent_posts = wp_get_recent_posts(array(
								'numberposts' => 5,
								'post_status' => 'publish',
							));
							foreach ($recent_posts as $post) {
								echo '<li><a href="' . esc_url(get_permalink($post['ID'])) . '">' . esc_html($post['post_title']) . '</a></li>';
							}
							wp_reset_query();
							?>
						</ul>
					</aside>
				</div>
				<div class="footer-one-column default-widgets">
					<aside id="recent-comments-2" class="widget widget_recent_comments">
						<h2 class="widget-title"><?php esc_html_e('Recent Comments', 'aster-storefront'); ?></h2>
						<ul>
							<?php
							$recent_comments = get_comments(array(
								'number' => 5,
								'status' => 'approve',
							));
							foreach ($recent_comments as $comment) {
								echo '<li><a href="' . esc_url(get_comment_link($comment)) . '">' .
									/* translators: %s: details. */
									sprintf(esc_html__('Comment on %s', 'aster-storefront'), get_the_title($comment->comment_post_ID)) .
									'</a></li>';
							}
							?>
						</ul>
					</aside>
				</div>
				<div class="footer-one-column default-widgets">
					<aside id="calendar-2" class="widget widget_calendar">
						<h2 class="widget-title"><?php esc_html_e('Calendar', 'aster-storefront'); ?></h2>
						<?php get_calendar(); ?>
					</aside>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php
		endif;
	}
	endif;
add_action( 'aster_storefront_footer_widget', 'aster_storefront_footer_widget' );

function aster_storefront_footer_text_transform_css() {
    $aster_storefront_footer_text_transform = get_theme_mod('aster_storefront_footer_text_transform', 'none');
    ?>
    <style type="text/css">
        .site-footer h4,footer#colophon h2.wp-block-heading,footer#colophon .widgettitle,footer#colophon .widget-title {
            text-transform: <?php echo esc_html($aster_storefront_footer_text_transform); ?>;
        }
    </style>
    <?php
}
add_action('wp_head', 'aster_storefront_footer_text_transform_css');

/* Footer Social Icons */ 
function aster_storefront_footer_social_links() {

    if ( get_theme_mod('aster_storefront_enable_footer_icon_section', true) ) {

            ?>
            <div class="socialicons">
                <?php if ( get_theme_mod('aster_storefront_footer_facebook_link', 'https://www.facebook.com/') != '' ) { ?>
                    <a target="_blank" href="<?php echo esc_url(get_theme_mod('aster_storefront_footer_facebook_link', 'https://www.facebook.com/')); ?>">
                        <i class="<?php echo esc_attr(get_theme_mod('aster_storefront_facebook_icon', 'fab fa-facebook-f')); ?>"></i>
                        <span class="screen-reader-text"><?php esc_html_e('Facebook', 'aster-storefront'); ?></span>
                    </a>
                <?php } ?>
                <?php if ( get_theme_mod('aster_storefront_footer_twitter_link', 'https://x.com/') != '' ) { ?>
                    <a target="_blank" href="<?php echo esc_url(get_theme_mod('aster_storefront_footer_twitter_link', 'https://x.com/')); ?>">
                        <i class="<?php echo esc_attr(get_theme_mod('aster_storefront_twitter_icon', 'fab fa-twitter')); ?>"></i>
                        <span class="screen-reader-text"><?php esc_html_e('Twitter', 'aster-storefront'); ?></span>
                    </a>
                <?php } ?>
				<?php if ( get_theme_mod('aster_storefront_footer_instagram_link', 'https://www.instagram.com/') != '' ) { ?>
                    <a target="_blank" href="<?php echo esc_url(get_theme_mod('aster_storefront_footer_instagram_link', 'https://www.instagram.com/')); ?>">
                        <i class="<?php echo esc_attr(get_theme_mod('aster_storefront_instagram_icon', 'fab fa-instagram')); ?>"></i>
                        <span class="screen-reader-text"><?php esc_html_e('Instagram', 'aster-storefront'); ?></span>
                    </a>
                <?php } ?>
                <?php if ( get_theme_mod('aster_storefront_footer_linkedin_link', 'https://in.linkedin.com/') != '' ) { ?>
                    <a target="_blank" href="<?php echo esc_url(get_theme_mod('aster_storefront_footer_linkedin_link', 'https://in.linkedin.com/')); ?>">
                        <i class="<?php echo esc_attr(get_theme_mod('aster_storefront_linkedin_icon', 'fab fa-linkedin')); ?>"></i>
                        <span class="screen-reader-text"><?php esc_html_e('Linkedin', 'aster-storefront'); ?></span>
                    </a>
                <?php } ?>
				<?php if ( get_theme_mod('aster_storefront_footer_youtube_link', 'https://www.youtube.com/') != '' ) { ?>
                    <a target="_blank" href="<?php echo esc_url(get_theme_mod('aster_storefront_footer_youtube_link', 'https://www.youtube.com/')); ?>">
                        <i class="<?php echo esc_attr(get_theme_mod('aster_storefront_youtube_icon', 'fab fa-youtube')); ?>"></i>
                        <span class="screen-reader-text"><?php esc_html_e('Youtube', 'aster-storefront'); ?></span>
                    </a>
                <?php } ?>
            </div>
            <?php
    }
}
add_action('wp_footer', 'aster_storefront_footer_social_links');