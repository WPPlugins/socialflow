<?php
/**
 * SocialFlow Accounts class
 *
 * @package SocialFlow
 */
class SocialFlow_Accounts {
	
	/**
	 * Active accounts ids
	 *
	 * @since 2.0
	 * @access protected
	 * @var array
	 */
	protected $active;

	/**
	 * Account ids from last query
	 *
	 * @since 2.0
	 * @access protected
	 * @var array
	 */
	protected $last;

	/**
	 * Default order for available account types
	 * 
	 * @var array
	 */
	protected static $type_order = array( 'twitter', 'facebook', 'google_plus', 'linkedin' );

	/**
	 * PHP5 Constructor
	 */
	function __construct(){}

	/**
	 * Retrieve array of accounts
	 *
	 * @since 2.1
	 * @access public
	 *
	 * @param array filter query
	 * @return mixed ( array | bool ) Return array of accounts or false if none matched
	 * can also return single account if client_account_id is passed instead of query array
	 */
	public function get( $query = array(), $post_type = 'post' ) {
		global $socialflow;

		$accounts = $socialflow->options->get( 'accounts', array() );

		// For attachments return accounts with specific types only
		if ( 'attachment' == $post_type ) {
			foreach ( $accounts as $key => $account ) {
				if ( !in_array( $account['account_type'], array( 'twitter', 'facebook_page', 'google_plus_page' ) ) )
					unset( $accounts[ $key ] );
			}
		}

		// return all acconts if empty query passed
		if ( empty( $query ) )
			return $accounts;

		// return single account if $query is int - client_account_id
		if ( is_int( $query ) ) {
			if ( array_key_exists( $query, $accounts ) ) {
				return $accounts[ $query ];
			} else {
				return false;
			}
		}

		// Check if array of account ids was passed
		if ( isset( $query[0] ) && is_int( $query[0] ) ) {
			if ( $intersect = array_intersect( array_keys( $accounts ), array_values( $query ) ) ) {
				foreach ( $accounts as $key => $value ) {
					if ( !in_array( $key, $intersect ) )
						unset( $accounts[ $key ] );
				}
				return $accounts;
			}
			return false;
		}

		// loop through query attributes and unset not matching accounts
		foreach ( $accounts as $key => $account ) {
			// check current account to match all qeuries
			foreach ( $query as $check ) {

				// To-Do add different comparison operators

				// break loop if query doesn't match
				if ( 
					!isset( $check[ 'key' ] ) OR !is_string( $check[ 'key' ] ) OR
					!isset( $account[ $check[ 'key' ] ] ) OR 
					( !is_array( $check[ 'value' ] ) AND !is_array( $account[ $check[ 'key' ] ] ) AND $account[ $check[ 'key' ] ] != $check[ 'value' ] )  OR
					( is_array( $check[ 'value' ] ) AND !is_array( $account[ $check[ 'key' ] ] ) AND !in_array( $account[ $check[ 'key' ] ], $check[ 'value' ] ) )
				) {
					unset( $accounts[ $key ] );
					break;
				}
			}
		}

		if ( empty( $accounts ) )
			$accounts = false;

		return $accounts;
	}

	/**
	 * Get active accounts
	 *
	 * @since 2.1
	 * @access public
	 *
	 * @param string return accounts or only ids
	 * @return mixed ( array | bool ) array of accounts is returned if active attribute isset
	 */
	public function get_active( $fields = 'all' ) {

		if ( ! $this->isset_active() )
			return false;

		if ( 'ids' == $fields )
			return $this->active;

		return $this->get( array(
			'client_account_id' => $this->active
		));
	}

	/**
	 * Set active accounts
	 *
	 * @since 2.1
	 * @access public
	 * @deprecated not used in plugin anymore
	 *
	 * @param array of query arguments passed to get() method
	 * @return bool accounts were found and active ids were set
	 */
	public function set_active( $query = array() ) {

		// Get accounts by query
		$accounts = $this->get( $query );

		// $active atribute needs only ids
		if ( false != $accounts ) {
			$accounts = array_keys( $accounts );
		}

		return $accounts;
	}

