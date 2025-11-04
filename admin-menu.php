<?php
// SECURITY CHECK - Ensure this file is only included from the plugin class
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>YouTube Playlist Grid Settings</h1>
    
    <?php 
    if (get_transient('ytpg_cache_cleared_notice')) {
            echo '<div class="notice notice-success is-dismissible"><p>Cache cleared successfully! The playlist will refresh on the next page load.</p></div>';
            // Delete the transient so message only shows once
            delete_transient('ytpg_cache_cleared_notice');
        }
        ?>
    
    <!-- MAIN SETTINGS FORM -->
    <!-- This form saves to WordPress using options.php -->
    <form method="post" action="options.php">
        <?php 
        // Add hidden security fields that WordPress requires
        settings_fields('ytpg_settings_group'); 
        ?>
        
        <table class="form-table">
            <!-- API KEY FIELD -->
            <tr>
                <th scope="row">
                    <label for="api_key">YouTube API Key</label>
                </th>
                <td>
                    <!-- Text input for the YouTube API key -->
                    <input type="text" id="api_key" name="<?php echo $this->option_name; ?>[api_key]" 
                           value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" 
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
                    <input type="text" id="playlist_id" name="<?php echo $this->option_name; ?>[playlist_id]" 
                           value="<?php echo esc_attr($settings['playlist_id'] ?? ''); ?>" 
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
                    <input type="number" id="max_results" name="<?php echo $this->option_name; ?>[max_results]" 
                           value="<?php echo esc_attr($settings['max_results'] ?? 6); ?>" 
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
                <select id="gallery_style" name="<?php echo $this->option_name; ?>[gallery_style]">
                    <option value="simple" <?php selected($settings['gallery_style'] ?? 'simple', 'simple'); ?>>Simple Layout</option>
                    <option value="modern" <?php selected($settings['gallery_style'] ?? 'simple', 'modern'); ?>>Modern Layout</option>
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
                    <textarea id="custom_css" name="<?php echo $this->option_name; ?>[custom_css]" 
                              rows="10" class="large-text code"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                    <p class="description">Add your custom CSS styles here</p>
                </td>
            </tr>
        </table>
        
        <?php 
        // Display the "Save Changes" button
        submit_button(); 
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
        wp_nonce_field('ytpg_clear_cache_action', 'ytpg_clear_cache_nonce'); 
        ?>
        <input type="submit" name="ytpg_clear_cache" class="button button-secondary" value="Clear Cache & Refresh Playlist" />
    </form>
    
    <hr>
    
    <!-- USAGE INSTRUCTIONS -->
    <h2>Usage</h2>
    <p>Use the shortcode <code>[youtube_playlist_grid]</code> in any page or post to display your playlist grid.</p>
</div>