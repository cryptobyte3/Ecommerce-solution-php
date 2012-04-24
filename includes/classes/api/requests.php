<?php
/**
 * Grey Suit Retail - Requests Class
 *
 * @requires Studio98 Framework
 * @requires Database Connection file
 *
 * This handles all API Requests
 * @version 1.0.0
 */
class Requests {
	/**
	 * Constant paths to include files
	 */
	const DEBUG = false;

	/**
	 * Set of messages used throughout the script for easy access
	 * @var array $messages
	 */
	private $messages = array(
		'error' => 'An unknown error has occured. This has been reported to the Database Administrator. Please try again later.',
		'failed-add-order-item' => 'Failed to add the order item. Please verify you have the correct parameters.',
		'failed-authentication' => 'Authentication failed. Please verify you have the correct Authorization Key.',
		'failed-create-order' => 'Create Order failed. Please verify you have sent the correct parameters.',
		'failed-create-user' => 'Create User failed. Please verify you have sent the correct parameters.',
		'failed-create-website' => 'Create Website failed. Please verify you have sent the correct parameters.',
        'failed-update-social-media' => 'Update Social media failed. Please verify you have sent the correct parameters.',
		'failed-update-user' => 'Update User failed. Please verify you have sent the correct parameters.',
		'failed-set-arb-subscription' => 'Update User ARB Subscription failed. Please verify you have sent the correct parameters.',
		'no-authentication-key' => 'Authentication failed. No Authorization Key was sent.',
		'ssl-required' => 'You must make the call to the secured version of our website.',
		'success-add-order-item' => 'Add Order Item succeeded!',
		'success-create-order' => 'Create Order succeeded!',
		'success-create-user' => 'Create User succeeded!',
		'success-create-website' => 'Create Website succeeded! The checklist and checklist items have also been created.',
        'success-update-social-media' => 'Update Social Media succeeded!',
		'success-update-user' => 'Update User succeeded!',
		'success-set-arb-subscription' => 'Update User ARB Subscription succeeded!'
	);
	
	/**
	 * Set of valid methods
	 * @var array $messages
	 */
	private $methods = array(
		'add_order_item',
        'craigslist_error',
		'create_order',
		'create_user',
		'create_website',
        'update-social-media',
		'update_user',
		'update_user_arb_subscription',
	);
	
	/**
	 * Pieces of data accrued throughout processing
	 */
	private $company_id = 0;
	private $method = '';
	private $error_message = '';
	private $response = array();
	
	/**
	 * Statuses of different stages of processing
	 */
	private $statuses = array( 
		'init' => false,
		'auth' => false,
		'method_called' => false
	);
	private $logged = false;
	private $error = false;
	
	/**
	 * Construct class will initiate and run everything
	 *
	 * This class simply needs to be initiated for it run to the data on $_POST variables
	 */
	public function __construct() {
		// Do we need to debug
		if( self::DEBUG )
			error_reporting( E_ALL );
		
		// Load everything that needs to be loaded
		$this->init();
		
		// Authenticate & load company id
		$this->authenticate();
		
		// Parse method
		$this->parse();
	}
	
	/**
	 * This authenticates the request and loads the company data
	 *
	 * @access private
	 */
	private function authenticate() {
		// They didn't send an authorization key
		if( !isset( $_POST['auth_key'] ) ) {
			$this->add_response( array( 'success' => false, 'message' => 'no-authentication-key' ) );
			
			$this->error = true;
			$this->error_message = 'There was no authentication key';
			exit;
		}

        $auth_key = $this->db->escape( $_POST['auth_key'] );
		$this->company_id = $this->db->get_var( "SELECT `company_id` FROM `api_keys` WHERE `status` = 1 AND `key` = '$auth_key'" );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( 'Failed to retrieve company id', __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-authentication' ) );
			exit;
		}
		
		// If failed to grab any company id
		if( !$this->company_id ) {
			$this->add_response( array( 'success' => false, 'message' => 'failed-authentication' ) );
			
			$this->error = true;
			$this->error_message = 'There was no company to match API key';
			exit;
		}
		
		$this->statuses['auth'] = true;
	}
	
