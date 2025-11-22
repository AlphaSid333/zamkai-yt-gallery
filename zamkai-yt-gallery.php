<?php
/**
 * Plugin Name: Zamkai YT Gallery
 * Description: Displays YouTube playlist videos in a customizable grid format
 * Author: Zamkai master
 * Text Domain: zamkai-yt-gallery
 * Version: 1.0
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// This prevents people from accessing this file directly in their browser
// It's a security measure to ensure this code only runs within WordPress
if (!defined('ABSPATH')) exit;

/**
 * MAIN PLUGIN CLASS
 * This is the container for all our plugin's functionality
 * Think of it as the "brain" of the plugin that coordinates everything
 */
class YouTube_Playlist_Grid {
    
    private $option_name = 'ytpg_settings';
    /**
     * CONSTRUCTOR - This runs automatically when the plugin loads
     * It "hooks" our functions into WordPress so they run at the right times
     * Think of hooks as saying "Hey WordPress, when you do X, also run my function Y"
     */
    public function __construct() {
        
        // Register our shortcode [youtube_playlist_grid] so it displays videos
        add_shortcode('youtube_playlist_grid', array($this, 'render_grid'));
        
        // When WordPress loads page styles, add our CSS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        add_action( 'enqueue_block_editor_assets', array($this,'enqueue_styles') );

        add_action('init', array($this, 'zamkai_yt_register_block'));

    // Include the admin class
        require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
        
        //This registers the block for the gallery
        // add_action('init', 'zamkai_carousel_register_block');
    }
    
   
    
/**
 * ENQUEUE STYLES
 * This loads the CSS styles that make our video grid look good
 * It runs on every front-end page (not admin pages)
 */
public function enqueue_styles() {
    // Get our settings to access custom CSS
    $settings = get_option($this->option_name);
    

// Get the gallery style setting (default to 'simple' if not set)
        $gallery_style = $settings['gallery_style'] ?? 'simple';

        if ($gallery_style === 'modern') {
            wp_register_style(
                'ytpg-default',                          // Handle (unique identifier)
                plugins_url('css/modern-yt-cards.css', __FILE__), // URL to the CSS file
                array(),                                 // Dependencies (add if needed, e.g., array('wp-block-library'))
                '1.0.0',                                 // Version (update for cache busting)
                'all'                                    // Media type
            );
        } else{
            wp_register_style(
                'ytpg-default',                          // Handle (unique identifier)
                plugins_url('css/yt-cards.css', __FILE__), // URL to the CSS file
                array(),                                 // Dependencies (add if needed, e.g., array('wp-block-library'))
                '1.0.0',                                 // Version (update for cache busting)
                'all'                                    // Media type
            );
        }
        wp_enqueue_style('ytpg-default');
    // Enqueue the external CSS file (replace __FILE__ with $this->plugin_file if needed)
    
    
    // If user added custom CSS in settings, add that too
    // This allows them to override our default styles (loads after the file)
    if (!empty($settings['custom_css'])) {
        wp_add_inline_style('ytpg-default', $settings['custom_css']);
    }
}
    
    /**
     * EXTRACT PLAYLIST ID
     * Takes either a playlist ID or full YouTube URL and returns just the ID
     * Example: Converts "https://youtube.com/playlist?list=PLxxx" to "PLxxx"
     */
    private function extract_playlist_id($input) {
        // If it's already just an ID (letters, numbers, underscores, hyphens)
        if (preg_match('/^[A-Za-z0-9_-]+$/', $input)) {
            return $input;
        }
        
        // If it's a URL, extract the ID from the "list=" parameter
        if (preg_match('/[?&]list=([A-Za-z0-9_-]+)/', $input, $matches)) {
            return $matches[1];
        }
        
        // If we can't figure it out, just return what they gave us
        return $input;
    }
    
    /**
     * FETCH PLAYLIST VIDEOS
     * Contacts YouTube's API and gets the list of videos from the playlist
     * This is where we actually talk to YouTube to get video information
     */
    private function fetch_playlist_videos($api_key, $playlist_id, $max_results) {
        // Clean up the playlist ID (remove URL parts if needed)
        $playlist_id = $this->extract_playlist_id($playlist_id);
        
        // Build the YouTube API URL with our parameters
        // add_query_arg safely adds parameters to a URL
        $api_url = add_query_arg(array(
            'part' => 'snippet',              // We want video details (snippet)
            'playlistId' => $playlist_id,     // Which playlist to get
            'maxResults' => $max_results,     // How many videos to fetch
            'key' => $api_key                 // Our API key for authentication
        ), 'https://www.googleapis.com/youtube/v3/playlistItems');
        
        // Make the HTTP request to YouTube
        // timeout: 15 means give up after 15 seconds if no response
        $response = wp_remote_get($api_url, array('timeout' => 15));
        
        // Check if the request failed (network error, etc.)
        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }
        
