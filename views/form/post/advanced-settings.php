<?php 
/**
 * Template for displaying post compose form advanced settings
 *
 * Available variables
 *
 * @param $post             - object current post
 * @param $accounts         - non grouped array of accounts
 * @param $SocialFlow_Post  - reference to SocialFlow_Post class object
 * 
 *
 * @since 2.7.1
 */

global $socialflow;

$post             = $data['post'];
$SocialFlow_Post  = $data['SocialFlow_Post'];
$accounts         = $data['accounts'];

// Get saved settings
$advanced = get_post_meta( $post->ID, 'sf_advanced', true );
$methods  = array( 'publish', 'hold', 'optimize' );

$_must = esc_attr__( 'Must Send', 'socialflow' );
$_can  = esc_attr__( 'Can Send',  'socialflow' );

// array of enabled account ids
$send_to = get_post_meta( $post->ID, 'sf_send_accounts', true );

if ( '' === $send_to )
	$send_to = $socialflow->options->get( 'send', array() );

$publish_options  = SocialFlow_Admin_Settings_General::get_publish_options();
$optimize_periods = SocialFlow_Admin_Settings_General::get_optimize_periods();

$data_tz_offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

$dublicated = array( 'optimize', 'schedule' );

?>
<?php  // Render advenced settings ?>
<div id="js-advanced-settings" class="advanced-settings">
	<a id="sf-advanced-toggler" class="advanced-toggler" href="#"><?php esc_html_e( 'Advanced Settings', 'socialflow' ) ?></a>
	<!-- display none -->
	<div id="sf-advanced-content" class="advanced-settings-content" >
		<table width="100%"><tbody>

		<?php foreach ( $accounts as $user_id => $account ) : 

			$user_id = esc_attr( $user_id );

			// Extract acctount advanced variables
			$advanced_options = $SocialFlow_Post->get_user_advanced_options( $account, $advanced );		

		?>
			<tr valign="top" class="field socialflow-user-advanced">
				<td>
					<label class="account" for="sf_send_<?php echo esc_attr( $user_id ); ?>">
						<input class="js-sf-account-checkbox" name="socialflow[send][]" id="sf_send_<?php echo $user_id; ?>" type="checkbox" <?php checked( in_array( $user_id, $send_to ), true ) ?> value="<?php echo $user_id; ?>" /> 
						<?php echo esc_html( $socialflow->accounts->get_display_name( $account ) ); ?>
					</label>
				</td>
				<td>
					<div class="sf-advanced-items js-sf-advanced-items" data-max-count="5">
						<?php foreach ( $advanced_options as $key => $item ): 
							$data_name = "socialflow[$user_id]";
							$item_id   = "socialflow[$user_id][$key]";
						?>
							<div class="sf-advanced-item" data-name="<?php echo $data_name ?>">
								<select class="publish-option" id="sf_publish_option<?php echo $user_id; ?>" name="<?php echo $item_id ?>[publish_option]">
									<?php foreach ( $publish_options as $key => $title ): 
										$is_dublicate = absint( in_array( $key, $dublicated ) );
									?>
										<option value="<?php echo $key ?>" <?php selected( $item['publish_option'], $key ); ?> data-dublicate="<?php echo $is_dublicate; ?>"><?php echo $title; ?></option>
									<?php endforeach ?>
								</select>

								<span class="optimize">
									<span class="clickable must_send" data-toggle_html="<?php echo ( 0 == $item['must_send'] ) ? $_must : $_can; ?>"><?php echo ( 0 == $item['must_send'] ) ? $_can : $_must; ?></span>
									<input class="must_send" type="hidden" value="<?php echo esc_attr( $item['must_send'] ); ?>" name="<?php echo $item_id ?>[must_send]" />

									<select class="optimize-period" name="<?php echo $item_id ?>[optimize_period]">
										<?php foreach ( $optimize_periods as $key => $title ): ?>
											<option <?php selected( $item['optimize_period'], $key ); ?> value="<?php echo $key ?>" ><?php echo $title; ?></option>
										<?php endforeach ?>
									</select>

									<span class="optimize-range" <?php if ( $item['optimize_period'] != 'range' ) echo 'style="display:none;"' ?>>
										<?php esc_html_e( 'from', 'socialflow' ); ?>
										<input class="time datetimepicker" type="text" value="<?php echo esc_attr( $item['optimize_start_date'] ); ?>" name="<?php echo $item_id ?>[optimize_start_date]" data-tz-offset="<?php echo $data_tz_offset ?>" />
										<?php esc_html_e( 'to', 'socialflow' ); ?>
										<input class="time datetimepicker" type="text" value="<?php echo esc_attr( $item['optimize_end_date'] ); ?>" name="<?php echo $item_id ?>[optimize_end_date]" data-tz-offset="<?php echo $data_tz_offset; ?>" />
									</span>
								</span>

								<span class="schedule">
									<?php esc_html_e( 'Send at', 'socialflow' ); ?>
									<input class="time datetimepicker" type="text" value="<?php echo esc_attr( $item['scheduled_date'] ); ?>" name="<?php echo $item_id ?>[scheduled_date]" data-tz-offset="<?php echo $data_tz_offset; ?>" />
								</span>
								<div class="sf-advanced-item-actions">									
									<a href="#" class="remove-button sf-remove-button"><?php esc_html_e( 'Remove', 'socialflow' ) ?></a>
								</div>
							</div>
						<?php endforeach ?>
						<div class="sf-advanced-items-actions">
							<button class="button dublicate-button sf-dublicate-button"><?php esc_html_e( 'Duplicate', 'socialflow' ) ?></button>
						</div>
					</div>
				</td>

			</tr><!-- .field -->
		<?php endforeach; ?>
		</tbody></table>
	</div><!-- #sf-advanced-content -->
</div><!-- .advanced-settings -->