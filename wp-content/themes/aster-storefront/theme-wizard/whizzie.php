<?php
/**
 * Wizard
 *
 * @package Whizzie
 * @author Aster Themes
 * @since 1.0.0
 */

class Whizzie {

	protected $version = '1.1.0';
	protected $theme_name = '';
	protected $theme_title = '';
	protected $page_slug = '';
	protected $page_title = '';
	protected $config_steps = array();
	public $plugin_path;
	public $parent_slug;
	
	/**
	 * Relative plugin url for this plugin folder
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_url = '';

	/**
	 * TGMPA instance storage
	 *
	 * @var object
	 */
	protected $tgmpa_instance;

	/**
	 * TGMPA Menu slug
	 *
	 * @var string
	 */
	protected $tgmpa_menu_slug = 'tgmpa-install-plugins';

	/**
	 * TGMPA Menu url
	 *
	 * @var string
	 */
	protected $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

	/*** Constructor ***
	* @param $config	Our config parameters
	*/
	public function __construct( $config ) {
		$this->set_vars( $config );
		$this->init();
	}

	/**
	* Set some settings
	* @since 1.0.0
	* @param $config	Our config parameters
	*/

	public function set_vars( $config ) {
		// require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/class-tgm-plugin-activation.php';
		require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/tgm.php';

		if( isset( $config['page_slug'] ) ) {
			$this->page_slug = esc_attr( $config['page_slug'] );
		}
		if( isset( $config['page_title'] ) ) {
			$this->page_title = esc_attr( $config['page_title'] );
		}
		if( isset( $config['steps'] ) ) {
			$this->config_steps = $config['steps'];
		}

		$this->plugin_path = trailingslashit( dirname( __FILE__ ) );
		$relative_url = str_replace( get_template_directory(), '', $this->plugin_path );
		$this->plugin_url = trailingslashit( get_template_directory_uri() . $relative_url );
		$current_theme = wp_get_theme();
		$this->theme_title = $current_theme->get( 'Name' );
		$this->theme_name = strtolower( preg_replace( '#[^a-zA-Z]#', '', $current_theme->get( 'Name' ) ) );
		$this->page_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_page_slug', $this->theme_name . '-wizard' );
		$this->parent_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_parent_slug', '' );
	}

