<?php if ( !defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' ); 

/**
 * Replace current site url to entered
 *
 * @package SocialFlow
 * @since 2.7.4
 */
class SF_Module_Replace_Site_Url
{
	protected $slug = 'socialflow';

	protected $field_key = 'replace_site_url';

	protected $replacement = array(
		'message',
		'content_attributes',
	);

	protected $replace_url = '';

	protected $current_url = '';

	function __construct()
	{
		global $socialflow;

		add_action( 'init', array( $this, 'set_urls' ) );

		add_action( 'toplevel_page_socialflow', array( $this, 'set_error' ), 1 );

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 ); 

		add_filter( 'sf_oauth_post_request_params', array( $this, 'replace_oauth_post_request_params' ) );
	}

	function set_error()
	{
		if ( $this->is_valid_url( $this->replace_url ) ) 
			return;

		$this->add_settings_error( 'error_not_valid_site_url' );
	}

	/**
	 * Set used url
	 *
	 * @since 2.7.4
	 */
	function set_urls()
	{
		global $socialflow;

		$this->replace_url = $socialflow->options->get( $this->field_key );
		$this->current_url = get_home_url( get_current_blog_id() );
	}

	/**
	 * Check is empty or valid url
	 *
	 * @since 2.7.4
	 */
	protected function is_valid_url( $url )
	{
		if ( empty( $url ) )
			return true;

		return !!filter_var( $url, FILTER_VALIDATE_URL );
	}

	/**
	 * Replace url on oauth post request
	 *
	 * @since 2.7.4
	 */
	function replace_oauth_post_request_params( array $params )
	{
		if ( empty( $this->replace_url ) )
			return $params;

		if ( ! $this->is_valid_url( $this->replace_url ) )
			return $params;

		$params['message'] = $this->replace_site_url( $params['message'] );

		if ( ! isset( $params['content_attributes'] ) )
			return $params;

		$atts = json_decode( $params['content_attributes'], true );

		foreach ( $atts as $key => $value ) {
			$atts[ $key ] = $this->replace_site_url( $value );
		}

		$params['content_attributes'] = wp_json_encode( $atts );

		return $params;
	}

	/**
	 * Replace url in text 
	 *
	 * @since 2.7.4
	 */
	protected function replace_site_url( $text )
	{
		global $socialflow;

		$new = $this->replace_url;

		if ( empty( $new ) )
			return $text;

		$current = $this->current_url;

		return str_replace( $current, $new, $text );
	}

	/**
	 * This is callback for admin_menu action fired in construct
	 *
	 * @since 2.7.4
	 */
	function admin_menu() 
	{
		global $socialflow;

		add_settings_field( 
			$this->field_key,
			esc_attr__( 'Replace Site Url in request:', 'socialflow' ),
			array( $this, 'setting_replace_site_url' ),
			$this->slug,
			'general_settings_section'
		);
	}

	/**
	 * Setting field html data
	 *
	 * @since 2.7.4
	 */
	function setting_replace_site_url()
	{
		global $socialflow;
		?>
			<input id="sf_<?php echo $this->field_key ?>" type="text" value="<?php echo $socialflow->options->get( $this->field_key ) ?>" name="socialflow[<?php echo $this->field_key ?>]" size="30" />
			<p class="description"><?php _e( 'Current url is' ) ?> <code><?php echo $this->current_url ?></code></p>
		<?php
	}

	/**
	 * Settings error
	 *
	 * @since 2.7.4
	 */
	protected function add_settings_error( $key )
	{
		switch ( $key ) {
			case 'error_not_valid_site_url':
				$message = __( 'Replacement Site Url is not valid.', 'socialflow' );
				break;
		}

		if ( isset( $message ) )
			add_settings_error( $this->slug, $key, $message, 'error' );
	}
}
new SF_Module_Replace_Site_Url;