	/**
	 * Check if active attribute isset
	 *
	 * @since 2.1
	 * 
	 * @return bool
	 */
	protected function isset_active() {
		return isset( $this->active );
	}

	/**
	 * Retrieve single account display name
	 * 
	 * @since 2.1
	 * @access public
	 *
	 * @param mixed ( array | int ) single account or account_id
	 * @param bool add type prefix or not
	 * @return string account display name
	 */
	public function get_display_name( $account = array(), $add_prefix = true ) {
		$name = $prefix = '';

		// Get account if account id was passed
		$account = is_int( $account ) ? self::get( $account ) : $account;

		$type = self::get_global_type( $account );

		if ( empty( $type ) )
			return __( 'Missing account', 'socialflow' );

		// Retrieve account name depending on account type
		switch ( $type ) {
			case 'facebook':
				$name = $account['name'];
				$prefix = __('Facebook Wall ', 'socialflow');
				break;
			case 'twitter':
				$name = $account['screen_name'];
				$prefix = __('Twitter', 'socialflow') . ' @';
				break;
			case 'google_plus':
				$name = $account['name'];
				$prefix = __('Google+ ', 'socialflow');
				break;
			case 'linkedin':
				$name = $account['name'];
				$prefix = __('LinkedIn ', 'socialflow');
				break;
			default:
				$name = $account['name'];
				break;
		}

		return $add_prefix ? $prefix . $name : $name;
	}

	/**
	 * Group accounts by type
	 *
	 * @since 2.0
	 * @access public
	 *
	 * @param array accounts to group
	 * @return array grouped accounts
	 */
	public function group_by( $type = 'global_type', $accounts = array(), $order = false ) {
		if ( empty( $accounts ) )
			return $accounts;

		$new = array();
		foreach ($accounts as $key => $account) {
			// Define
			$type = self::get_global_type($account);

			if (isset($new[$type]))
				$new[$type][] = $account;
			else
				$new[$type] = array($account);
		}
		$accounts = $new;

		if ( false == $order )
			return $accounts;

		$types = array_intersect( array_flip( self::$type_order ), array_keys( $accounts ) );

		return array_replace( $types, $accounts );
	}


	/**
	 * User Friendly type title
	 * @param  string $type Account type
	 * @return string       Account type title
	 */
	public static function get_type_title( $type ) {
		switch ( $type ) {
			case 'google_plus':
				return 'Google+';
				break;

			case 'linkedin':
				return 'LinkedIn';
				break;

			default:
				return ucfirst( $type );
				break;
		}
	}

	/**
	 * Get global account type
	 * @param mixed account
	 * @return string account type
	 *
	 * @since 1.0
	 * @access public
	 */
	public function get_global_type( $account ) {
		$type = is_array( $account ) ? $account['account_type'] : '';

		if ( strpos( $type, 'twitter' ) !== false )
			$type = 'twitter';
		elseif ( strpos( $type, 'facebook' ) !== false )
			$type = 'facebook';
		elseif ( strpos( $type, 'google_plus' ) !== false )
			$type = 'google_plus';
		elseif ( strpos( $type, 'linked_in' ) !== false )
			$type = 'linkedin';

		return $type;
	}

	/**
	 * Send message to accounts
	 *
	 * @since 2.1
	 * @access public
	 *
	 * @param array of additional data for each account, array keys are client_account_id's
	 * @return mixed ( bool | object ) true on success and WP_Error on failure
	 */
	public function compose( $data = array() ) {
		global $socialflow;

		// Validate data
		$data = $this->valid_compose_data( $data );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$data = self::structure_data( $data );

		$api = $socialflow->get_api();

		// We have valid api object and valid data,
		// but we still need to collect statuses from socialflow
		$statuses = $errors = $success = array();

		// Loop through data and send message to appropriate account
		foreach ( self::get( array_keys( $data ) ) as $account_id => $account ) {
			foreach ( $data[ $account_id ] as $options ) {
				$statuses[] = $api->add_message( 
					$account['service_user_id'], 
					$account['account_type'], 
					$options, 
					$account_id 
				);
			}
		}

		$errors = $success = array();

		// Find all errors in statuses
		foreach ( $statuses as $status ) {
			// Collect error statuses
			if ( is_wp_error( $status ) ) {
				$errors[] = $status;
			}
			// Collect success statuses
			else {
				$success[] = $status;
			}
		}

		if ( !empty( $errors ) ) {
			return $socialflow->join_errors( $errors );
		}

		return $success;
	}