	/**
	 * Hooks and filters
	 * @since 1.0.0
	 */
	public function init() {
		if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
			add_action( 'init', array( $this, 'get_tgmpa_instance' ), 30 );
			add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_init', array( $this, 'get_plugins' ), 30 );
		add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
		add_action( 'wp_ajax_setup_plugins', array( $this, 'setup_plugins' ) );
		add_action( 'wp_ajax_setup_widgets', array( $this, 'setup_widgets' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'theme-wizard-style', get_template_directory_uri() . '/theme-wizard/assets/css/theme-wizard-style.css');
		wp_register_script( 'theme-wizard-script', get_template_directory_uri() . '/theme-wizard/assets/js/theme-wizard-script.js', array( 'jquery' ), );

		wp_localize_script(
			'theme-wizard-script',
			'aster_storefront_whizzie_params',
			array(
				'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
				'verify_text'	=> esc_html( 'verifying', 'aster-storefront' )
			)
		);
		wp_enqueue_script( 'theme-wizard-script' );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function tgmpa_load( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Get configured TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2*/
	public function get_tgmpa_instance() {
		$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	}

	/**
	 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function set_tgmpa_url() {
		$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
		$this->tgmpa_menu_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );
		$tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';
		$this->tgmpa_url = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );
	}

	/***        Make a modal screen for the wizard        ***/
	
	public function menu_page() {
		add_theme_page( esc_html( $this->page_title ), esc_html( $this->page_title ), 'manage_options', $this->page_slug, array( $this, 'aster_storefront_setup_wizard' ) );
	}

	/***        Make an interface for the wizard        ***/

	public function wizard_page() {
		tgmpa_load_bulk_installer();
		// install plugins with TGM.
		if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
			die( 'Failed to find TGM' );
		}
		$url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'whizzie-setup' );

		// copied from TGM
		$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
		$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.
		if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
			return true; // Stop the normal page form from displaying, credential request form will be shown.
		}
		// Now we have some credentials, setup WP_Filesystem.
		if ( ! WP_Filesystem( $creds ) ) {
			// Our credentials were no good, ask the user for them again.
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}
		/* If we arrive here, we have the filesystem */ ?>
		<div class="main-wrap">
			<?php
			echo '<div class="card whizzie-wrap">';
				// The wizard is a list with only one item visible at a time
				$steps = $this->get_steps();
				echo '<ul class="whizzie-menu">';
				foreach( $steps as $step ) {
					$class = 'step step-' . esc_attr( $step['id'] );
					echo '<li data-step="' . esc_attr( $step['id'] ) . '" class="' . esc_attr( $class ) . '">';
						printf( '<h2>%s</h2>', esc_html( $step['title'] ) );
						// $content is split into summary and detail
						$content = call_user_func( array( $this, $step['view'] ) );
						if( isset( $content['summary'] ) ) {
							printf(
								'<div class="summary">%s</div>',
								wp_kses_post( $content['summary'] )
							);
						}
						if( isset( $content['detail'] ) ) {
							// Add a link to see more detail
							printf( '<p><a href="#" class="more-info">%s</a></p>', __( 'More Info', 'aster-storefront' ) );
							printf(
								'<div class="detail">%s</div>',
								$content['detail'] // Need to escape this
							);
						}
						// The next button
						if( isset( $step['button_text'] ) && $step['button_text'] ) {
                            ?>
                            <?php if ( ! get_option('is-demo-imported') ) : ?>
                            <?php
                            printf(
                                '<div class="button-wrap"><a href="#" class="button button-primary do-it" data-callback="%s" data-step="%s">%s</a></div>',
                                esc_attr( $step['callback'] ),
                                esc_attr( $step['id'] ),
                                esc_html( $step['button_text'] )
                            );
                            ?>
                            <?php else : ?>
                                <a target="_blank" href="<?php echo esc_url( home_url() ); ?>" class="button button-primary" style="margin: auto; font-size: 20px; font-weight: 600">
                                    <?php esc_html_e( 'Visit Site', 'aster-storefront' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php
                        }
					echo '</li>';
				}
				echo '</ul>';
				?>
				<div class="step-loading"><span class="spinner"></span></div>
			</div><!-- .whizzie-wrap -->

		</div><!-- .wrap -->
	<?php }



	public function activation_page() {
		?>
		<div class="main-wrap">
			<h3><?php esc_html_e('Theme Setup Wizard','aster-storefront'); ?></h3>
		</div>
		<?php
	}

	public function aster_storefront_setup_wizard(){

			$display_string = '';

			$body = [
				'home_url'					 => home_url(),
				'theme_text_domain'	 => wp_get_theme()->get( 'TextDomain' )
			];

			$body = wp_json_encode( $body );
			$options = [
				'body'        => $body,
				'sslverify' 	=> false,
				'headers'     => [
					'Content-Type' => 'application/json',
				]
			];

			//custom function about theme customizer
			$return = add_query_arg( array()) ;
			$theme = wp_get_theme( 'aster-storefront' );

			?>
				<div class="wrapper-info get-stared-page-wrap">
					<div class="tab-sec theme-option-tab">
						<div id="demo_offer" class="tabcontent">
							<?php $this->wizard_page(); ?>
						</div>
					</div>
				</div>
			<?php
	}
	

	/**
	* Set options for the steps
	* Incorporate any options set by the theme dev
	* Return the array for the steps
	* @return Array
	*/
	public function get_steps() {
		$dev_steps = $this->config_steps;
		$steps = array(
			'intro' => array(
				'id'			=> 'intro',
				'title'			=> __( 'Welcome to ', 'aster-storefront' ) . $this->theme_title,
				'icon'			=> 'dashboard',
				'view'			=> 'get_step_intro', // Callback for content
				'callback'		=> 'do_next_step', // Callback for JS
				'button_text'	=> __( 'Start Now', 'aster-storefront' ),
				'can_skip'		=> false // Show a skip button?
			),
			'plugins' => array(
				'id'			=> 'plugins',
				'title'			=> __( 'Plugins', 'aster-storefront' ),
				'icon'			=> 'admin-plugins',
				'view'			=> 'get_step_plugins',
				'callback'		=> 'install_plugins',
				'button_text'	=> __( 'Install Plugins', 'aster-storefront' ),
				'can_skip'		=> true
			),
			'widgets' => array(
				'id'			=> 'widgets',
				'title'			=> __( 'Demo Importer', 'aster-storefront' ),
				'icon'			=> 'welcome-widgets-menus',
				'view'			=> 'get_step_widgets',
				'callback'		=> 'install_widgets',
				'button_text'	=> __( 'Import Demo', 'aster-storefront' ),
				'can_skip'		=> true
			),
			'done' => array(
				'id'			=> 'done',
				'title'			=> __( 'All Done', 'aster-storefront' ),
				'icon'			=> 'yes',
				'view'			=> 'get_step_done',
				'callback'		=> ''
			)
		);

		// Iterate through each step and replace with dev config values
		if( $dev_steps ) {
			// Configurable elements - these are the only ones the dev can update from config.php
			$can_config = array( 'title', 'icon', 'button_text', 'can_skip' );
			foreach( $dev_steps as $dev_step ) {
				// We can only proceed if an ID exists and matches one of our IDs
				if( isset( $dev_step['id'] ) ) {
					$id = $dev_step['id'];
					if( isset( $steps[$id] ) ) {
						foreach( $can_config as $element ) {
							if( isset( $dev_step[$element] ) ) {
								$steps[$id][$element] = $dev_step[$element];
							}
						}
					}
				}
			}
		}
		return $steps;
	}

	/*** Display the content for the intro step ***/
	public function get_step_intro() { ?>
		<div class="summary">
			<p style="text-align: center;"><?php esc_html_e( 'Thank you for choosing our theme! We are excited to help you get started with your new website.', 'aster-storefront' ); ?></p>
			<p style="text-align: center;"><?php esc_html_e( 'This section will guide you through setting up and customizing the theme. You can follow the steps to import demo content or adjust settings at any time to make the website look and work the way you want.', 'aster-storefront' ); ?></p>
		</div>
	<?php }
	
	

	/**
	 * Get the content for the plugins step
	 * @return $content Array
	 */
	public function get_step_plugins() {
		$plugins = $this->get_plugins();
		$content = array(); ?>
			<div class="summary">
				<p>
					<?php esc_html_e('Additional plugins always make your website exceptional. Install these plugins by clicking the install button. You may also deactivate them from the dashboard.','aster-storefront') ?>
				</p>
			</div>
		<?php // The detail element is initially hidden from the user
		$content['detail'] = '<ul class="whizzie-do-plugins">';
		// Add each plugin into a list
		foreach( $plugins['all'] as $slug=>$plugin ) {
			$content['detail'] .= '<li data-slug="' . esc_attr( $slug ) . '">' . esc_html( $plugin['name'] ) . '<span>';
			$keys = array();
			if ( isset( $plugins['install'][ $slug ] ) ) {
			    $keys[] = 'Installation';
			}
			if ( isset( $plugins['update'][ $slug ] ) ) {
			    $keys[] = 'Update';
			}
			if ( isset( $plugins['activate'][ $slug ] ) ) {
			    $keys[] = 'Activation';
			}
			$content['detail'] .= implode( ' and ', $keys ) . ' required';
			$content['detail'] .= '</span></li>';
		}
		$content['detail'] .= '</ul>';

		return $content;
	}

	/*** Display the content for the widgets step ***/
	public function get_step_widgets() { ?>
		<div class="summary">
			<p><?php esc_html_e('To get started, use the button below to import demo content and add widgets to your site. After installation, you can manage settings and customize your site using the Customizer. Enjoy your new theme!', 'aster-storefront'); ?></p>
		</div>
	<?php }

	/***        Print the content for the final step        ***/

	public function get_step_done() { ?>
		<div id="aster-demo-setup-guid">
			<div class="aster-setup-menu">
				<h3><?php esc_html_e('Setup Navigation Menu','aster-storefront'); ?></h3>
				<p><?php esc_html_e('Follow the following Steps to Setup Menu','aster-storefront'); ?></p>
				<h4><?php esc_html_e('A) Create Pages','aster-storefront'); ?></h4>
				<ol>
					<li><?php esc_html_e('Go to Dashboard >> Pages >> Add New','aster-storefront'); ?></li>
					<li><?php esc_html_e('Enter Page Details And Save Changes','aster-storefront'); ?></li>
				</ol>
				<h4><?php esc_html_e('B) Add Pages To Menu','aster-storefront'); ?></h4>
				<ol>
					<li><?php esc_html_e('Go to Dashboard >> Appearance >> Menu','aster-storefront'); ?></li>
					<li><?php esc_html_e('Click On The Create Menu Option','aster-storefront'); ?></li>
					<li><?php esc_html_e('Select The Pages And Click On The Add to Menu Button','aster-storefront'); ?></li>
					<li><?php esc_html_e('Select Primary Menu From The Menu Setting','aster-storefront'); ?></li>
					<li><?php esc_html_e('Click On The Save Menu Button','aster-storefront'); ?></li>
				</ol>
			</div>
			<div class="aster-setup-widget">
				<h3><?php esc_html_e('Setup Footer Widgets','aster-storefront'); ?></h3>
				<p><?php esc_html_e('Follow the following Steps to Setup Footer Widgets','aster-storefront'); ?></p>
				<ol>
					<li><?php esc_html_e('Go to Dashboard >> Appearance >> Widgets','aster-storefront'); ?></li>
					<li><?php esc_html_e('Drag And Add The Widgets In The Footer Columns','aster-storefront'); ?></li>
				</ol>
			</div>
			<div style="display:flex; justify-content: center; margin-top: 20px; gap:20px">
				<div class="aster-setup-finish">
					<a target="_blank" href="<?php echo esc_url(home_url()); ?>" class="button button-primary">Visit Site</a>
				</div>
				<div class="aster-setup-finish">
					<a target="_blank" href="<?php echo esc_url( admin_url('customize.php') ); ?>" class="button button-primary">Customize Your Demo</a>
				</div>
				<div class="aster-setup-finish">
					<a target="_blank" href="<?php echo esc_url( admin_url('themes.php?page=aster-storefront-getting-started') ); ?>" class="button button-primary">Dashboard</a>
				</div>
			</div>
		</div>
	<?php }

	/***       Get the plugins registered with TGMPA       ***/
	public function get_plugins() {
		$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		$plugins = array(
			'all' 		=> array(),
			'install'	=> array(),
			'update'	=> array(),
			'activate'	=> array()
		);
		foreach( $instance->plugins as $slug=>$plugin ) {
			if( $instance->is_plugin_active( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
				// Plugin is installed and up to date
				continue;
			} else {
				$plugins['all'][$slug] = $plugin;
				if( ! $instance->is_plugin_installed( $slug ) ) {
					$plugins['install'][$slug] = $plugin;
				} else {
					if( false !== $instance->does_plugin_have_update( $slug ) ) {
						$plugins['update'][$slug] = $plugin;
					}
					if( $instance->can_plugin_activate( $slug ) ) {
						$plugins['activate'][$slug] = $plugin;
					}
				}
			}
		}
		return $plugins;
	}


	public function setup_plugins() {
		$json = array();
		// send back some json we use to hit up TGM
		$plugins = $this->get_plugins();

		// what are we doing with this plugin?
		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating Plugin','aster-storefront' ),
				);
				break;
			}
		}
		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating Plugin','aster-storefront' ),
				);
				break;
			}
		}
		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing Plugin','aster-storefront' ),
				);
				break;
			}
		}
		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success','aster-storefront' ) ) );
		}
		exit;
	}

	/***------------------------------------------------- Imports the Demo Content* @since 1.1.0 ----------------------------------------------****/


	//                      ------------- MENUS -----------------                    //

	public function aster_storefront_customizer_primary_menu(){
		// ------- Create Primary Menu --------
		$aster_storefront_themename = 'Aster Storefront'; // Ensure the theme name is set
		$aster_storefront_menuname = $aster_storefront_themename . ' Primary Menu';
		$aster_storefront_bpmenulocation = 'primary';
		$aster_storefront_menu_exists = wp_get_nav_menu_object($aster_storefront_menuname);
	
		if( !$aster_storefront_menu_exists ) {
			$aster_storefront_menu_id = wp_create_nav_menu($aster_storefront_menuname);
			
			// Home
			wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
				'menu-item-title' => __('Home', 'aster-storefront'),
				'menu-item-classes' => 'home',
				'menu-item-url' => home_url('/'),
				'menu-item-status' => 'publish'
			));

			// Tops
			$page_tops = get_page_by_path('tops');
			if($page_tops){
				wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
					'menu-item-title' => __('Tops', 'aster-storefront'),
					'menu-item-classes' => 'tops',
					'menu-item-url' => get_permalink($page_tops),
					'menu-item-status' => 'publish'
				));
			}

			// Bottoms
			$page_bottom = get_page_by_path('bottom');
			if($page_bottom){
				wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
					'menu-item-title' => __('Bottoms', 'aster-storefront'),
					'menu-item-classes' => 'bottom',
					'menu-item-url' => get_permalink($page_bottom),
					'menu-item-status' => 'publish'
				));
			}

			// Shop
			$shop_page_id = wc_get_page_id('shop'); // Get the WooCommerce shop page ID
			if ($shop_page_id) {
				wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
					'menu-item-title' => __('Shop', 'aster-storefront'), // Update the title to 'Shop'
					'menu-item-classes' => 'shop', // Add a class if needed
					'menu-item-url' => get_permalink($shop_page_id), // Get the permalink for the shop page
					'menu-item-status' => 'publish' // Publish the menu item
				));
			}
	
			// Outwear
			$page_outwear = get_page_by_path('outwear');
			if($page_outwear){
				wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
					'menu-item-title' => __('Outwear', 'aster-storefront'),
					'menu-item-classes' => 'outwear',
					'menu-item-url' => get_permalink($page_outwear),
					'menu-item-status' => 'publish'
				));
			}

			// Accessories
			$page_accessories = get_page_by_path('accessories');
			if($page_accessories){
				wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
					'menu-item-title' => __('Accessories', 'aster-storefront'),
					'menu-item-classes' => 'accessories',
					'menu-item-url' => get_permalink($page_accessories),
					'menu-item-status' => 'publish'
				));
			}

			// Blogs
			$page_blog = get_page_by_path('blog');
			if($page_blog){
				wp_update_nav_menu_item($aster_storefront_menu_id, 0, array(
					'menu-item-title' => __('Blogs', 'aster-storefront'),
					'menu-item-classes' => 'blog',
					'menu-item-url' => get_permalink($page_blog),
					'menu-item-status' => 'publish'
				));
			}
	
			// Assign menu to location if not set
			if( !has_nav_menu($aster_storefront_bpmenulocation) ){
				$locations = get_theme_mod('nav_menu_locations');
				$locations[$aster_storefront_bpmenulocation] = $aster_storefront_menu_id;
				set_theme_mod('nav_menu_locations', $locations);
			}
		}
	}

	public function setup_widgets() {

		// Create a front page and assign the template
		$aster_storefront_home_title = 'Home';
		$aster_storefront_home_check = get_page_by_path('home');
		if (!$aster_storefront_home_check) {
			$aster_storefront_home = array(
				'post_type'    => 'page',
				'post_title'   => $aster_storefront_home_title,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_name'    => 'home' // Unique slug for the home page
			);
			$aster_storefront_home_id = wp_insert_post($aster_storefront_home);

			// Set the static front page
			if (!is_wp_error($aster_storefront_home_id)) {
				update_option('page_on_front', $aster_storefront_home_id);
				update_option('show_on_front', 'page');
			}
		}

		// Create a posts page and assign the template
		$aster_storefront_blog_title = 'Blogs';
		$aster_storefront_blog_check = get_page_by_path('blog');
		if (!$aster_storefront_blog_check) {
			$aster_storefront_blog = array(
				'post_type'    => 'page',
				'post_title'   => $aster_storefront_blog_title,
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_name'    => 'blog' // Unique slug for the blog page
			);
			$aster_storefront_blog_id = wp_insert_post($aster_storefront_blog);

			// Set the posts page
			if (!is_wp_error($aster_storefront_blog_id)) {
				update_option('page_for_posts', $aster_storefront_blog_id);
			}
		}

		// Create a Tops page and assign the template
		$aster_storefront_tops_title = 'Tops';
		$aster_storefront_tops_check = get_page_by_path('tops');
		if (!$aster_storefront_tops_check) {
			$aster_storefront_tops = array(
				'post_type'    => 'page',
				'post_title'   => $aster_storefront_tops_title,
				'post_content' => '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_name'    => 'tops' // Unique slug for the Tops page
			);
			wp_insert_post($aster_storefront_tops);
		}

		// Create a Accessories page and assign the template
		$aster_storefront_accessories_title = 'Accessories';
		$aster_storefront_accessories_check = get_page_by_path('accessories');
		if (!$aster_storefront_accessories_check) {
			$aster_storefront_accessories = array(
				'post_type'    => 'page',
				'post_title'   => $aster_storefront_accessories_title,
				'post_content' => '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_name'    => 'accessories' // Unique slug for the Accessories page
			);
			wp_insert_post($aster_storefront_accessories);
		}

		// Create a Outwear page and assign the template
		$aster_storefront_outwear_title = 'Outwear';
		$aster_storefront_outwear_check = get_page_by_path('outwear');
		if (!$aster_storefront_outwear_check) {
			$aster_storefront_outwear = array(
				'post_type'    => 'page',
				'post_title'   => $aster_storefront_outwear_title,
				'post_content' => '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_name'    => 'outwear' // Unique slug for the Outwear page
			);
			wp_insert_post($aster_storefront_outwear);
		}

		// Create a Bottoms page and assign the template
		$aster_storefront_bottom_title = 'Bottoms';
		$aster_storefront_bottom_check = get_page_by_path('bottom');
		if (!$aster_storefront_bottom_check) {
			$aster_storefront_bottom = array(
				'post_type'    => 'page',
				'post_title'   => $aster_storefront_bottom_title,
				'post_content' => '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960 with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_name'    => 'bottom' // Unique slug for the Bottoms page
			);
			wp_insert_post($aster_storefront_bottom);
		}


		/*----------------------------------------- Header Button --------------------------------------------------*/

			set_theme_mod( 'aster_storefront_discount_topbar_text','CLICK HERE TO GET 10% OFF YOUR FIRST ORDER');
		

		/*----------------------------------------- Product --------------------------------------------------*/

			set_theme_mod( 'aster_storefront_trending_product_heading', 'Trending Now');

			$products = array(
				'Store Front Store Product 01',
				'Store Front Store Product 02',
				'Store Front Store Product 03',
				'Store Front Store Product 04',
			);
			
			$review_text = array(
				'Nice product',
				'Good Quality Product',
			);
			
			$k = 1;
			foreach ($products as $index => $product_title) {
				// Create post content
				$content = 'Lorem Ipsum has been the industry standard dummy text ever since the 1500s.';
			
				// Create post object
				$my_post = array(
					'post_title'    => wp_strip_all_tags($product_title),
					'post_content'  => $content,
					'post_status'   => 'publish',
					'post_type'     => 'product',
				);
			
				// Insert the post into the database
				$post_id = wp_insert_post($my_post);
			
				// Set the product category
				wp_set_post_terms($post_id, 'uncategorized', 'product_cat');
			
				// Set the price of the product
				update_post_meta($post_id, '_regular_price', '599'); // Set regular price	
				update_post_meta($post_id, '_sale_price', '499'); // Set sale price
				update_post_meta($post_id, '_price', '499'); // Set current price (sale price is applied)
			
				// Add ratings and reviews
				for ($c = 0; $c <= 1; $c++) { // Only loop through available review texts
					$comment_id = wp_insert_comment(array(
						'comment_post_ID'      => $post_id,
						'comment_author'       => get_the_author_meta('display_name'),
						'comment_author_email' => get_the_author_meta('user_email'),
						'comment_content'      => $review_text[$c],
						'comment_parent'       => 0,
						'user_id'              => get_current_user_id(),
						'comment_date'         => date('Y-m-d H:i:s'),
						'comment_approved'     => 1,
					));
			
					// Ensure the 'rating' meta is correctly added
					if ($comment_id) {
						update_comment_meta($comment_id, 'rating', 5); // Set rating value here
					}
				}
			
				// Handle product image upload
				$image_url = get_template_directory_uri() . '/resource/img/' . str_replace(' ', '-', strtolower($product_title)) . '.png';
				$image_name = $product_title . '.png';
				$upload_dir = wp_upload_dir();
			
				// Set upload folder
				$image_data = file_get_contents(esc_url($image_url));
			
				if (false === $image_data) {
					wp_die('Error retrieving the image.');
				}
			
				// Generate unique name
				$unique_file_name = wp_unique_filename($upload_dir['path'], $image_name);
				$filename = basename($unique_file_name);
			
				// Check folder permission and define file location
				if (wp_mkdir_p($upload_dir['path'])) {
					$file = $upload_dir['path'] . '/' . $filename;
				} else {
					$file = $upload_dir['basedir'] . '/' . $filename;
				}
			
				// Initialize WP_Filesystem API
				if (!function_exists('WP_Filesystem')) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
			
				WP_Filesystem();
				global $wp_filesystem;
			
				// Create the image file on the server using WP_Filesystem
				if (!$wp_filesystem->put_contents($file, $image_data, FS_CHMOD_FILE)) {
					wp_die('Error saving the file.');
				}
			
				// Check image file type
				$wp_filetype = wp_check_filetype($filename, null);
			
				// Set attachment data
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name($filename),
					'post_type'      => 'product',
					'post_status'    => 'inherit',
				);
			
				// Create the attachment
				$attach_id = wp_insert_attachment($attachment, $file, $post_id);
			
				// Include image.php
				require_once(ABSPATH . 'wp-admin/includes/image.php');
			
				// Define attachment metadata
				$attach_data = wp_generate_attachment_metadata($attach_id, $file);
			
				// Assign metadata to attachment
				wp_update_attachment_metadata($attach_id, $attach_data);
			
				// Assign featured image to post
				set_post_thumbnail($post_id, $attach_id);
			
				$k++;
			}	

		
		// ------------------------------------------ Blogs for Sections --------------------------------------
				
			wp_delete_post(1);

			// Loop to create posts
			for ($i = 1; $i <= 3; $i++) {

				$title = array(
					'New Arrival Dresses',
					'Exclusive Deals on Trending Fashion',
					'Shop the Best Styles for Every Season'
				);				

				$content = 'Lorem Ipsum has been the industry standard dummy text ever since the 1500s when an unknown printer took a galley of type and scrambled it to make a type specimen book.';

				// Create post object
				$my_post = array(
					'post_title'    => wp_strip_all_tags( $title[$i-1] ),
					'post_content'  => $content,
					'post_status'   => 'publish',
					'post_type'     => 'post',
				);

				// Insert the post into the database
				$post_id = wp_insert_post($my_post);

				// Set dynamic image name
				$aster_storefront_image_name = 'slider' . $i . '.png';
				$aster_storefront_image_url  = get_template_directory_uri() . '/resource/img/' . $aster_storefront_image_name;

				$aster_storefront_upload_dir = wp_upload_dir();
				$aster_storefront_image_data = file_get_contents($aster_storefront_image_url);
				$aster_storefront_unique_file_name = wp_unique_filename($aster_storefront_upload_dir['path'], $aster_storefront_image_name);
				$filename = basename($aster_storefront_unique_file_name);

				if (wp_mkdir_p($aster_storefront_upload_dir['path'])) {
					$file = $aster_storefront_upload_dir['path'] . '/' . $filename;
				} else {
					$file = $aster_storefront_upload_dir['basedir'] . '/' . $filename;
				}

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}
				
				WP_Filesystem();
				global $wp_filesystem;
				
				if ( ! $wp_filesystem->put_contents( $file, $aster_storefront_image_data, FS_CHMOD_FILE ) ) {
					wp_die( 'Error saving file!' );
				}

				$wp_filetype = wp_check_filetype($filename, null);
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => sanitize_file_name($filename),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);

				$aster_storefront_attach_id = wp_insert_attachment($attachment, $file, $post_id);

				require_once(ABSPATH . 'wp-admin/includes/image.php');

				$aster_storefront_attach_data = wp_generate_attachment_metadata($aster_storefront_attach_id, $file);
				wp_update_attachment_metadata($aster_storefront_attach_id, $aster_storefront_attach_data);
				set_post_thumbnail($post_id, $aster_storefront_attach_id);
			}


		// ---------------------------------------- Slider --------------------------------------------------- //

			for($i=1; $i<=3; $i++) {
				set_theme_mod('aster_storefront_banner_button_label_'.$i,'Shop Now');
				set_theme_mod('aster_storefront_banner_button_link_'.$i,'#');
			}
			set_theme_mod('aster_storefront_enable_service_section', true);

		// ---------------------------------------- Footer section --------------------------------------------------- //	
		
			set_theme_mod('footer_background_color_setting','#000000');
			

		// ---------------------------------------- Related post_tag --------------------------------------------------- //	
		
			set_theme_mod('aster_storefront_post_related_post_label','Related Posts');
			set_theme_mod('aster_storefront_related_posts_count','3');

		$this->aster_storefront_customizer_primary_menu();
		update_option('is-demo-imported', true);
	}
}