<?php
require_once get_template_directory() . '/theme-includes.php';

if ( ! function_exists( 'ratio_edge_styles' ) ) {
	/**
	 * Function that includes theme's core styles
	 */
	function ratio_edge_styles() {
		wp_register_style( 'ratio-edge-blog', EDGE_ASSETS_ROOT . '/css/blog.min.css' );
		
		//include theme's core styles
		wp_enqueue_style( 'ratio-edge-default-style', EDGE_ROOT . '/style.css' );
		wp_enqueue_style( 'ratio-edge-modules-plugins', EDGE_ASSETS_ROOT . '/css/plugins.min.css' );
		wp_enqueue_style( 'ratio-edge-modules', EDGE_ASSETS_ROOT . '/css/modules.min.css' );
		
		ratio_edge_icon_collections()->enqueueStyles();
		
		if ( ratio_edge_load_blog_assets() ) {
			wp_enqueue_style( 'ratio-edge-blog' );
		}
		
		if ( ratio_edge_load_blog_assets() || is_singular( 'portfolio-item' ) ) {
			wp_enqueue_style( 'wp-mediaelement' );
		}
		
		if ( ratio_edge_is_woocommerce_installed() && ratio_edge_load_woo_assets() ) {
			wp_enqueue_style( 'ratio-edge-woocommerce', EDGE_ASSETS_ROOT . '/css/woocommerce.min.css' );
		}
		
		//define files afer which style dynamic needs to be included. It should be included last so it can override other files
		$style_dynamic_deps_array = array();
		if ( ratio_edge_load_woo_assets() ) {
			$style_dynamic_deps_array[] = 'ratio-edge-woocommerce';
		}
		
		//is responsive option turned on?
		if ( ratio_edge_is_responsive_on() ) {
			wp_enqueue_style( 'ratio-edge-modules-responsive', EDGE_ASSETS_ROOT . '/css/modules-responsive.min.css' );
			wp_enqueue_style( 'ratio-edge-blog-responsive', EDGE_ASSETS_ROOT . '/css/blog-responsive.min.css' );
			
			if ( ratio_edge_is_woocommerce_installed() && ratio_edge_load_woo_assets() ) {
				wp_enqueue_style( 'ratio-edge-woocommerce-responsive', EDGE_ASSETS_ROOT . '/css/woocommerce-responsive.min.css' );
			}
			
			if ( ratio_edge_load_woo_assets() ) {
				$style_dynamic_deps_array[] = 'ratio-edge-woocommerce-responsive';
			}
			
			//include proper styles
			if ( file_exists( EDGE_ROOT_DIR . '/assets/css/style_dynamic_responsive.css' ) && ratio_edge_is_css_folder_writable() && ! is_multisite() ) {
				wp_enqueue_style( 'ratio-edge-style-dynamic-responsive', EDGE_ASSETS_ROOT . '/css/style_dynamic_responsive.css', array(), filemtime( EDGE_ROOT_DIR . '/assets/css/style_dynamic_responsive.css' ) );
			}
		}
		
		if ( file_exists( EDGE_ROOT_DIR . '/assets/css/style_dynamic.css' ) && ratio_edge_is_css_folder_writable() && ! is_multisite() ) {
			wp_enqueue_style( 'ratio-edge-style-dynamic', EDGE_ASSETS_ROOT . '/css/style_dynamic.css', $style_dynamic_deps_array, filemtime( EDGE_ROOT_DIR . '/assets/css/style_dynamic.css' ) ); //it must be included after woocommerce styles so it can override it
		}
		
		//include Visual Composer styles
		if ( class_exists( 'WPBakeryVisualComposerAbstract' ) ) {
			wp_enqueue_style( 'js_composer_front' );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_styles' );
}

if ( ! function_exists( 'ratio_edge_google_fonts_styles' ) ) {
	/**
	 * Function that includes google fonts defined anywhere in the theme
	 */
	function ratio_edge_google_fonts_styles() {
		$is_enabled = boolval( apply_filters( 'ratio_edge_filter_enable_google_fonts', true ) );

		if ( $is_enabled ) {
			$font_simple_field_array = ratio_edge_options()->getOptionsByType( 'fontsimple' );
			if ( ! ( is_array( $font_simple_field_array ) && count( $font_simple_field_array ) > 0 ) ) {
				$font_simple_field_array = array();
			}

			$font_field_array = ratio_edge_options()->getOptionsByType( 'font' );
			if ( ! ( is_array( $font_field_array ) && count( $font_field_array ) > 0 ) ) {
				$font_field_array = array();
			}

			$available_font_options = array_merge( $font_simple_field_array, $font_field_array );
			$font_weight_str        = '100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';

			//define available font options array
			$fonts_array = array();
			foreach ( $available_font_options as $font_option ) {
				//is font set and not set to default and not empty?
				$font_option_value = ratio_edge_options()->getOptionValue( $font_option );
				if ( ratio_edge_is_font_option_valid( $font_option_value ) && ! ratio_edge_is_native_font( $font_option_value ) ) {
					$font_option_string = $font_option_value . ':' . $font_weight_str;
					if ( ! in_array( $font_option_string, $fonts_array ) ) {
						$fonts_array[] = $font_option_string;
					}
				}
			}

			$fonts_array         = array_diff( $fonts_array, array( '-1:' . $font_weight_str ) );
			$google_fonts_string = implode( '|', $fonts_array );

			//default fonts should be separated with %7C because of HTML validation
			$default_font_string = 'Open Sans:' . $font_weight_str . '|Roboto:' . $font_weight_str;
			$protocol            = is_ssl() ? 'https:' : 'http:';

			//is google font option checked anywhere in theme?
			if ( count( $fonts_array ) > 0 ) {

				//include all checked fonts
				$fonts_full_list      = $default_font_string . '|' . str_replace( '+', ' ', $google_fonts_string );
				$fonts_full_list_args = array(
					'family' => urlencode( $fonts_full_list ),
					'subset' => urlencode( 'latin,latin-ext' ),
				);

				$ratio_edge_fonts = add_query_arg( $fonts_full_list_args, $protocol . '//fonts.googleapis.com/css' );
				wp_enqueue_style( 'ratio-edge-google-fonts', esc_url_raw( $ratio_edge_fonts ), array(), '1.0.0' );

			} else {
				//include default google font that theme is using
				$default_fonts_args = array(
					'family' => urlencode( $default_font_string ),
					'subset' => urlencode( 'latin,latin-ext' ),
				);
				$ratio_edge_fonts   = add_query_arg( $default_fonts_args, $protocol . '//fonts.googleapis.com/css' );
				wp_enqueue_style( 'ratio-edge-google-fonts', esc_url_raw( $ratio_edge_fonts ), array(), '1.0.0' );
			}
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_google_fonts_styles' );
}

if ( ! function_exists( 'ratio_edge_scripts' ) ) {
	/**
	 * Function that includes all necessary scripts
	 */
	function ratio_edge_scripts() {
		global $wp_scripts;
		
		//init theme core scripts
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'wp-mediaelement' );
		
		// 3rd party JavaScripts that we used in our theme
		wp_enqueue_script( 'appear', EDGE_ASSETS_ROOT . '/js/plugins/jquery.appear.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'modernizr', EDGE_ASSETS_ROOT . '/js/plugins/modernizr.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'hoverIntent' );
		wp_enqueue_script( 'jquery-plugin', EDGE_ASSETS_ROOT . '/js/plugins/jquery.plugin.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'countdown', EDGE_ASSETS_ROOT . '/js/plugins/jquery.countdown.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'parallax', EDGE_ASSETS_ROOT . '/js/plugins/parallax.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'select-2', EDGE_ASSETS_ROOT . '/js/plugins/select2.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'easypiechart', EDGE_ASSETS_ROOT . '/js/plugins/easypiechart.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'waypoints', EDGE_ASSETS_ROOT . '/js/plugins/jquery.waypoints.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'chart', EDGE_ASSETS_ROOT . '/js/plugins/Chart.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'counter', EDGE_ASSETS_ROOT . '/js/plugins/counter.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'fluidvids', EDGE_ASSETS_ROOT . '/js/plugins/fluidvids.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'prettyphoto', EDGE_ASSETS_ROOT . '/js/plugins/jquery.prettyPhoto.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'nicescroll', EDGE_ASSETS_ROOT . '/js/plugins/jquery.nicescroll.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'scroll-to-plugin', EDGE_ASSETS_ROOT . '/js/plugins/ScrollToPlugin.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'tweenlite', EDGE_ASSETS_ROOT . '/js/plugins/TweenLite.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'mixitup', EDGE_ASSETS_ROOT . '/js/plugins/jquery.mixitup.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'waitforimages', EDGE_ASSETS_ROOT . '/js/plugins/jquery.waitforimages.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'jquery-easing-1.3', EDGE_ASSETS_ROOT . '/js/plugins/jquery.easing.1.3.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'skrollr', EDGE_ASSETS_ROOT . '/js/plugins/skrollr.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'bootstrap-carousel', EDGE_ASSETS_ROOT . '/js/plugins/bootstrapCarousel.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'touch-swipe', EDGE_ASSETS_ROOT . '/js/plugins/jquery.touchSwipe.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'multiscroll', EDGE_ASSETS_ROOT . '/js/plugins/jquery.multiscroll.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'typed', EDGE_ASSETS_ROOT . '/js/plugins/typed.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'slick', EDGE_ASSETS_ROOT . '/js/plugins/slick.min.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'isotope', EDGE_ASSETS_ROOT . '/js/plugins/jquery.isotope.min.js', array( 'jquery' ), false, true );
		
		if ( ratio_edge_is_smoth_scroll_enabled() ) {
			wp_enqueue_script( "smoothpagescroll", EDGE_ASSETS_ROOT . "/js/plugins/smoothPageScroll.js", array(), false, true );
		}
		
		//include google map api script
		$google_maps_api_key = ratio_edge_options()->getOptionValue( 'google_maps_api_key' );
		if ( ! empty( $google_maps_api_key ) ) {
			wp_enqueue_script( 'ratio-edge-google-map-api', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $google_maps_api_key ) . "&callback=qodefEmptyCallback", array(), false, true );
			wp_add_inline_script('ratio-edge-google-map-api', 'window.qodefEmptyCallback = function () {};','before');
		}
		
		wp_enqueue_script( 'ratio-edge-modules', EDGE_ASSETS_ROOT . '/js/modules.min.js', array( 'jquery' ), false, true );
		
		if ( ratio_edge_load_blog_assets() ) {
			wp_enqueue_script( 'ratio-edge-blog', EDGE_ASSETS_ROOT . '/js/blog.min.js', array( 'jquery' ), false, true );
		}
		
		//include comment reply script
		$wp_scripts->add_data( 'comment-reply', 'group', 1 );
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
		
		//include Visual Composer script
		if ( class_exists( 'WPBakeryVisualComposerAbstract' ) ) {
			wp_enqueue_script( 'wpb_composer_front_js' );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_scripts' );
}

if ( ! function_exists( 'ratio_edge_theme_setup' ) ) {
	/**
	 * Function that adds various features to theme. Also defines image sizes that are used in a theme
	 */
	function ratio_edge_theme_setup() {
		//add support for feed links
		add_theme_support( 'automatic-feed-links' );
		
		//add support for post formats
		add_theme_support( 'post-formats', array( 'gallery', 'link', 'quote', 'video', 'audio' ) );
		
		//add theme support for post thumbnails
		add_theme_support( 'post-thumbnails' );
		
		//add theme support for title tag
		add_theme_support( 'title-tag' );
		
		//add theme support for editor style
		add_editor_style( 'framework/admin/assets/css/editor-style.css' );
		
		//defined content width variable
		$GLOBALS['content_width'] = apply_filters( 'ratio_edge_filter_set_content_width', 1100 );
		
		//define thumbnail sizes
		add_image_size( 'ratio_edge_square', 550, 550, true );
		add_image_size( 'ratio_edge_landscape', 800, 600, true );
		add_image_size( 'ratio_edge_portrait', 600, 800, true );
		add_image_size( 'ratio_edge_large_width', 1000, 500, true );
		add_image_size( 'ratio_edge_large_height', 500, 1000, true );
		add_image_size( 'ratio_edge_large_width_height', 1000, 1000, true );
		
		load_theme_textdomain( 'ratio', EDGE_ROOT_DIR . '/languages' );
	}
	
	add_action( 'after_setup_theme', 'ratio_edge_theme_setup' );
}

if ( ! function_exists( 'ratio_edge_enqueue_editor_customizer_styles' ) ) {
	/**
	 * Enqueue supplemental block editor styles
	 */
	function ratio_edge_enqueue_editor_customizer_styles() {
		$protocol = is_ssl() ? 'https:' : 'http:';
		//include default google font that theme is using
		$default_fonts_args          = array(
			'family' => urlencode( 'Open Sans:300,400,600,700' ),
			'subset' => urlencode( 'latin-ext' ),
		);
		$stockholm_global_fonts = add_query_arg( $default_fonts_args, $protocol . '//fonts.googleapis.com/css' );
		wp_enqueue_style( 'ratio-editor-google-fonts', esc_url_raw( $stockholm_global_fonts ) );
		
		wp_enqueue_style( 'ratio-editor-customizer-style', EDGE_ROOT . 'framework/admin/assets/css/editor-customizer-style.css' );
		wp_enqueue_style( 'ratio-editor-blocks-style', EDGE_ROOT . 'framework/admin/assets/css/editor-blocks-style.css' );
	}
	
	add_action( 'enqueue_block_editor_assets', 'ratio_edge_enqueue_editor_customizer_styles' );
}

if ( ! function_exists( 'ratio_edge_rgba_color' ) ) {
	/**
	 * Function that generates rgba part of css color property
	 *
	 * @param $color string hex color
	 * @param $transparency float transparency value between 0 and 1
	 *
	 * @return string generated rgba string
	 */
	function ratio_edge_rgba_color( $color, $transparency ) {
		if ( $color !== '' && $transparency !== '' ) {
			$rgba_color = '';
			
			$rgb_color_array = ratio_edge_hex2rgb( $color );
			$rgba_color      .= 'rgba(' . implode( ', ', $rgb_color_array ) . ', ' . $transparency . ')';
			
			return $rgba_color;
		}
	}
}

if ( ! function_exists( 'ratio_edge_header_meta' ) ) {
	/**
	 * Function that echoes meta data if our seo is enabled
	 */
	function ratio_edge_header_meta() { ?>
		<meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<link rel="profile" href="http://gmpg.org/xfn/11"/>
		<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php endif; ?>
	<?php }
	
	add_action( 'ratio_edge_header_meta', 'ratio_edge_header_meta' );
}

if ( ! function_exists( 'ratio_edge_user_scalable_meta' ) ) {
	/**
	 * Function that outputs user scalable meta if responsiveness is turned on
	 * Hooked to ratio_edge_header_meta action
	 */
	function ratio_edge_user_scalable_meta() {
		//is responsiveness option is chosen?
		if ( ratio_edge_is_responsive_on() ) { ?>
			<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
		<?php } else { ?>
			<meta name="viewport" content="width=1200,user-scalable=yes">
		<?php }
	}
	
	add_action( 'ratio_edge_header_meta', 'ratio_edge_user_scalable_meta' );
}

if ( ! function_exists( 'ratio_edge_get_page_id' ) ) {
	/**
	 * Function that returns current page / post id.
	 * Checks if current page is woocommerce page and returns that id if it is.
	 * Checks if current page is any archive page (category, tag, date, author etc.) and returns -1 because that isn't
	 * page that is created in WP admin.
	 *
	 * @return int
	 *
	 * @version 0.1
	 *
	 * @see ratio_edge_is_woocommerce_installed()
	 * @see ratio_edge_is_woocommerce_shop()
	 */
	function ratio_edge_get_page_id() {
		if ( ratio_edge_is_woocommerce_installed() && ( ratio_edge_is_woocommerce_shop() || ratio_edge_is_product_category() || ratio_edge_is_product_tag() ) ) {
			return ratio_edge_get_woo_shop_page_id();
		}
		
		if ( ratio_edge_is_default_wp_template() ) {
			return - 1;
		}
		
		return get_queried_object_id();
	}
}

if ( ! function_exists( 'ratio_edge_is_default_wp_template' ) ) {
	/**
	 * Function that checks if current page archive page, search, 404 or default home blog page
	 * @return bool
	 *
	 * @see is_archive()
	 * @see is_search()
	 * @see is_404()
	 * @see is_front_page()
	 * @see is_home()
	 */
	function ratio_edge_is_default_wp_template() {
		return is_archive() || is_search() || is_404() || ( is_front_page() && is_home() );
	}
}

if ( ! function_exists( 'ratio_edge_get_page_template_name' ) ) {
	/**
	 * Returns current template file name without extension
	 * @return string name of current template file
	 */
	function ratio_edge_get_page_template_name() {
		$file_name = '';
		
		if ( ! ratio_edge_is_default_wp_template() ) {
			$file_name_without_ext = preg_replace( '/\\.[^.\\s]{3,4}$/', '', basename( get_page_template() ) );
			
			if ( $file_name_without_ext !== '' ) {
				$file_name = $file_name_without_ext;
			}
		}
		
		return $file_name;
	}
}

if ( ! function_exists( 'ratio_edge_has_shortcode' ) ) {
	/**
	 * Function that checks whether shortcode exists on current page / post
	 *
	 * @param string shortcode to find
	 * @param string content to check. If isn't passed current post content will be used
	 *
	 * @return bool whether content has shortcode or not
	 */
	function ratio_edge_has_shortcode( $shortcode, $content = '' ) {
		$has_shortcode = false;
		
		if ( $shortcode ) {
			//if content variable isn't past
			if ( $content == '' ) {
				//take content from current post
				$page_id = ratio_edge_get_page_id();
				if ( ! empty( $page_id ) ) {
					$current_post = get_post( $page_id );
					
					if ( is_object( $current_post ) && property_exists( $current_post, 'post_content' ) ) {
						$content = $current_post->post_content;
					}
				}
			}
			
			//does content has shortcode added?
			if ( stripos( $content, '[' . $shortcode ) !== false ) {
				$has_shortcode = true;
			}
		}
		
		return $has_shortcode;
	}
}

if ( ! function_exists( 'ratio_edge_get_dynamic_sidebar' ) ) {
	/**
	 * Return Custom Widget Area content
	 *
	 * @return string
	 */
	function ratio_edge_get_dynamic_sidebar( $index = 1 ) {
		ob_start();
		dynamic_sidebar( $index );
		$sidebar_contents = ob_get_clean();
		
		return $sidebar_contents;
	}
}

if ( ! function_exists( 'ratio_edge_get_sidebar' ) ) {
	/**
	 * Return Sidebar
	 *
	 * @return string
	 */
	function ratio_edge_get_sidebar() {
		
		$id = ratio_edge_get_page_id();
		
		$sidebar = "sidebar";
		
		if ( get_post_meta( $id, 'edgtf_custom_sidebar_meta', true ) != '' ) {
			$sidebar = get_post_meta( $id, 'edgtf_custom_sidebar_meta', true );
		} else {
			if ( is_single() && ratio_edge_options()->getOptionValue( 'blog_single_custom_sidebar' ) != '' ) {
				$sidebar = esc_attr( ratio_edge_options()->getOptionValue( 'blog_single_custom_sidebar' ) );
			} elseif ( ( is_archive() || ( is_home() && is_front_page() ) ) && ratio_edge_options()->getOptionValue( 'blog_custom_sidebar' ) != '' ) {
				$sidebar = esc_attr( ratio_edge_options()->getOptionValue( 'blog_custom_sidebar' ) );
			} elseif ( is_page() && ratio_edge_options()->getOptionValue( 'page_custom_sidebar' ) != '' ) {
				$sidebar = esc_attr( ratio_edge_options()->getOptionValue( 'page_custom_sidebar' ) );
			}
		}
		
		return $sidebar;
	}
}

if ( ! function_exists( 'ratio_edge_sidebar_columns_class' ) ) {
	
	/**
	 * Return classes for columns holder when sidebar is active
	 *
	 * @return array
	 */
	
	function ratio_edge_sidebar_columns_class() {
		
		$sidebar_class  = array();
		$sidebar_layout = ratio_edge_sidebar_layout();
		
		switch ( $sidebar_layout ):
			case 'sidebar-33-right':
				$sidebar_class[] = 'edgtf-two-columns-66-33';
				break;
			case 'sidebar-25-right':
				$sidebar_class[] = 'edgtf-two-columns-75-25';
				break;
			case 'sidebar-33-left':
				$sidebar_class[] = 'edgtf-two-columns-33-66';
				break;
			case 'sidebar-25-left':
				$sidebar_class[] = 'edgtf-two-columns-25-75';
				break;
		
		endswitch;
		
		$sidebar_class[] = 'clearfix';
		
		return ratio_edge_class_attribute( $sidebar_class );
		
	}
	
}

if ( ! function_exists( 'ratio_edge_sidebar_layout' ) ) {
	
	/**
	 * Function that check is sidebar is enabled and return type of sidebar layout
	 */
	
	function ratio_edge_sidebar_layout() {
		
		$sidebar_layout = '';
		$page_id        = ratio_edge_get_page_id();
		
		$page_sidebar_meta = get_post_meta( $page_id, 'edgtf_sidebar_meta', true );
		
		if ( ( $page_sidebar_meta !== '' ) && $page_id !== - 1 ) {
			if ( $page_sidebar_meta == 'no-sidebar' ) {
				$sidebar_layout = '';
			} else {
				$sidebar_layout = $page_sidebar_meta;
			}
		} else {
			if ( is_single() && ratio_edge_options()->getOptionValue( 'blog_single_sidebar_layout' ) ) {
				$sidebar_layout = esc_attr( ratio_edge_options()->getOptionValue( 'blog_single_sidebar_layout' ) );
			} elseif ( ( is_archive() || ( is_home() && is_front_page() ) ) && ratio_edge_options()->getOptionValue( 'archive_sidebar_layout' ) ) {
				$sidebar_layout = esc_attr( ratio_edge_options()->getOptionValue( 'archive_sidebar_layout' ) );
			} elseif ( is_page() && ratio_edge_options()->getOptionValue( 'page_sidebar_layout' ) ) {
				$sidebar_layout = esc_attr( ratio_edge_options()->getOptionValue( 'page_sidebar_layout' ) );
			}
		}
		
		if ( ! empty( $sidebar_layout ) && ! is_active_sidebar( ratio_edge_get_sidebar() ) ) {
			$sidebar_layout = '';
		}
		
		return $sidebar_layout;
	}
}

if( !function_exists('ratio_edge_page_custom_style') ) {
	
	/**
	 * Function that print custom page style
	 */
	
	function ratio_edge_page_custom_style() {
		$style = array();
		$html  = '';
		$style = apply_filters('ratio_edge_add_page_custom_style', $style);
		if(is_array($style) && count($style)) {
			$html .= implode(' ', $style);
			wp_add_inline_style( 'ratio-edge-modules', $html);
		}
	}
	add_action('wp_enqueue_scripts', 'ratio_edge_page_custom_style');
}

if ( ! function_exists( 'ratio_edge_vc_custom_style' ) ) {
	/**
	 * Function that print custom page style
	 */
	function ratio_edge_vc_custom_style( $style ) {
		if ( ratio_edge_visual_composer_installed() && ( is_page() || is_single() || is_singular( 'portfolio-item' ) ) ) {
			$page_id = ratio_edge_get_page_id();
			
			$shortcodes_custom_css = get_post_meta( $page_id, '_wpb_shortcodes_custom_css', true );
			if ( ! empty( $shortcodes_custom_css ) ) {
				$style[] = get_post_meta( $page_id, '_wpb_shortcodes_custom_css', true );
			}
			
			$post_custom_css = get_post_meta( $page_id, '_wpb_post_custom_css', true );
			if ( ! empty( $post_custom_css ) ) {
				$style[] = get_post_meta( $page_id, '_wpb_post_custom_css', true );
			}
		}
		
		return $style;
	}
	
	add_filter( 'ratio_edge_add_page_custom_style', 'ratio_edge_vc_custom_style' );
}

if ( ! function_exists( 'ratio_edge_get_unique_page_class' ) ) {
	/**
	 * Returns unique page class based on post type and page id
	 *
	 * @return string
	 */
	function ratio_edge_get_unique_page_class() {
		$id         = ratio_edge_get_page_id();
		$page_class = '';
		
		if ( ratio_edge_is_woocommerce_installed() && is_product() ) {
			$id = get_the_ID();
		}
		
		if ( is_single() ) {
			$page_class = '.postid-' . $id;
		} elseif ( is_home() ) {
			$page_class .= '.home';
		} elseif ( is_archive() || $id === ratio_edge_get_woo_shop_page_id() ) {
			$page_class .= '.archive';
		} elseif ( is_search() ) {
			$page_class .= '.search';
		} elseif ( is_404() ) {
			$page_class .= '.error404';
		} else {
			$page_class .= '.page-id-' . $id;
		}
		
		return $page_class;
	}
}

if ( ! function_exists( 'ratio_edge_container_style' ) ) {
	
	/**
	 * Function that return container style
	 */
	
	function ratio_edge_container_style( $style ) {
		$id           = ratio_edge_get_page_id();
		$class_prefix = ratio_edge_get_unique_page_class();
		
		$container_selector = array(
			$class_prefix . ' .edgtf-content .edgtf-content-inner > .edgtf-container',
			$class_prefix . ' .edgtf-content .edgtf-content-inner > .edgtf-full-width',
		);
		
		$container_class       = array();
		$page_background_color = get_post_meta( $id, "edgtf_page_background_color_meta", true );
		
		if ( $page_background_color ) {
			$container_class['background-color'] = $page_background_color;
		}
		
		$current_style = ratio_edge_dynamic_css( $container_selector, $container_class );
		$style[]       = $current_style;
		
		return $style;
		
	}
	
	add_filter( 'ratio_edge_add_page_custom_style', 'ratio_edge_container_style' );
}

if ( ! function_exists( 'ratio_edge_page_padding' ) ) {
	/**
	 * Function that return container style
	 */
	function ratio_edge_page_padding( $style ) {
		$id           = ratio_edge_get_page_id();
		$class_prefix = ratio_edge_get_unique_page_class();
		
		$page_selector = array(
			$class_prefix . ' .edgtf-content .edgtf-content-inner > .edgtf-container > .edgtf-container-inner',
			$class_prefix . ' .edgtf-content .edgtf-content-inner > .edgtf-full-width > .edgtf-full-width-inner'
		);
		
		$page_css = array();
		
		$page_padding = get_post_meta( $id, 'edgtf_page_padding_meta', true );
		
		if ( $page_padding !== '' ) {
			$page_css['padding'] = $page_padding;
		}
		
		$current_style = ratio_edge_dynamic_css( $page_selector, $page_css );
		
		$style[] = $current_style;
		
		return $style;
	}
	
	add_filter( 'ratio_edge_add_page_custom_style', 'ratio_edge_page_padding' );
}

if ( ! function_exists( 'ratio_edge_page_boxed_style' ) ) {
	/**
	 * Function that return container style
	 */
	function ratio_edge_page_boxed_style( $style ) {
		$id           = ratio_edge_get_page_id();
		$class_prefix = ratio_edge_get_unique_page_class();
		
		$page_selector = array(
			$class_prefix . '.edgtf-boxed .edgtf-wrapper'
		);
		$page_css      = array();
		
		$page_background_color           = get_post_meta( $id, 'edgtf_page_background_color_in_box_meta', true );
		$page_background_image           = get_post_meta( $id, 'edgtf_boxed_background_image_meta', true );
		$page_background_image_repeating = get_post_meta( $id, 'edgtf_boxed_background_image_repeating_meta', true );
		
		if ( $page_background_color !== '' ) {
			$page_css['background-color'] = $page_background_color;
		}
		if ( $page_background_image !== '' && $page_background_image_repeating != '' ) {
			$page_css['background-image']  = 'url(' . $page_background_image . ')';
			$page_css['background-repeat'] = $page_background_image_repeating;
			
			if ( $page_background_image_repeating == 'no' ) {
				$page_css['background-position'] = 'center 0';
				$page_css['background-repeat']   = 'no-repeat';
			} else {
				$page_css['background-position'] = '0 0';
				$page_css['background-repeat']   = 'repeat';
			}
		}
		
		$current_style = ratio_edge_dynamic_css( $page_selector, $page_css );
		
		$style[] = $current_style;
		
		return $style;
		
	}
	
	add_filter( 'ratio_edge_add_page_custom_style', 'ratio_edge_page_boxed_style' );
}

if ( ! function_exists( 'ratio_edge_print_custom_css' ) ) {
	/**
	 * Prints out custom css from theme options
	 */
	function ratio_edge_print_custom_css() {
		$custom_css = ratio_edge_options()->getOptionValue( 'custom_css' );
		
		if ( $custom_css !== '' ) {
			wp_add_inline_style( 'ratio-edge-modules', $custom_css );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_print_custom_css' );
}

if ( ! function_exists( 'ratio_edge_print_custom_js' ) ) {
	/**
	 * Prints out custom css from theme options
	 */
	function ratio_edge_print_custom_js() {
		$custom_js = ratio_edge_options()->getOptionValue( 'custom_js' );
		
		if ( ! empty( $custom_js ) ) {
			wp_add_inline_script( 'ratio-edge-modules', $custom_js );
		}
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_print_custom_js' );
}

if ( ! function_exists( 'ratio_edge_get_global_variables' ) ) {
	/**
	 * Function that generates global variables and put them in array so they could be used in the theme
	 */
	function ratio_edge_get_global_variables() {
		
		$global_variables      = array();
		$element_appear_amount = - 150;
		
		$global_variables['edgtfAddForAdminBar']      = is_admin_bar_showing() ? 32 : 0;
		$global_variables['edgtfElementAppearAmount'] = ratio_edge_options()->getOptionValue( 'element_appear_amount' ) !== '' ? ratio_edge_options()->getOptionValue( 'element_appear_amount' ) : $element_appear_amount;
		$global_variables['edgtfFinishedMessage']     = esc_html__( 'No more posts', 'ratio' );
		$global_variables['edgtfMessage']             = esc_html__( 'Loading new posts...', 'ratio' );
		
		$global_variables = apply_filters( 'ratio_edge_js_global_variables', $global_variables );
		
		wp_localize_script( 'ratio-edge-modules', 'edgtfGlobalVars', array(
			'vars' => $global_variables
		) );
		
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_get_global_variables' );
}

if ( ! function_exists( 'ratio_edge_per_page_js_variables' ) ) {
	/**
	 * Outputs global JS variable that holds page settings
	 */
	function ratio_edge_per_page_js_variables() {
		$per_page_js_vars = apply_filters( 'ratio_edge_per_page_js_vars', array() );
		
		wp_localize_script( 'ratio-edge-modules', 'edgtfPerPageVars', array(
			'vars' => $per_page_js_vars
		) );
	}
	
	add_action( 'wp_enqueue_scripts', 'ratio_edge_per_page_js_variables' );
}

if ( ! function_exists( 'ratio_edge_content_elem_style_attr' ) ) {
	/**
	 * Defines filter for adding custom styles to content HTML element
	 */
	function ratio_edge_content_elem_style_attr() {
		$styles = apply_filters( 'ratio_edge_content_elem_style_attr', array() );
		
		ratio_edge_inline_style( $styles );
	}
}

if ( ! function_exists( 'ratio_edge_is_woocommerce_installed' ) ) {
	/**
	 * Function that checks if woocommerce is installed
	 * @return bool
	 */
	function ratio_edge_is_woocommerce_installed() {
		return function_exists( 'is_woocommerce' );
	}
}

if ( ! function_exists( 'ratio_edge_visual_composer_installed' ) ) {
	/**
	 * Function that checks if visual composer installed
	 * @return bool
	 */
	function ratio_edge_visual_composer_installed() {
		//is Visual Composer installed?
		if ( class_exists( 'WPBakeryVisualComposerAbstract' ) ) {
			return true;
		}
		
		return false;
	}
}

if ( ! function_exists( 'ratio_edge_contact_form_7_installed' ) ) {
	/**
	 * Function that checks if contact form 7 installed
	 * @return bool
	 */
	function ratio_edge_contact_form_7_installed() {
		//is Contact Form 7 installed?
		if ( defined( 'WPCF7_VERSION' ) ) {
			return true;
		}
		
		return false;
	}
}

if ( ! function_exists( 'ratio_edge_is_wpml_installed' ) ) {
	/**
	 * Function that checks if WPML plugin is installed
	 * @return bool
	 *
	 * @version 0.1
	 */
	function ratio_edge_is_wpml_installed() {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}
}

if ( ! function_exists( 'ratio_edge_get_module_part' ) ) {
	function ratio_edge_get_module_part( $module ) {
		return $module;
	}
}

if ( ! function_exists( 'ratio_edge_max_image_width_srcset' ) ) {
	/**
	 * Set max width for srcset to 1920
	 *
	 * @return int
	 */
	function ratio_edge_max_image_width_srcset() {
		return 1920;
	}
	
	add_filter( 'max_srcset_image_width', 'ratio_edge_max_image_width_srcset' );
}

if ( ! function_exists( 'ratio_edge_is_gutenberg_installed' ) ) {
	/**
	 * Function that checks if Gutenberg plugin installed
	 * @return bool
	 */
	function ratio_edge_is_gutenberg_installed() {
		return function_exists( 'is_gutenberg_page' ) && is_gutenberg_page();
	}
}

if ( ! function_exists( 'ratio_edge_is_wp_gutenberg_installed' ) ) {
	/**
	 * Function that checks if WordPress 5.x with Gutenberg editor installed
	 * @return bool
	 */
	function ratio_edge_is_wp_gutenberg_installed() {
		return class_exists( 'WP_Block_Type' );
	}
}

if( ! function_exists( 'ratio_edge_is_theme_registered' ) ) {
    function ratio_edge_is_theme_registered() {
        if( function_exists( 'ratio_core_is_theme_registered' ) ){
            return ratio_core_is_theme_registered();
        } else {
            return false;
        }
    }
}

if( ! function_exists( 'ratio_edge_add_registration_admin_notice' ) ) {
    function ratio_edge_add_registration_admin_notice() {
        if( ratio_edge_core_installed() &&  ! ratio_edge_is_theme_registered() ) {
            ?>
            <div class="error">
                <p>
                    <?php
                    echo wp_kses_post( sprintf(
                        __( 'Your copy of the theme has not been activated. Please navigate to <a href="%s">Ratio Dashboard</a> where you can input your purchase code and activate your copy of the theme so you can have access to all the theme features, elements & options.', 'ratio' ),
                        admin_url('admin.php?page=ratio_core_dashboard')
                    ) );
                    ?>
                </p>
            </div>
            <?php
        }
    }
    add_action('admin_notices', 'ratio_edge_add_registration_admin_notice');
}

