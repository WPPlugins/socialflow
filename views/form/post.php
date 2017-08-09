<?php
/**
 * Template for displaying post compose form
 *
 * Available variables
 *
 * @param $post             - object current post
 * @param $compose_now      - int ( 0 | 1 ) compose now status
 * @param $grouped_accounts - array of accounts grouped by type
 * @param $accounts         - non grouped array of accounts
 * @param $SocialFlow_Post  - reference to SocialFlow_Post class object
 * 
 *
 * @since 2.0
 */
global $socialflow, $pagenow;

$grouped_accounts = $data['grouped_accounts'];
$post             = $data['post'];
$SocialFlow_Post  = $data['SocialFlow_Post'];
$compose_now      = $data['compose_now'];
$accounts         = $data['accounts'];

$autocomplete = 'post-new.php' == $pagenow ? $socialflow->options->get( 'global_disable_autocomplete', 0 ) : get_post_meta( $post->ID, 'sf_disable_autcomplete', true );

// Nonce is stored as postmeta to prevent multiple message submission
$nonce = wp_create_nonce( SF_ABSPATH );
update_post_meta( $post->ID, 'socialflow_nonce', $nonce );

?>
<input type="hidden" name="socialflow_nonce" value="<?php echo $nonce; ?>" />

<input type="hidden" name="sf_current_post_id" id="sf_current_post_id" value="<?php echo esc_attr( $post->ID ); ?>">

<p class="sf_compose">
	<input id="sf_compose" type="checkbox" value="1" name="socialflow[compose_now]" <?php checked( $compose_now, 1 ); ?> />
	<label for="sf_compose">
		<?php if ( 'publish' != $post->post_status ) : ?>
			<?php esc_html_e( 'Send to SocialFlow when the post is published', 'socialflow' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'Send to SocialFlow when the post is updated', 'socialflow' ); ?>
		<?php endif; ?>
	</label>
</p>

<?php if ( 'attachment' !== $post->post_type ) : ?>
	<p class="sf-media-toggle-container"> 
		<input id="sf_media_compose" class="sf_media_compose" type="checkbox" value="1" name="socialflow[compose_media]" <?php checked( get_post_meta( $post->ID, 'sf_compose_media', true ), 1 ); ?> />
		<label for="sf_media_compose"><?php esc_html_e( 'Image Post', 'socialflow' ) ?></label>
	</p>
<?php endif; ?>

<p class="sf-media-toggle-container"> 
	<input id="sf_disable_autcomplete" class="sf_disable_autcomplete" type="checkbox" value="1" name="socialflow[disable_autcomplete]" <?php checked( absint( $autocomplete ), 1 ); ?> />
	<label for="sf_disable_autcomplete"><?php esc_html_e( 'Disable autocomplete', 'socialflow' ) ?></label>
</p>

<p class="sf-autofill-button-container"><button id="sf_autofill" class="button" data-confirm="<?php esc_html_e( 'Are you sure you would like to update social text?', 'socialflow' ) ?>"><?php esc_html_e( 'Update Social Text', 'socialflow' ); ?></button></p>

<input id="sf-post-id" type="hidden" value="<?php echo esc_attr( $post->ID ); ?>" />

<?php $socialflow->render_view( 'form/post/compose-tabs', compact( 'grouped_accounts', 'post', 'SocialFlow_Post' ) ); ?>

<div class="tabs-panel sf-media-attachment">
	<?php $media = get_post_meta( $post->ID, 'sf_media', true ); ?>

	<div class="sf-image-container">
		<?php if ( $media ) : ?>
		<img src="<?php echo esc_url( $media['medium_thumbnail_url'] ); ?>" alt="">
		<?php endif; ?>
	</div>

	<?php if ( 'attachment' !== $post->post_type ) : ?>
		<button class="button js-attachments-set-media sf-custom-attachment-button"><?php esc_html_e( 'Select', 'socialflow' ); ?> <span class="additional-hint"><?php esc_html_e( 'image', 'socialflow' ); ?></span></button>
	<?php endif; ?>
</div>

<?php $socialflow->render_view( 'form/post/advanced-settings', compact( 'post', 'SocialFlow_Post', 'accounts' ) ); ?>