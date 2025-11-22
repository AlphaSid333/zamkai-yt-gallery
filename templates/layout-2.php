<?php
// SECURITY CHECK - Ensure this file is only included from the plugin class
//phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- GRID CONTAINER - Wraps all video cards -->
<div class="ytpg-container">
	<div class="ytpg-grid">
		
		<?php
		// LOOP through each video in the playlist
		foreach ( $videos['items'] as $item ) :

			// Extract video information from the API response
			$snippet     = $item['snippet'];
			$video_id    = $snippet['resourceId']['videoId'];
			$title       = $snippet['title'];
			$description = $snippet['description'];

			// Get the best quality thumbnail available
			// Try "high" quality first, fall back to "default" if not available
			$thumbnail = isset( $snippet['thumbnails']['high']['url'] )
				? $snippet['thumbnails']['high']['url']
				: ( isset( $snippet['thumbnails']['default']['url'] )
					? $snippet['thumbnails']['default']['url']
					: '' );

			// Build the YouTube watch URL
			$video_url = 'https://www.youtube.com/watch?v=' . $video_id;
			?>
		
		<!-- SINGLE VIDEO CARD -->
		<div class="ytpg-video-card">
			
			<!-- BACKGROUND IMAGE OVERLAY -->
			<div class="ytpg-card-background" 
				style="background-image: url('<?php echo esc_url( $thumbnail ); ?>');">
			</div>
			
			<!-- GRADIENT OVERLAY -->
			<div class="ytpg-overlay"></div>
			
			<!-- CONTENT SECTION (positioned over image) -->
			<div class="ytpg-content">
				
				<!-- Top section with play icon -->
				<div class="ytpg-play-icon">
					<svg viewBox="0 0 24 24" fill="currentColor">
						<path d="M8 5v14l11-7z"/>
					</svg>
				</div>
				
				<!-- Bottom section with text -->
				<div class="ytpg-info">
					<!-- Video Title -->
					<h3 class="ytpg-title"><?php echo esc_html( $title ); ?></h3>
					
					<!-- Video Description (only show if it exists) -->
					<?php if ( ! empty( $description ) ) : ?>
						<p class="ytpg-description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
					
					<!-- Watch Button -->
					<a href="<?php echo esc_url( $video_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="ytpg-watch-btn">
						<span>Watch Now</span>
						<svg viewBox="0 0 24 24" fill="currentColor">
							<path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
						</svg>
					</a>
				</div>
				
			</div>
			
		</div>
		
		<?php endforeach; ?>
		
	</div>
</div>

<?php
