<?php
/** SECURITY CHECK - Ensure this file is only included from the plugin class.**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class YTPG_Admin_Menu {

	// This stores the name we use to save settings in the WordPress database.
	// It's like a label on a storage box where we keep all our plugin settings.
	private $option_name = 'ytpg_settings';

	public function __construct() {
		// When WordPress builds the admin menu, add our settings page
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

		// When WordPress initializes admin features, register our settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// When WordPress initializes admin features, also check if user clicked "Clear Cache"
		add_action( 'admin_init', array( $this, 'handle_cache_clear' ) );
	}

		/**
		 * ADD ADMIN MENU
		 * Creates a link in the WordPress admin sidebar under "Settings"
		 * This is where users will configure the plugin
		 */
	public function add_admin_page() {
		add_menu_page(
			'YouTube Playlist Grid Settings',  // Page title (shows in browser tab)
			'YT Playlist Grid',                // Menu title (shows in sidebar)
			'manage_options',                  // Required user permission (only admins)
			'youtube-playlist-grid',           // Unique page identifier (slug)
			array( $this, 'settings_page' ),      // Function to display the page
			'dashicons-youtube'                // Custom dashicon YT image for menu page
		);
	}

	/**
	 * REGISTER SETTINGS
	 * Tells WordPress "these settings are safe to save to the database"
	 * Without this, WordPress won't save our settings for security reasons
	 */
	public function register_settings() {
		register_setting(
			'ytpg_settings_group',
			$this->option_name,
			array( $this, 'sanitize_settings' ) // your sanitization method
		);
	}

	// Add this method to your class
	public function sanitize_settings( $values ) {
		// Always return the sanitized array (or scalar)
		$sanitized = array();

		// Example: if your option is an array of values
		foreach ( (array) $values as $key => $value ) {
			if ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} elseif ( is_array( $value ) ) {
				$sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
			} elseif ( is_int( $value ) ) {
				$sanitized[ $key ] = (int) $value;
			} // ... handle other types: urls, emails, etc.
			else {
				$sanitized[ $key ] = $value; // fallback
			}
		}

		return $sanitized;
	}

	/**
	 * HANDLE CACHE CLEAR
	 * This function runs when someone clicks the "Clear Cache" button
	 * It deletes the stored playlist data so fresh data is fetched next time
	 */
	public function handle_cache_clear() {
		// Check if the clear cache button was clicked AND verify the security token
		if ( isset( $_POST['ytpg_clear_cache'] ) && check_admin_referer( 'ytpg_clear_cache_action', 'ytpg_clear_cache_nonce' ) ) {

			// Get our saved settings from the database
			$settings    = get_option( $this->option_name );
			$playlist_id = $settings['playlist_id'] ?? '';
			$max_results = $settings['max_results'] ?? 6;

			// Only try to clear cache if we have a playlist ID
			if ( ! empty( $playlist_id ) ) {
				// Generate the same cache key we use to store the data
				// This is like finding the right storage box to empty
				$cache_key = 'ytpg_videos_' . md5( $playlist_id . $max_results );

				// Delete the cached data from WordPress
				delete_transient( $cache_key );

				// Store success message in a transient (temporary storage)
				// This way we can display it once and it won't duplicate
				// set_transient('ytpg_cache_cleared_notice', true, 30);
				add_settings_error(
					'ytpg_messages',               // Slug (can be anything)
					'ytpg_cache_cleared',          // Unique code
					'Cache cleared successfully and playlist refreshed!', // Message
					'success'                      // Type: 'success', 'error', 'warning', 'info'
				);
			}
		}
	}

	/**
	 * SETTINGS PAGE
	 * This creates the entire admin interface where users configure the plugin
	 * It displays input fields for API key, playlist ID, number of videos, and custom CSS
	 */
	public function settings_page() {
		// Get our current settings from the database
		$settings = get_option( $this->option_name );

		?>
	<div class="wrap">
	<h1>YouTube Playlist Grid Settings</h1>

		<?php
		// if (get_transient('ytpg_cache_cleared_notice')) {
		// echo '<div class="notice notice-success is-dismissible"><p>Cache cleared successfully! The playlist will refresh on the next page load.</p></div>';
		// Delete the transient so message only shows once
		// delete_transient('ytpg_cache_cleared_notice');
		// }
		?>

	<!-- MAIN SETTINGS FORM -->
	<!-- This form saves to WordPress using options.php -->
	<form method="post" action="options.php">
		<?php
		// Add hidden security fields that WordPress requires
		settings_fields( 'ytpg_settings_group' );
		?>

	<!-- USAGE INSTRUCTIONS -->
	<h2>Usage:</h2>
	<p>Fill out the fields below and use the Gutenberg Block or the shortcode <code>[youtube_playlist_grid]</code> in any page or post to display your playlist grid.</p>

		<table class="form-table">
			<!-- API KEY FIELD -->
			<tr>
				<th scope="row">
					<label for="api_key">YouTube API Key</label>
				</th>
				<td>
					<!-- Text input for the YouTube API key -->
					<input type="text" id="api_key" name="<?php echo esc_html( $this->option_name ); ?>[api_key]"
							value="<?php echo esc_attr( $settings['api_key'] ?? '' ); ?>"
							class="regular-text" />
					<p class="description">Get your API key from <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a></p>
				</td>
			</tr>

			<!-- PLAYLIST ID FIELD -->
			<tr>
				<th scope="row">
					<label for="playlist_id">Playlist ID or URL</label>
				</th>
				<td>
					<!-- Text input for the playlist ID or full URL -->
					<input type="text" id="playlist_id" name="<?php echo esc_html( $this->option_name ); ?>[playlist_id]"
							value="<?php echo esc_attr( $settings['playlist_id'] ?? '' ); ?>"
							class="regular-text" />
					<p class="description">Enter the playlist ID (e.g., PLxxxxxxxxxxx) or full YouTube playlist URL</p>
				</td>
			</tr>

			<!-- NUMBER OF VIDEOS FIELD -->
			<tr>
				<th scope="row">
					<label for="max_results">Number of Videos</label>
				</th>
				<td>
					<!-- Number input limited between 1 and 50 -->
					<input type="number" id="max_results" name="<?php echo esc_html( $this->option_name ); ?>[max_results]"
							value="<?php echo esc_attr( $settings['max_results'] ?? 6 ); ?>"
							min="1" max="50" />
					<p class="description">Number of videos to display (1-50)</p>
				</td>
			</tr>

			<!-- Layout Selector -->
			<tr>
				<th scope="row">
					<label for="gallery_style">Gallery Style</label>
				</th>
				<td>
				<!-- Dropdown select for choosing the gallery layout style -->
				<select id="gallery_style" name="<?php echo esc_html( $this->option_name ); ?>[gallery_style]">
					<option value="simple" <?php selected( $settings['gallery_style'] ?? 'simple', 'simple' ); ?>>Simple Layout</option>
					<option value="modern" <?php selected( $settings['gallery_style'] ?? 'simple', 'modern' ); ?>>Modern Layout</option>
				</select>
				<p class="description">Choose a layout style you want to use</p>
				</td>
			</tr>

			<!-- CUSTOM CSS FIELD -->
			<tr>
				<th scope="row">
					<label for="custom_css">Custom CSS</label>
				</th>
				<td>
					<!-- Large text area for custom CSS code -->
					<textarea id="custom_css" name="<?php echo esc_html( $this->option_name ); ?>[custom_css]"
								rows="10" class="large-text code"><?php echo esc_textarea( $settings['custom_css'] ?? '' ); ?></textarea>
					<p class="description">Add your custom CSS styles here</p>
				</td>
			</tr>
		</table>

		<?php
		// Display the "Save Changes" button
		submit_button();
		settings_errors();
		?>
	</form>

	<hr>

	<!-- CACHE MANAGEMENT SECTION -->
	<h2>Cache Management</h2>
	<p>The playlist is cached for 1 hour to improve performance and reduce API usage. Use the button below to manually refresh the cache after uploading new videos.</p>

	<!-- CLEAR CACHE FORM -->
	<!-- This is a separate form that only clears the cache -->
	<form method="post" action="">
		<?php
		// Add a security token to prevent unauthorized cache clearing
		wp_nonce_field( 'ytpg_clear_cache_action', 'ytpg_clear_cache_nonce' );
		?>
		<input type="submit" name="ytpg_clear_cache" class="button button-secondary" value="Clear Cache & Refresh Playlist" />
	</form>

	<hr>
	</div>
		<?php
	}
}
new YTPG_admin_menu();