        // Get the response body (the actual data YouTube sent back)
        $body = wp_remote_retrieve_body($response);
        
        // Convert the JSON response to a PHP array we can use
        $data = json_decode($body, true);
        
        // Check if YouTube sent back an error (wrong API key, wrong playlist ID, etc.)
        if (isset($data['error'])) {
            return array('error' => $data['error']['message']);
        }
        
        // Everything worked! Return the video data
        return $data;
    }
    
    /**
     * RENDER GRID
     * This is the main function that displays the video grid on the front-end
     * It's called when someone uses the [youtube_playlist_grid] shortcode
     */
    public function render_grid($atts) {
        // Get our saved settings from the database
        $settings = get_option($this->option_name);
        
        // Extract the settings we need (use defaults if not set)
        $api_key = $settings['api_key'] ?? '';
        $playlist_id = $settings['playlist_id'] ?? '';
        $max_results = $settings['max_results'] ?? 6;
        
        // If settings aren't configured, show an error message
        if (empty($api_key) || empty($playlist_id)) {
            return '<div class="ytpg-error">Please configure the YouTube API key and Playlist ID in the plugin settings.</div>';
        }
        
        // Create a unique cache key based on playlist ID and number of videos
        // This is like a label for our stored data
        $cache_key = 'ytpg_videos_' . md5($playlist_id . $max_results);
        
        // Try to get cached videos from WordPress storage
        // This avoids hitting YouTube's API every time
        $videos = get_transient($cache_key);
        
        // If no cached data exists (or cache expired)
        if (false === $videos) {
            // Fetch fresh data from YouTube
            $videos = $this->fetch_playlist_videos($api_key, $playlist_id, $max_results);
            
            // If we got valid data (no errors), cache it for 1 hour
            if (!isset($videos['error'])) {
                set_transient($cache_key, $videos, HOUR_IN_SECONDS);
            }
        }
        
        // If there was an error fetching videos, show error message
        if (isset($videos['error'])) {
            return '<div class="ytpg-error">Error fetching videos: ' . esc_html($videos['error']) . '</div>';
        }
        
        // If playlist is empty, show a message
        if (empty($videos['items'])) {
            return '<div class="ytpg-error">No videos found in this playlist.</div>';
        }
        
        // Start output buffering - we'll collect HTML and return it all at once
        ob_start();
        
        // Get the gallery style setting (default to 'simple' if not set)
        $gallery_style = $settings['gallery_style'] ?? 'simple';

        // Determine the template path based on the style
            $template_path = '';
            if ($gallery_style === 'simple') {
                $template_path = plugin_dir_path(__FILE__) . '/templates/layout-1.php';
            } elseif ($gallery_style === 'modern') {
                $template_path = plugin_dir_path(__FILE__) . '/templates/layout-2.php';
            } else {
                // Default fallback to 'simple' if invalid value
                $template_path = plugin_dir_path(__FILE__) . '/templates/layout-1.php';
            }

            // Check if the template file exists, then include it
            if (file_exists($template_path)) {
                // Start output buffering - we'll collect HTML and return it all at once
                include $template_path;

                // Get all the HTML we collected and return it
            } else {
                // Fallback error if template is missing
                return '<div class="ytpg-error">Template file not found for style: ' . esc_html($gallery_style) . '</div>';
            }

        
        // Get all the HTML we collected and return it
        return ob_get_clean();
    }
    //Block addition function the function is hooked in the construct function at start
    function zamkai_yt_register_block() {
        // Only register if build exists
        if ( file_exists( plugin_dir_path( __FILE__ ) . 'build/index.js' ) ) {
            register_block_type( __DIR__ . '/build' );
        }
    }
}
// CREATE AN INSTANCE OF OUR PLUGIN CLASS
// This actually starts the plugin running
new YouTube_Playlist_Grid();

