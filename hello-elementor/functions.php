<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '2.9.0' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Allow active/inactive via the Experiments
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

add_shortcode('api-data','api_calling');

function api_calling()
{
	$url='https://jsonplaceholder.typicode.com/users';
	$args= array('method'=>'GET');

	$response=wp_remote_get($url, $args);

	if(is_wp_error($response))
	{
		$error_message=$response->get_error_message();
		echo "something gets wrong: $error_message";
	}

	$results=json_decode(wp_remote_retrieve_body($response));

	$html='';
	$html .='<table>';
	$html .='<tr>';
	$html .='<td>id</td>';
	$html .='<td>Name</td>';
	$html .='<td>Username</td>';
	$html .='<td>Email</td>';
 
	$html .='</tr>';

foreach($results as $result)
{
	$html .='<tr>';
	$html .='<td>'.$result->id.'</td>';
	$html .='<td>'. $result->name.'</td>';
	$html .='<td>'.$result->username.'</td>';
	$html .='<td>'.$result->email.'</td>';
 
	$html .='</tr>';


}

	return $html;
}

add_shortcode('api-team-names', 'api_team_names');

function api_team_names()
{
    $url = 'https://api.cricapi.com/v1/cricScore?apikey=42d886f8-cbb9-4618-b83d-3ded8b989dba'; // Replace with the actual API endpoint URL
    $args = array('method' => 'GET');

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    }

    $results = json_decode(wp_remote_retrieve_body($response), true);

    $html = '';
    $html .= '<table>';
    $html .= '<tr>';
    $html .= '<td>Team 1</td>';
    $html .= '<td>Team 2</td>';
    $html .= '</tr>';

     // Limit the loop to the first five teams
	 $teamsToShow = min(5, count($results['data']));

	 for ($i = 0; $i < $teamsToShow; $i++) {
		 $match = $results['data'][$i];
		 $html .= '<tr>';
		 $html .= '<td>' . $match['t1'] . '</td>';
		 $html .= '<td>' . $match['t2'] . '</td>';
		 $html .= '</tr>';
	 }
 
	 $html .= '</table>';

    return $html;
}

function enqueue_jquery() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery');

function enqueue_jquery_and_custom_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-match-cards-script', get_template_directory_uri() . '/custom-match-cards-script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_jquery_and_custom_script');




//https://api.cricapi.com/v1/cricScore?apikey=ee0a0cbe-115b-4ed9-b055-884d44413c9a

add_shortcode('api-match-cards', 'api_match_cards');

function api_match_cards()
{
    $url = 'https://api.cricapi.com/v1/cricScore?apikey=ee0a0cbe-115b-4ed9-b055-884d44413c9a'; // Replace with the actual API endpoint URL
    $args = array('method' => 'GET');

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
        return '';
    }

    $results = json_decode(wp_remote_retrieve_body($response), true);

    $html = '';
	// Check if the 'data' key exists in the API response
	if (isset($results['data']) && is_array($results['data'])) {
	// Display cards for the first five matches with at least one score
	$matchesProcessed = 0;
    // Display cards for matches with at least one score
    foreach ($results['data'] as $match) {
        if (!empty($match['t1s']) || !empty($match['t2s'])) {
            $html .= '<div style="background-color: #f0f0f0; border-radius: 10px; margin-bottom: 20px; padding: 10px; display: flex;">';
            $html .= '<div style="flex: 1; text-align: center;">';
            $html .= '<h3>Team 1</h3>';
            $html .= '<p>' . $match['t1'] . '</p>';

            // Check if 't1img' exists before displaying it
            if (isset($match['t1img'])) {
                $html .= '<img src="' . $match['t1img'] . '" alt="' . $match['t1'] . '" width="48">';
            }

            // Check if 't1s' exists before displaying it
            if (isset($match['t1s'])) {
                $html .= '<p>Score: <span class="team1-score">' . $match['t1s'] . '</span></p>';
            }

            $html .= '</div>';
            $html .= '<div style="flex: 1; text-align: center;">';
            $html .= '<h3>Team 2</h3>';
            $html .= '<p>' . $match['t2'] . '</p>';

            // Check if 't2img' exists before displaying it
            if (isset($match['t2img'])) {
                $html .= '<img src="' . $match['t2img'] . '" alt="' . $match['t2'] . '" width="48">';
            }

            // Check if 't2s' exists before displaying it
            if (isset($match['t2s'])) {
                $html .= '<p>Score: <span class="team2-score">' . $match['t2s'] . '</span></p>';
            }

            $html .= '</div>';

            // Display match type, status, and match state in the middle of the card
            $html .= '<div style="flex: 1; text-align: center;">';
            $html .= '<p>Match Type: ' . $match['matchType'] . '</p>';
            $html .= '<p>Status: ' . $match['status'] . '</p>';
            $html .= '<p>Match State: ' . $match['ms'] . '</p>';
            $html .= '</div>';

            $html .= '</div>';

			$matchesProcessed++;

            if ($matchesProcessed >= 5) {
                // Stop processing after five matches
                break;
            }
        }
    }
}
	else {
        $html .= '<p>Max Api Hits Reached. No Data is available</p>';
    }

    // Enqueue jQuery and custom JavaScript
    wp_enqueue_script('custom-match-cards-script', get_template_directory_uri() . '/path/to/custom-match-cards-script.js', array('jquery'), null, true);

    return $html;
}
