<?php
/**
 * Template for displaying post compose form tabs
 *
 * Available variables
 *
 * @param $post             - object current post
 * @param $grouped_accounts - array of accounts grouped by type
 * @param $SocialFlow_Post  - reference to SocialFlow_Post class object
 * 
 *
 * @since 2.7.1
 */

$grouped_accounts = $data['grouped_accounts'];
$post             = $data['post'];
$SocialFlow_Post  = $data['SocialFlow_Post'];

?>
<ul class="compose-tabs" id="sf-compose-tabs">
	<?php foreach ( $grouped_accounts as $group => $group_accounts ) : ?>
		<li class="tabs <?php echo esc_attr( $group ); ?>-tab-item"><a href="#sf-compose-<?php echo esc_attr( $group ); ?>-panel"><?php echo SocialFlow_Accounts::get_type_title( $group ); ?></a></li>
	<?php endforeach; // accounts loop ?>
</ul>

<?php
// Loop through grouped accounts
foreach ( $grouped_accounts as $group => $group_accounts ) :
	$message = esc_html( apply_filters( 'sf_message', get_post_meta( $post->ID, "sf_message_{$group}", true ), $group, $post ) );
?>
	<div class="tabs-panel sf-tabs-panel <?php echo esc_attr( $group ); ?>-tab-panel" id="sf-compose-<?php echo esc_attr( $group ); ?>-panel">

		<textarea data-content-selector="#title" class="autofill widefat socialflow-message-<?php echo esc_attr( $group ); ?>" id="sf_message_<?php echo esc_attr( $group ); ?>" name="socialflow[message][<?php echo esc_attr( $group ); ?>]" cols="30" rows="5" placeholder="<?php esc_html_e('Message', 'socialflow') ?>" ><?php echo esc_html( $message ); ?></textarea>

		<?php if ( 'twitter' == $group ): ?>
			<div class="sf_message_postfix">
				<input type="text" id="sf_message_postfix_<?php echo esc_attr( $group ); ?>" name="socialflow[message_postfix][<?php echo esc_attr( $group ); ?>]" value="<?php echo esc_html( get_post_meta( $post->ID, "sf_message_postfix_{$group}", true ) ) ?>" placeholder="<?php esc_attr_e( 'Message postfix', 'socialflow' ) ?>">
			</div>
		<?php endif ?>

		<?php if ( 'google_plus' == $group ) : ?>
			<span class="sf-muted-text"><?php esc_html_e( '* Metadata title and description are not editable for G+', 'socialflow' ); ?></span>
		<?php endif; ?>

		<?php if ( in_array( $group, array( 'google_plus', 'facebook', 'linkedin' ) ) ) :

			if ( in_array( $group, array( 'facebook', 'linkedin' ) ) ) {
				$title       = get_post_meta( $post->ID, 'sf_title_'.$group, true );
				$description = get_post_meta( $post->ID, 'sf_description_'.$group, true );
			} else {
				$title = $post->post_title;
				$description = ( !empty( $post->post_excerpt ) ) ? $post->post_excerpt : $post->post_content;
				$description = wp_trim_words( strip_tags( apply_filters( 'the_content', $description ) ), 20, '...' );
			}

			$image = get_post_meta( $post->ID, 'sf_image_'.$group, true );

			if ( 'attachment' == $post->post_type ) {
				$is_custom_image = true;
				$media_image = $SocialFlow_Post->get_attachment_media( $post->ID );

				$custom_image = is_array( $media_image ) ? $media_image['medium_thumbnail_url'] : '';
				$custom_image_filename = is_array( $media_image ) ? $media_image['filename'] : '';
			} 
			else {
				$is_custom_image = absint( get_post_meta( $post->ID, 'sf_is_custom_image_'.$group, true ) );
				$custom_image = get_post_meta( $post->ID, 'sf_custom_image_'.$group, true );
				$custom_image_filename = get_post_meta( $post->ID, 'sf_custom_image_filename_'.$group, true );
			}

		?>
		<div class="sf-additional-fields">

			<div class="sf-attachments js-sf-attachments <?php if ( $is_custom_image ) echo 'sf-is-custom-attachment'; ?>">

				<div class="sf-attachments-slider">
					<div class="image-container sf-attachment-slider">
						<?php $SocialFlow_Post->post_attachments( $post->ID, $post->post_content ); ?>
					</div>

					<?php if ( 'linkedin' !== $group ) : ?>
						<button class="button button-attachment-switch-status js-toggle-custom-image"><?php esc_html_e( 'Select', 'socialflow' ); ?></button>
					<?php endif; ?>

					<span title="<?php esc_html_e( 'Previous', 'socialflow' ) ?>" class="prev icon sf-attachment-slider-prev"><?php esc_html_e( 'Previous', 'socialflow' ); ?></span>
					<span title="<?php esc_html_e( 'Next', 'socialflow' ) ?>" class="next icon sf-attachment-slider-next"><?php esc_html_e( 'Next', 'socialflow' ); ?></span>
					<span class="sf-update-attachments icon reload sf-update-attachments"><?php esc_html_e( 'Update attachments', 'socialflow' ); ?></span>
				</div>

				<div class="sf-attachments-custom">
					<div class="image-container">
						<?php if ( $custom_image ) : ?>
							<img src="<?php echo esc_url( $custom_image ); ?>" alt="">
						<?php endif; ?>
					</div>

					<?php if ( 'linkedin' !== $group ) : ?>
						<button class="button button-attachment-switch-status js-toggle-custom-image"><?php esc_html_e( 'Cancel', 'socialflow' ); ?></button>
					<?php endif; ?>

					<button class="button js-attachments-set-custom-image sf-custom-attachment-button"><?php esc_html_e( 'Select', 'socialflow' ); ?> <span class="additional-hint"><?php esc_html_e( 'image', 'socialflow' ); ?></span></button>
				</div>

				<input class="sf-current-attachment" type="hidden" name="socialflow[image][<?php echo esc_attr( $group ); ?>]" value="<?php echo esc_attr( $image ); ?>" />

				<input class="sf-is-custom-image" type="hidden" name="socialflow[is_custom_image][<?php echo esc_attr( $group ); ?>]" value="<?php echo esc_attr( $is_custom_image ); ?>" />
				<input class="sf-custom-image" type="hidden" name="socialflow[custom_image][<?php echo esc_attr( $group ); ?>]" value="<?php echo esc_attr( $custom_image ); ?>" />
				<input class="sf-custom-image-filename" type="hidden" name="socialflow[custom_image_filename][<?php echo esc_attr( $group ); ?>]" value="<?php echo esc_attr( $custom_image_filename ); ?>" />
			</div>

			<?php if ( in_array( $group, array( 'facebook', 'linkedin' ) ) ) : ?>
			<input data-content-selector="#title" class="autofill sf-title widefat socialflow-title-<?php echo esc_attr( $group ); ?>" type="text" name="socialflow[title][<?php echo esc_attr( $group ); ?>]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_html_e( 'Title', 'socialflow' ); ?>" />
			<textarea data-content-selector="#content" class="autofill sf-description widefat socialflow-description-<?php echo esc_attr( $group ); ?>" name="socialflow[description][<?php echo esc_attr( $group ); ?>]" cols="30" rows="5" placeholder="<?php esc_html_e( 'Description', 'socialflow' ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
			<?php else : ?>
			<div class="sf-muted-text" data-content-selector="#title" class="autofill"><?php echo esc_attr( $title ); ?></div> <hr>
			<div class="sf-muted-text" data-content-selector="#content" class="autofill" ><small><?php echo esc_html( $description ); ?></small></div>
			<?php endif; ?>
		</div>
		<?php endif; // fecebook group ?>
	</div>
<?php endforeach; // accounts loop ?>