	protected static function structure_data( $data )
	{
		$output = array();

		foreach ( $data as $id => $args ) {
			$publish = $args['publish_data'];

			unset( $args['publish_data'] );

			foreach ( $publish as $key => $options ) {
				$output[ $id ][ $key ] = array_merge( $options, $args );
			}
		}

		return $output;
	}

	/**
	 * Validate passed data
	 * check for required variables and add some additional data
	 *
	 * @since 2.1
	 * @access private
	 *
	 * @param array
	 * @return mixed valid data or WP_Error object if some errors were found
	 */
	protected static function valid_compose_data( $data ) {
		global $socialflow;
		$errors = array();

		// In fact passed data can't be empty but we will still check in this too
		if ( empty( $data ) ) {
			return new WP_Error( 'empty_data', __( 'Empty send data was passed', 'socialflow' ) );
		}

		$valid_data = array();

		foreach ( $data as $account_id => $values ) {
			$account      = self::get( $account_id );
			$account_type = self::get_global_type( $account );

			// check for required fields
			if ( empty( $values['publish_data'] ) OR !is_array( $values['publish_data'] ) ) {
				$errors[] = new WP_Error( 'empty_message:', __( '<b>Error:</b> Publish options are required for: <i>%s</i>.' ), array( $account_id ) );
			}

			// check for required fields
			if ( empty( $values['message'] ) && $account_type !== 'google_plus' ) {
				$errors[] = new WP_Error( 'empty_message:', __( '<b>Error:</b> Message field is required for: <i>%s</i>.' ), array( $account_id ) );
			}

			// Reset total message length
			$total_len = 0;

			// Add message to valid data array
			$valid_data[ $account_id ]['message'] = self::valid_text( $values['message'], 'message', $total_len );

			// Add publish data
			foreach ( (array) $values['publish_data'] as $key => $options ) {
				$options = self::valid_piblish_options( $options, $account_id );

				if ( is_wp_error( $options ) ) {
					$errors[] = $options;
					continue;
				}

				$valid_data[ $account_id ]['publish_data'][ $key ] = $options;
			}

			// check for special fields
			if ( isset( $values['content_attributes'] ) ) {

				if ( isset( $values['content_attributes']['name'] ) ) {
					$values['content_attributes']['name'] = self::valid_text( $values['content_attributes']['name'], 'name', $total_len );
				}
				if ( isset( $values['content_attributes']['description'] ) ) {
					$values['content_attributes']['description'] = self::valid_text( $values['content_attributes']['description'], 'description', $total_len );
				}

				$valid_data[ $account_id ]['content_attributes'] = json_encode( $values['content_attributes'] );
			}

			// Custom image
			if ( isset( $values['media_thumbnail_url'] ) ) {
				$valid_data[ $account_id ]['media_thumbnail_url'] = $values['media_thumbnail_url'];

				if ( isset( $values['media_filename'] ) ) {
					$valid_data[ $account_id ]['media_filename'] = $values['media_filename'];
				}
			}

			// add additianal fields
			$valid_data[ $account_id ]['created_by']    = get_user_option( 'user_email', get_current_user_id() );
			$valid_data[ $account_id ]['shorten_links'] = absint( $socialflow->options->get( 'shorten_links' ) );
		}

		// Return error instead of valid data
		if ( !empty( $errors ) ) {
			return $socialflow->join_errors( $errors );
		}

		return $valid_data;
	}