	/**
	 * This parses the request and calls the correct functions
	 *
	 * @access private
	 */
	private function parse() {
		if( in_array( $_POST['method'], $this->methods ) ) {
			$this->method = $_POST['method'];
			$this->statuses['method_called'] = true;
			
			$class_name = 'IRR';
			call_user_func( array( 'IRR', $_POST['method'] ) );
		} else {
			$this->add_response( array( 'success' => false, 'message' => 'The method, "' . $_POST['method'] . '", is not a valid method.' ) );
			
			$this->error = true;
			$this->error_message = 'The method, "' . $_POST['method'] . '", is not a valid method.';
			exit;
		}
	}
	
	/*************************/
	/* START: IR API Methods */
	/*************************/
	
	/**
	 * Add Order Item
	 *
	 * @param int $order_id
	 * @param string $item The item name
	 * @param int $quantity
	 * @param float $amount the setup cost
	 * @param float $monthly the monthly cost
	 * @return bool
	 */
	private function add_order_item() {
		// Gets parameters and errors out if something is missing
		$order_item = $this->get_parameters( 'order_id', 'item', 'quantity', 'amount', 'monthly' );
		
		// Execute the command
		$this->db->insert( 'order_items', $order_item, 'isidd' );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( 'Failed to add order item', __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-add-order-item' ) );
			exit;
		}
		
		$this->add_response( array( 'success' => true, 'message' => 'success-add-order-item' ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called.' . "\nOrder ID: " . $order_item['order_id'], true );
	}
	
	/**
	 * Create Order
	 *
	 * @param int $user_id
	 * @param float $setup
	 * @param float $monthly
	 * @return int|bool
	 */
	private function create_order() {
		// Gets parameters and errors out if something is missing
		$order = $this->get_parameters( 'user_id', 'setup', 'monthly' );
		
		// Correct the field names
		$order['total_amount'] = $order['setup'];
		$order['total_monthly'] = $order['monthly'];
		unset( $order['setup'], $order['monthly'] );

		// Add extra fields
		$order['type'] = 'new-website';
		$order['status'] = 0;
		$order['date_created'] = dt::date('Y-m-d H:i:s');
		
		$this->db->insert( 'orders', $order, 'iddsis' );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( 'Failed to create order', __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-create-order' ) );
			exit;
		}
		
		$order_id = $this->db->insert_id;
		
		$this->add_response( array( 'success' => true, 'message' => 'success-create-order', 'order_id' => $order_id ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called.' . "\nUser ID: " . $order['user_id'] . "\nOrder ID:" . $order_id, true );
	}
	
	/**
	 * Create User
	 *
	 * @param string $email
	 * @param string $password
	 * @param string $contact_name
	 * @param string $store_name
	 * @param string $work_phone
	 * @param string $cell_phone
	 * @param string $billing_first_name
	 * @param string $billing_last_name
	 * @param string $billing_address1
	 * @param string $billing_city
	 * @param string $billing_state
	 * @param string $billing_zip
	 * @return $user_id
	 */
	private function create_user() {
		// Gets parameters and errors out if something is missing
		$personal_information = $this->get_parameters( 'email', 'password', 'contact_name', 'store_name', 'work_phone', 'cell_phone', 'billing_first_name', 'billing_last_name', 'billing_address1', 'billing_city', 'billing_state', 'billing_zip' );
		$personal_information['password'] = md5( $personal_information['password'] );

        $email = $this->db->escape( $personal_information['email'] );

        $user = $this->db->get_row( "SELECT `user_id`, `status` FROM `users` WHERE `email` = $email", ARRAY_A );

        // If there is a user already, use that
        if ( $user ) {
            if ( 0 == $user['status'] ) {
                $this->db->update( 'users', array( 'status' => 1 ), array( 'user_id' => $user['user_id'] ), 'i', 'i' );

                // If there was a MySQL error
                if( $this->db->errno() ) {
                    $this->err( 'Failed to create user', __LINE__, __METHOD__ );
                    $this->add_response( array( 'success' => false, 'message' => 'failed-create-user' ) );
                    exit;
                }
            }

            $user_id = $user['user_id'];
        } else {
            // Insert the user
            $this->db->insert( 'users', array_merge( array( 'company_id' => $this->company_id ), $personal_information, array( 'date_created' => dt::date('Y-m-d H:i:s') ) ), 'isssssssssssss' );

            // If there was a MySQL error
            if( $this->db->errno() ) {
                $this->err( 'Failed to create user', __LINE__, __METHOD__ );
                $this->add_response( array( 'success' => false, 'message' => 'failed-create-user' ) );
                exit;
            }

            $user_id = $this->db->insert_id;
        }

		$this->add_response( array( 'success' => true, 'message' => 'success-create-user', 'user_id' => $user_id ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called.' . "\nUser ID: $user_id", true );
	}
	
	/**
	 * Create Website
	 * 
	 * @param int $user_id
	 * @param string $domain
	 * @param string $title
	 * @param string $type
	 * @param bool $blog
	 * @param bool $email_marketing
	 * @param bool $shopping_cart
	 * @param bool $seo
	 * @param bool $room_planner
	 * @param bool $domain_registration
	 * @param bool $additional_email_addresses
	 * @return int|bool
	 */
	private function create_website() {
		// Gets parameters and errors out if something is missing
		$website = $this->get_parameters( 'user_id', 'domain', 'title', 'plan_name', 'plan_description', 'type', 'blog', 'email_marketing', 'shopping_cart', 'seo', 'room_planner', 'domain_registration', 'additional_email_addresses', 'products' );
		$website['status'] = 1;
        $website['date_created'] = date_time::date('Y-m-d H:i:s');
		
		// Insert website
		$this->db->insert( 'websites', $website, 'isssssiiiiiiisiis' );

		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( "Failed to create website.\n\nUser ID: " . $website['user_id'], __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-create-website' ) );
			exit;
		}
		
		// Get the website ID
		$website_id = $this->db->insert_id;
		
		// Now we have to insert checklists
		$this->db->insert( 'checklists', array( 'website_id' => $website_id, 'type' => 'Website Setup', 'date_created' => dt::date('Y-m-d H:i:s') ), 'iss' );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( "Failed to insert checklist.\n\nWebsite ID: $website_id", __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-create-website' ) );
			exit;
		}
		
		// Get checklist ID
		$checklist_id = (int) $this->db->insert_id;

        // Insert all the checklist items
        $this->db->query( "INSERT INTO `checklist_website_items` ( `checklist_id`, `checklist_item_id` ) SELECT $checklist_id, `checklist_item_id` FROM `checklist_items` WHERE `status` = 1" );

		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( "Failed to insert checklist.\n\Checklist ID: $checklist_id", __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-create-website' ) );
			exit;
		}

        // If they had social media, add all the plugins, they get update this later
        if ( '1' == $website['social_media'] ) {
            $this->db->insert( 'website_settings', array( 'website_id' => $website_id, 'key' => 'social-media-add-ons', 'value' => 'a:10:{i:0;s:13:"email-sign-up";i:1;s:9:"fan-offer";i:2;s:11:"sweepstakes";i:3;s:14:"share-and-save";i:4;s:13:"facebook-site";i:5;s:10:"contact-us";i:6;s:8:"about-us";i:7;s:8:"products";i:8;s:10:"current-ad";i:9;s:7:"posting";}' ), 'iss' );

            // If there was a MySQL error
            if( $this->db->errno() ) {
                $this->err( "Failed to create website settings.\n\Website ID: $website_id", __LINE__, __METHOD__ );
                $this->add_response( array( 'success' => false, 'message' => 'failed-create-website' ) );
                exit;
            }
        }

		// Everything was successful
		$this->add_response( array( 'success' => true, 'message' => 'success-create-website', 'website_id' => $website_id ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called.' . "\nUser ID: " . $website['user_id'] . "\nWebsite ID: $website_id", true );
	}

    /**
	 * Update Social Media
	 *
	 * @param int $website_id
	 * @param array $social_media_add_ons
	 */
	private function update_social_media() {
		// Gets parameters and errors out if something is missing
		extract( $this->get_parameters( 'website_id', 'website_social_media_add_ons' ) );

        // Make sure we can edit this website
        $this->verify_website( $website_id );

        if ( !is_array( $website_social_media_add_ons ) ) {
            $this->add_response( array( 'success' => false, 'message' => 'failed-update-social-media' ) );
            exit;
        }

        // Master list of social media add ons
        $social_media_add_ons = array(
            'email-sign-up'
            , 'fan-offer'
            , 'sweepstakes'
            , 'share-and-save'
            , 'facebook-site'
            , 'contact-us'
            , 'about-us'
            , 'products'
            , 'current-ad'
            , 'posting'
        );

        // Make sure we only have valid arguments
        foreach ( $website_social_media_add_ons as &$value ) {
            if ( !in_array( $value, $social_media_add_ons ) )
                unset( $value );
        }

        // Check again to make sure it is an array
        if ( !is_array( $website_social_media_add_ons ) ) {
            $this->add_response( array( 'success' => false, 'message' => 'failed-update-social-media' ) );
            exit;
        }

        // Type Juggling
        $website_id = (int) $website_id;

        // Make the variable
        $db_website_social_media_add_ons = $this->db->escape( serialize( $website_social_media_add_ons ) );

        // Insert/update website settings
        $this->db->query( "INSERT INTO `website_settings` ( `website_id`, `key`, `value` ) VALUES ( $website_id, 'social-media-add-ons', '$db_website_social_media_add_ons' ) ON DUPLICATE KEY UPDATE `value` = '$db_website_social_media_add_ons'" );

        // If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( "Failed to update website settings.\n\Website ID: $website_id", __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-update-social-media' ) );
			exit;
		}

		$this->add_response( array( 'success' => true, 'message' => 'success-update-social-media' ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called. Website ID: ' . $website_id, true );
	}

	/**
	 * Update User
	 *
	 * @param string $email
	 * @param string $password
	 * @param string $contact_name
	 * @param string $store_name
	 * @param string $work_phone
	 * @param string $cell_phone
	 * @param string $billing_first_name
	 * @param string $billing_last_name
	 * @param string $billing_address1
	 * @param string $billing_city
	 * @param string $billing_state
	 * @param string $billing_zip
	 * @param int $user_id
	 */
	private function update_user() {
		// Gets parameters and errors out if something is missing
		$personal_information = $this->get_parameters( 'email', 'password', 'contact_name', 'store_name', 'work_phone', 'cell_phone', 'billing_first_name', 'billing_last_name', 'billing_address1', 'billing_city', 'billing_state', 'billing_zip', 'user_id' );
		
		// Get the user_id, but we don't want it in the update data
		$user_id = $personal_information['user_id'];
		unset( $personal_information['user_id'] );
		
		// Make sure he exists, if not, create user
		if( !$this->user_exists( $user_id ) ) {
			$this->create_user();
			return;
		}
		
		$personal_information['password'] = md5( $personal_information['password'] );
		
		// Update the user
		$this->db->update( 'users', array_merge( $personal_information, array( 'date_created' => dt::date('Y-m-d H:i:s') ) ), array( 'user_id' => $user_id, 'company_id' => $this->company_id ), 'ssssssssssss', 'ii' );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( 'Failed to update user', __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-update-user' ) );
			exit;
		}
		
		$this->add_response( array( 'success' => true, 'message' => 'success-update-user' ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called. User ID: ' . $user_id, true );
	}
	
	/**
	 * Set ARB Subscription
	 *
	 * ARB is Automatic Recurring Billing (part of Authorize.net)
	 *
	 * @param int $arb_subscription_id
	 * @param int $website_id
	 * @return bool
	 */
	private function set_arb_subscription() {
		// Gets parameters and errors out if something is missing
		extract( $this->get_parameters( 'arb_subscription_id', 'website_id' ) );

        // Protection
		$website_id = (int) $website_id;
        $arb_subscription_id = $this->db->escape( $arb_subscription_id );

		$this->db->query( "INSERT INTO `website_settings` VALUES $website_id, 'arb-subscription-id', '$arb_subscription_id' ON DUPLICATE KEY UPDATE `value` = '$arb_subscription_id' " );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( "Failed to set ARB subscription id.\n\nWebsite ID: $website_id\nARB Subscription ID:$arb_subscription_id", __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-set-arb-subscription' ) );
			exit;
		}

		$this->add_response( array( 'success' => true, 'message' => 'success-set-arb-subscription' ) );
		$this->log( 'method', 'The method "' . $this->method . '" has been successfully called. Website ID: ' . $website_id, true );
	}
	
	/***********************/
	/* END: IR API Methods */
	/***********************/

    /**
     * Check to make sure a website belongs to the company
     *
     * @param int $website_id
\     */
    private function verify_website( $website_id ) {
        // Type Juggling
        $website_id = (int) $website_id;
        $company_id = (int) $this->company_id;

        // See if we can grab the website ID
        $verify_website_id = $this->db->get_var( "SELECT a.`website_id` FROM `websites` AS a LEFT JOIN `users` AS b ON ( a.`user_id` = b.`user_id`) LEFT JOIN `companies` AS c ON ( b.`company_id` = c.`company_id` ) WHERE a.`website_id` = $website_id AND c.`company_id` = $company_id" );

        // If there was a MySQL error
        if( $this->db->errno() ) {
			$this->err( "Could not verify Website ID: $website_id to Company ID: $company_id", __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-website-verification' ) );
			exit;
		}

        // Verify that it exists
        if ( !$verify_website_id ) {
            $this->add_response( array( 'success' => false, 'message' => 'failed-website-verification' ) );
            exit;
        }
    }

	/**
	 * Checks to see if a user exists
	 *
	 * @param int $user_id
	 * @return bool
	 */
	private function user_exists( $user_id ) {
        // Type Juggling
        $user_id = (int) $user_id;
        $company_id = (int) $this->company_id;

		$email = $this->db->get_var( "SELECT `email` FROM `users` WHERE `user_id` = $user_id AND `company_id` = $company_id" );
		
		// If there was a MySQL error
		if( $this->db->errno() ) {
			$this->err( 'Failed to check if user exists', __LINE__, __METHOD__ );
			$this->add_response( array( 'success' => false, 'message' => 'failed-update-user' ) );
			exit;
		}
		
		return ( $email ) ? true : false;
	}


	/**
	 * This loads all the variables that we need
	 *
	 * @access private
	 */
	private function init() {
		// Make sure it's ssl
		if( !security::is_ssl() ) {
			$this->add_response( array( 'success' => false, 'message' => 'ssl-required' ) );
			
			$this->error = true;
			$this->error_message = 'The request was made without SSL';
			exit;
		}
		
		$this->statuses['init'] = true;
	}
	
	/**
	 * Add a response to be sent
	 *
	 * Adds data to the response that will be sent back to the client
	 *
	 * @param string|array $key this can contain the key OR an array of key => value pairs
	 * @param string (optional) $value of the $key. Only optional if $key is an array
	 */
	private function add_response( $key, $value = '' ) {
		if( empty( $value ) && !is_array( $key ) ) {
			$this->add_response( array( 'success' => false, 'message' => 'error' ) );
			
			$this->err( "Tried to add a response without a valid key and value\n\nKey: \n----------\n" . fn::info( $key, false ) . "\n----------\n" . $value, __LINE__, __METHOD__ );
		}
		
		// Set the response
		if( is_array( $key ) ) {
			foreach( $key as $k => $v ) {
				// Makes sure there isn't a premade message
				$this->response[$k] = ( is_string( $v ) || is_int( $v ) && array_key_exists( $v, $this->messages ) ) ? $this->messages[$v] : $v;
			}
		} else {
			// Makes sure there isn't a premade message
			$this->response[$key] = ( !is_array( $v ) && array_key_exists( $v, $this->messages ) ) ? $this->messages[$v] : $v;
		}
	}
	
	/**
	 * Gets parameters from the post variable and returns and associative array with those values
	 *
	 * @param mixed $args the args that contain the parameters to get
	 * @return array $parameters
	 */
	private function get_parameters() {
		$args = func_get_args();
		
		// Make sure the arguments are correct
		if( !is_array( $args ) ) {
			$this->add_response( array( 'success' => false, 'message' => 'error' ) );
			$this->err( "Call to get_parameters with incorrect arguments\n\nArguments:\n" . fn::info( $args ), __LINE__, __METHOD__ );
			exit;
		}
		
		// Go through each argument
		foreach( $args as $a ) {
			// Make sure the argument is set
			if( !isset( $_POST[$a] ) ) {
				$message = 'Required parameter "' . $a . '" was not set for the method "' . $this->method . '".';
				$this->add_response( array( 'success' => false, 'message' => $message ) );
				
				$this->error = true;
				$this->error_message = $message;
				exit;
			}
			
			$parameters[$a] = $_POST[$a];
		}
		
		// Return arguments
		return $parameters;
	}
	
	/**
	 * Adds an log entry to the API log table
	 *
	 * @param string $type the type of log entry
	 * @param string $message message to be put into the log
 	 * @param bool $success whether the call was successful
	 * @param bool $set_logged (optional) whether to set the logged variable as true
	 */
	private function log( $type, $message, $success, $set_logged = true ) { 
		// Set before hand so that a loop isn't caught in the destructor
		if( $set_logged )
			$this->logged = true;
		
		// If it fails to insert, send an email with the information
		$this->db->insert( 'api_log', array( 'company_id' => $this->company_id, 'type' => $type, 'method' => $this->method, 'message' => $message, 'success' => $success, 'date_created' => dt::date('Y-m-d H:i:s') ), 'isssis' );

        if( $this->db->errno() ) {
			$this->err( "Failed to add entry to log\n\nType: $type\nMessage:\n$message", __LINE__, __METHOD__ );
			
			// Let the client know that something broke
			$this->add_response( array( 'success' => false, 'message' => 'error' ) );
		}
	}
	
	/**
	 * Destructor which creates the log and any information that we should know about it
	 */
	public function __destruct() {
		// Make sure we haven't already logged something
		if( !$this->logged )
		if( $this->error ) {
			foreach( $this->statuses as $status => $value ) {
				// Set the message status name
				$message_status = ucwords( str_replace( '_', ' ', $status ) );
				
				$message .= ( $this->statuses[$status] ) ? "$message_status: True" : "$message_status: False";
				$message .= "\n";
			}
			
			$this->log( 'error', 'Error: ' . $this->error_message . "\n\n" . rtrim( $message, "\n" ), false );
		} else {
			$this->log( 'method', 'The method "' . $this->method . '" has been successfully called.', true );
		}
		
		// Respond in JSON
		echo json_encode( $this->response );
	}

	/**
	 * Report an error
	 *
	 * Make the parent error function a little less complicated
	 *
	 * @param string $message the error message
	 * @param int $line (optional) the line number
	 * @param string $method (optional) the class method that is being called
     * @return bool
	 */
	private function _err( $message, $line = 0, $method = '' ) {
		return $this->error( $message, $line, __FILE__, dirname(__FILE__), '', __CLASS__, $method );
	}
}