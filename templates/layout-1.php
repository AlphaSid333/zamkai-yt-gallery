<?php
// SECURITY CHECK - Ensure this file is only included from the plugin class
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if (!defined('ABSPATH')) {
    exit;
}
?>     
        <!-- GRID CONTAINER - Wraps all video cards -->
        <div class="ytpg-container">
            <div class="ytpg-grid">
                
                <?php 
                // LOOP through each video in the playlist
                foreach ($videos['items'] as $item): 
                    // Extract video information from the API response
                    $snippet = $item['snippet'];
                    $video_id = $snippet['resourceId']['videoId'];
                    $title = $snippet['title'];
                    $description = $snippet['description'];
                    
                    // Get the best quality thumbnail available
                    // Try "high" quality first, fall back to "default" if not available
                    $thumbnail = isset($snippet['thumbnails']['high']['url']) ? $snippet['thumbnails']['high']['url'] : (isset($snippet['thumbnails']['default']['url']) ? $snippet['thumbnails']['default']['url']: '');
                    
                    // Build the YouTube watch URL
                    $video_url = 'https://www.youtube.com/watch?v=' . $video_id;
                ?>
                
                    <!-- SINGLE VIDEO CARD -->
                    <div class="ytpg-video-card">
                        
                        <!-- THUMBNAIL SECTION -->
                        <div class="ytpg-thumbnail">
                            <img src="<?php echo esc_url($thumbnail); ?>" 
                                 alt="<?php echo esc_attr($title); ?>" 
                                 loading="lazy">
                        </div>
                        
                        <!-- CONTENT SECTION (title, description, button) -->
                        <div class="ytpg-content">
                            <!-- Video Title -->
                            <h3 class="ytpg-title"><?php echo esc_html($title); ?></h3>
                            
                            <!-- Video Description (only show if it exists) -->
                            <?php if (!empty($description)): ?>
                                <p class="ytpg-description"><?php echo esc_html($description); ?></p>
                            <?php endif; ?>
                            
                            <!-- Watch Button (opens in new tab) -->
                            <a href="<?php echo esc_url($video_url); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="ytpg-play-button">
                                Watch Video
                            </a>
                        </div>
                        
                    </div>
                    
                <?php endforeach; ?>
                
            </div>
        </div>
        
        <?php