	protected static function valid_piblish_options( $values, $account_id )
	{
		$errors = array();

		$valid_data['publish_option'] = $values['publish_option'];

		// Validate some passed data
		switch ( $values['publish_option'] ) {
			case 'schedule':
				if ( empty( $values['scheduled_date'] ) )
					return new WP_Error( 'empty_scheduled_date:', __( '<b>Error:</b> Scheduled date is required for schedule publish option for: <i>%s.</i>' ), array( $account_id ) );

				$valid_data['scheduled_date'] = self::get_valid_date( $values['scheduled_date'] );

				if ( empty( $valid_data['scheduled_date'] ) )
					return new WP_Error( 'incorrect_scheduled_date:', __( '<b>Error:</b> Post could not be sent to SocialFlow: set relevant schedule time for: <i>%s.</i>' ), array( $account_id ) );

				break;

			case 'optimize':

				// Set optimize start/end date depending on optimize_period
				if ( $values['optimize_period'] == 'range' ) {
					$messages = array(
						'start' => array(
							'empty'     => __( '<b>Error:</b> Optimize start date is required for optimize publish option for: <i>%s.</i>' ),
							'incorrect' => __( '<b>Error:</b> Post could not be sent to SocialFlow: set relevant optimize start time for: <i>%s.</i>' )
						),
						'end'   => array(
							'empty'     => __( '<b>Error:</b> Optimize end date is required for optimize publish option for: <i>%s.</i>' ),
							'incorrect' => __( '<b>Error:</b> Post could not be sent to SocialFlow: set relevant optimize end time for: <i>%s.</i>' )
						),
					);

					foreach ( $messages as $key => $msgs ) {
						$key = "optimize_{$key}_date";

						if ( empty( $values[ $key ] ) )
							return new WP_Error( "empty_{$key}:", $msgs['empty'], array( $account_id ) );

						$valid_data[ $key ] = self::get_valid_date( $values[ $key ] );

						if ( empty( $valid_data[ $key ] ) )
							return new WP_Error( "incorrect_{$key}:", $msgs['incorrect'], array( $account_id ) );
					}

					
					// set strtotime(), because comparasion of dates, for ex.
					// 11-04-2016 04:38 am (start) AND 11-04-2016 03:38 pm (end)
					// is incorrect, 
					// not counted am & pm, counted only date numerals
					if ( strtotime( $values['optimize_end_date'] ) < strtotime( $values['optimize_start_date'] ) ) 
						return new WP_Error( 'invalid_optimize_period:', __( '<b>Error:</b> Invalid optimize period for <i>%s.</i>' ), array( $account_id ) );

				} elseif ( $values['optimize_period'] != 'anytime' ) {
					$current_time = current_time( 'timestamp' );
					$valid_data['optimize_start_date'] = gmdate( 'Y-m-d H:i:s O', strtotime( '+1 minute', $current_time ) );
					$valid_data['optimize_end_date']   = gmdate( 'Y-m-d H:i:s O', strtotime( "+{$values['optimize_period']}", $current_time ) );
				}

				$valid_data['must_send'] = isset( $values['must_send'] ) ? absint( $values['must_send'] ) : 0;
				
				break;
		}

		return $valid_data;
	}
	/**
	 * Validate date
	 * @param  string $date date
	 * @return null|formatted date
	 */
	protected static function get_valid_date( $date )
	{
		if ( !$date )
			return;

		$date = strtotime( $date );

		// if set date_default_timezone, so all date/time functions return value on this location, not UTC
		// @since v 2.7.3
		$timestamp = 'UTC' == date_default_timezone_get() ? current_time( 'timestamp' ) : time();

		if ( $timestamp > $date )
			return;

		if ( 'UTC' == date_default_timezone_get() )
			$date = $date - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

		return date( 'Y-m-d H:i:s O', $date );
	}

	/**
	 * Validate text before sending to SocialFlow
	 * @param  string $text input text
	 * @return string       validated text
	 */
	protected static function valid_text( $text, $name, &$total_len = 0 ) {
		global $socialflow;

		// Decode html entities
		$text = wp_specialchars_decode( $text, ENT_QUOTES );

		switch ( $name ) {
			case 'message':
				$text = $socialflow->trim_chars( $text, 4200 );
				break;
			case 'name' :
				$text = $socialflow->trim_chars( $text, 500 );
				break;
			case 'description' :
				$text = $socialflow->trim_chars( $text, 5000 - $total_len );
				break;
			// no default case
		}

		$total_len += strlen( $text );

		return $text;
	}
}