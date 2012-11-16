<?php
/**
 * Handles all the craiglist functions
 *
 * @package Grey Suit Retail
 * @since 1.0
 */
class Craigslist extends Base_Class {
	/**
	 * Construct initializes data
	 */
	public function __construct() {
		// Need to load the parent constructor
		if ( !parent::__construct() )
			return false;
	}
	
	/**
	 * Get Craigslist Template
	 *
	 * Gets a specific craigslist template
	 *
	 * @param int $website_id
	 * @return array
	 */
	public function get( $craigslist_template_id ) {
		$craigslist = $this->db->get_row( 'SELECT a.`craigslist_template_id`, a.`title`, a.`description`, a.`category_id`, b.`name` AS `category_name` FROM `craigslist_templates` AS a LEFT JOIN `categories` AS b ON (a.`category_id` = b.`category_id`) WHERE a.`craigslist_template_id` = ' . (int) $craigslist_template_id, ARRAY_A );
	
		// Handle any error
		if ( mysql_errno() ) {
			$this->_err( 'Failed to get craigslist template.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $craigslist;
	}
	
	
	/**
	 * Creates a new Craigslist ad
	 * @param int $category_id
	 * @param string $title
	 * @param string $description
	 * @return int craigslist_template_id
	 */
	public function create( $category_id, $title, $description ) { 
		$this->db->insert( 'craigslist_templates',
						  array( 'category_id' => $category_id, 'title' => $title, 'description' => $description, 'publish_visibility' => 'visible', 'date_created' => dt::date('Y-m-d H:i:s') ),
						  'issss' );

		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to create craigslist ad template.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $this->db->insert_id;
	}
	
	/**
	 * Updates a Craigslist ad template
	 *
	 * @param int $craigslist_template_id
	 * @param int $category_id
	 * @param string $title
	 * @param string $description
	 * @return int craigslist_template_id
	 */
	public function update( $craigslist_template_id, $category_id, $title, $description ) {
		
		$this->db->update( 'craigslist_templates', 
						  array( 'category_id' => $category_id, 'title' => $title, 'description' => $description, 'publish_visibility' => 'visible' ),
						  array( 'craigslist_template_id' => $craigslist_template_id ),
						  'isss', 'i' );
		
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to update craigslist template.', __LINE__, __METHOD__ );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get all information of the craigslist templates
	 *
	 * @param string $where
	 * @param string $order_by
	 * @param string $limit
	 * @return array
	 */
	public function list_craigslist( $where, $order_by, $limit ) {
		$where .= '' . ( ( isset( $where ) ) ? $where . " AND a.`publish_visibility` = 'visible' " : " WHERE a.`publish_visibility` = 'visible'" );
								 
		// Get the templates
		$craigslist_templates = $this->db->get_results( "SELECT a.`craigslist_template_id`, a.`title`, a.`description`, b.`name` AS `category_name`, a.`category_id`, a.`date_created`
														FROM `craigslist_templates` as a INNER JOIN `categories` as b ON ( a.`category_id` = b.`category_id` )
														$where ORDER BY $order_by LIMIT $limit", ARRAY_A );
		
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get craigslist templates.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $craigslist_templates;
	}
	
	/**
	 * Sets a craigslist template as inactive
	 *
	 * @param int $craigslist_template_id
	 * @return bool
	 */
	public function delete( $craigslist_template_id ) {
		$this->db->update( 'craigslist_templates', array( 'publish_visibility' => 'deleted' ), array( 'craigslist_template_id' => $craigslist_template_id), 's', 'i' );

		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to delete craigslist template.', __LINE__, __METHOD__ );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Count all the craigslist templates
	 *
	 * @param string $where
	 * @return array

	 */
	public function count_craigslist( $where ) {		
		if ( isset( $where ) && $where ) {
			$where .= " AND a.`publish_visibility` = 'visible' ";
		} else {
			$where = " WHERE a.`publish_visibility` = 'visible' ";
		}
		
		// Get the craigslist template count
		$craigslist_count = count( $this->db->get_results( "SELECT COUNT( a.`craigslist_template_id` ) FROM `craigslist_templates` AS a LEFT JOIN `categories` AS b ON ( a.`category_id` = b.`category_id` ) {$where} GROUP BY a.`craigslist_template_id`", ARRAY_A ) );
		
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to count craigslist templates.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $craigslist_count;
    }

	/**
	 * Gets the data for an autocomplete request
	 *
	 * @param string $query
	 * @param string $field
	 * @return bool
	 */
	public function autocomplete( $query, $field ) {
		$sql = "SELECT DISTINCT( $field ) FROM `craigslist_templates` AS a LEFT JOIN `categories` AS b ON ( a.`category_id` = b.`category_id` ) WHERE $field LIKE '%$query%' AND a.`publish_visibility` = 'visible' ORDER BY $field";
		
		// Get results
		$results = $this->db->get_results( $sql, ARRAY_A );
		
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get autocomplete entries.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $results;
	}

	/**
	 * Gets random data for creating a preview
	 * 
	 * @param int $category_id
     * @param bool $bottom
	 * @return array $results
	 */
	public function get_preview_data( $category_id, $bottom ) {
	/*	[Product Name]
		[Store Name]
		[Category]
		[Brand]
		[Product Description]
		[Product Specs]
		[Photo]
		[Attributes]
		[SKU]					*/
		
		$results = $this->db->get_row( "SELECT 
									  a.`product_id`, 
									  d.`category_id`,
									  a.`name` AS `product_name`,
									  d.`name` AS `category`,
									  c.`name` AS `brand`,
									  a.`description` AS `product_description`,
									  a.`product_specifications` AS `product_specs`,
									  a.`sku`
									FROM `products` AS a 
									LEFT JOIN `product_categories` AS b ON (a.`product_id` = b.`product_id`) 
									LEFT JOIN `brands` AS c ON (a.`brand_id` = c.`brand_id` )
									LEFT JOIN `categories` AS d ON ( d.`category_id` = b.`category_id`)
									WHERE b.`category_id` = " . (int) $category_id . " AND ( a.`product_id` > " . (int) $bottom . " ) LIMIT 1", ARRAY_A );
		
		$attributes = $this->db->get_results( "SELECT
											 b.`attribute_item_name`,
											 c.`name` AS `attribute_name`
											 FROM `attribute_item_relations` AS a
											 LEFT JOIN `attribute_items` AS b ON (b.`attribute_item_id` = a.`attribute_item_id`)
											 LEFT JOIN `attributes` AS c ON (c.`attribute_id` = b.`attribute_id`)
											 WHERE a.`product_id` = " . (int) $results['product_id'] . " ORDER BY b.`sequence`", ARRAY_A );
		
		$photos = $this->db->get_results( "SELECT a.`image`, c.`name` AS `industry`, a.`sequence`, a.`product_id`
											 FROM `product_images` AS a 
											 LEFT JOIN `products` AS b ON (b.`product_id` = a.`product_id`)
											 LEFT JOIN `industries` AS c ON (b.`industry_id` = c.`industry_id`)
											 WHERE a.`product_id` = " . (int) $results['product_id'] . " ORDER BY a.`sequence`", ARRAY_A );
		
		$results['photos'] = $photos;
		$results['attributes'] = $attributes;
		
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get preview data.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $results;
	}

    /***** ACCOUNTS *****/

    /**
     * Get Craigslist Account
     *
     * @param int $website_id
     * @return int
     */
    public function get_account( $website_id ) {
        // Type Juggling
        $website_id = (int) $website_id;

        $account = $this->db->get_row( "SELECT a.`website_id`, a.`title`, IF( b.`value` IS NULL, '', b.`value` ) AS craigslist_customer_id, IF( c.`value` IS NULL, '', c.`value` ) AS plan FROM `websites` AS a LEFT JOIN `website_settings` AS b ON ( a.`website_id` = b.`website_id` AND b.`key` = 'craigslist-customer-id' ) LEFT JOIN `website_settings` AS c ON ( b.`website_id` = c.`website_id` AND c.`key` = 'craigslist-plan' ) WHERE a.`website_id` = $website_id AND b.`website_id` IS NOT NULL", ARRAY_A );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get craigslist account.', __LINE__, __METHOD__ );
			return false;
		}

        return $account;
    }

    /**
     * Get accounts that are marked as craigslist but are not linked through the API
     *
     * @return array
     */
    public function get_unlinked_accounts() {
        global $user;

        // Add restriction
        $where = ( $user['role'] < 8 ) ? ' AND u.`user_id` = ' . (int) $user['company_id'] : '';

        $accounts = $this->db->get_results( "SELECT a.`website_id`, a.`title` FROM `websites` AS a LEFT JOIN `website_settings` AS b ON ( a.`website_id` = b.`website_id` AND b.`key` = 'craigslist-customer-id' ) LEFT JOIN `users` AS u ON ( a.`user_id` = u.`user_id` ) WHERE a.`status` = 1 AND a.`craigslist` = 1 AND b.`website_id` IS NULL $where", ARRAY_A );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get unlinked craigslist accounts.', __LINE__, __METHOD__ );
			return false;
		}

        return $accounts;
    }

    /**
	 * List Craigslist Accounts
	 *
	 * @param string $where
	 * @param string $order_by
	 * @param string $limit
	 * @return array
	 */
	public function list_craigslist_accounts( $where, $order_by, $limit ) {
        global $user;

        // Make sure they can only see what they're supposed to
        if ( $user['role'] < 8 )
    		$where .= ' AND f.`company_id` = ' . (int) $user['company_id'];
		
		// Get the accounts
		$accounts = $this->db->get_results( "SELECT a.`website_id`, a.`title`, IF( c.`value` IS NULL, '', c.`value` ) AS plan, GROUP_CONCAT( CONCAT( IF( '' <> e.`area`, CONCAT( e.`area`, ' - ', e.`city` ), e.`city` ), ', ', e.`state` ) SEPARATOR '<br />' ) AS markets FROM `websites` AS a LEFT JOIN `website_settings` AS b ON ( a.`website_id` = b.`website_id` AND b.`key` = 'craigslist-customer-id' ) LEFT JOIN `website_settings` AS c ON ( b.`website_id` = c.`website_id` AND c.`key` = 'craigslist-plan' ) LEFT JOIN `craigslist_market_links` AS d ON ( b.`website_id` = d.`website_id` ) LEFT JOIN `craigslist_markets` AS e ON ( d.`craigslist_market_id` = e.`craigslist_market_id` ) LEFT JOIN `users` AS f ON ( a.`user_id` = f.`user_id` ) WHERE a.`status` = 1 AND b.`website_id` IS NOT NULL AND ( e.`status` IS NULL OR e.`status` = 1 ) $where GROUP BY a.`website_id` ORDER BY $order_by LIMIT $limit", ARRAY_A );

		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to list craigslist accounts.', __LINE__, __METHOD__ );
			return false;
		}

		return $accounts;
	}

    /**
	 * Count the craigslist accounts
	 *
	 * @param string $where
	 * @return array
	 */
	public function count_craigslist_accounts( $where ) {
        global $user;

        // Make sure they can only see what they're supposed to
        if ( $user['role'] < 8 )
    		$where .= ' AND f.`company_id` = ' . (int) $user['company_id'];

		// Get the craigslist account count
		$count = $this->db->get_var( "SELECT COUNT( DISTINCT a.`website_id` ) FROM `websites` AS a LEFT JOIN `website_settings` AS b ON ( a.`website_id` = b.`website_id` AND b.`key` = 'craigslist-customer-id' ) LEFT JOIN `website_settings` AS c ON ( b.`website_id` = c.`website_id` AND c.`key` = 'craigslist-plan' ) LEFT JOIN `craigslist_market_links` AS d ON ( b.`website_id` = d.`website_id` ) LEFT JOIN `craigslist_markets` AS e ON ( d.`craigslist_market_id` = e.`craigslist_market_id` ) LEFT JOIN `users` AS f ON ( a.`user_id` = f.`user_id` ) WHERE a.`status` = 1 AND b.`website_id` IS NOT NULL AND ( e.`status` IS NULL OR e.`status` = 1 ) $where" );
        
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to count craigslist accounts.', __LINE__, __METHOD__ );
			return false;
		}

		return $count;
	}

    /***** MARKETS *****/

    /**
     * Get Craigslist Market
     *
     * @param int $craigslist_market_id
     * @return array
     */
    public function get_market( $craigslist_market_id ) {
        // Type Juggling
        $craigslist_market_id = (int) $craigslist_market_id;

        $market = $this->db->get_row( "SELECT `craigslist_market_id`, `cl_market_id`, `parent_market_id`, `state`, `city`, `area`, CONCAT( `city`, ', ', IF( '' <> `area`, CONCAT( `state`, ' - ', `area` ), `state` ) ) AS market, `submarket` FROM `craigslist_markets` WHERE `craigslist_market_id` = $craigslist_market_id AND `status` = 1", ARRAY_A );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get market.', __LINE__, __METHOD__ );
			return false;
		}

        return $market;
    }

    /**
     * Get Craigslist Market Links
     *
     * @param int $website_id
     * @return array
     */
    public function get_market_links( $website_id ) {
        // Type Juggling
        $website_id = (int) $website_id;

        $market_links = $this->db->get_results( "SELECT a.`craigslist_market_id`, CONCAT( b.`city`, ', ', IF( '' <> b.`area`, CONCAT( b.`state`, ' - ', b.`area` ), b.`state` ) ) AS market, a.`market_id`, a.`cl_category_id`, b.`cl_market_id` FROM `craigslist_market_links` AS a LEFT JOIN `craigslist_markets` AS b ON ( a.`craigslist_market_id` = b.`craigslist_market_id` ) WHERE a.`website_id` = $website_id AND b.`status` = 1", ARRAY_A );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get market.', __LINE__, __METHOD__ );
			return false;
		}
		
        return ( $market_links ) ? $market_links : array();
    }
    
    /**
     * Get CL_Category_ID's
     *
     * @param int $website_id
     * @param int $cl_market_id
     * @return array
     */
    public function get_cl_category_ids( $website_id, $cl_market_id ) {
        // Type Juggling
        $website_id = (int) $website_id;
        $cl_market_id = (int) $cl_market_id;

        $cl_category_ids = $this->db->get_col( "SELECT a.`cl_category_id` FROM `craigslist_market_links` AS a LEFT JOIN `craigslist_markets` AS b ON ( a.`craigslist_market_id` = b.`craigslist_market_id` ) WHERE a.`website_id` = $website_id AND b.`cl_market_id` = $cl_market_id" );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( "Failed to get cl_category_id's.", __LINE__, __METHOD__ );
			return false;
		}
		
        return $cl_category_ids;
    }

    /**
     * Get All Craigslist Market Links
     *
     * @return array
     */
    public function get_all_market_links() {
        $market_links = $this->db->get_results( "SELECT a.`craigslist_market_id`, a.`market_id` FROM `craigslist_market_links` AS a LEFT JOIN `craigslist_markets` AS b ON ( a.`craigslist_market_id` = b.`craigslist_market_id` ) WHERE b.`status` = 1", ARRAY_A );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get market.', __LINE__, __METHOD__ );
			return false;
		}

        return ( $market_links ) ? ar::assign_key( $market_links, 'market_id', true ) : array();
    }

    /**
     * Get Craigslist Markets
     *
     * @return array
     */
    public function get_markets() {
        $markets = $this->db->get_results( "SELECT `craigslist_market_id`, `cl_market_id`, `parent_market_id`, CONCAT( `city`, ', ', IF( '' <> `area`, CONCAT( `state`, ' - ', `area` ), `state` ) ) AS market, `submarket` FROM `craigslist_markets` WHERE `status` = 1 ORDER BY market ASC", ARRAY_A );

        // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get markets.', __LINE__, __METHOD__ );
			return false;
		}

        return $markets;
    }

    /**
     * Link Craigslist Markets to an account
     *
     * @param int $website_id
     * @param int $craigslist_market_id
     * @param int $market_id
     * @param int $cl_category_id
     * @return bool
     */
    public function link_market( $website_id, $craigslist_market_id, $market_id, $cl_category_id ) {
       $this->db->insert( 'craigslist_market_links', compact( 'website_id', 'craigslist_market_id', 'market_id', 'cl_category_id' ), 'iiii' );

         // Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to add market link.', __LINE__, __METHOD__ );
			return false;
		}

        return true;
    }

    /***** TAGS ****/

    /**
     * Add Tags
     *
     * @param array $tags
     * @return bool
     */
    public function add_tags( $tags ) {
        $values = $product_skus = $craigslist_tags = array();

        if ( is_array( $tags ) || is_object( $tags ) )
        foreach ( $tags as $object_id => $tag ) {
            if ( 'item' == $tag->type ) {
                $product_skus[] = $this->db->escape( $tag->name );
                $craigslist_tags[$tag->id] = $tag->name;
            } else {
                $values[] = '( ' . (int) $tag->id . ", " . (int) $object_id . ", 'category' )";
            }
        }

        // Add at up to 500 at a time
        $value_chunks = array_chunk( $values, 500 );

        foreach ( $value_chunks as $vc ) {
            $this->db->query( "INSERT INTO `craigslist_tags` ( `craigslist_tag_id`, `object_id`, `type` ) VALUES " . implode( ',', $vc ) . " ON DUPLICATE KEY UPDATE `type` = VALUES(`type`)" );
			
            // Handle any error
            if ( $this->db->errno() ) {
                $this->_err( 'Failed to add craigslist tags.', __LINE__, __METHOD__ );
                return false;
            }
        }

        // Now Product SKUs
        if ( is_array( $product_skus ) ) {
            $product_ids = $this->db->get_results( "SELECT `product_id`, `sku` FROM `products` WHERE `publish_visibility` = 'public' AND `sku` IN ( '" . implode( "', '", $product_skus ) . "' ) GROUP BY `product_id`", ARRAY_A );

             // Handle any error
            if ( $this->db->errno() ) {
                $this->_err( 'Failed to get product ids.', __LINE__, __METHOD__ );
                return false;
            }

            $product_ids = ar::assign_key( $product_ids, 'sku', true );

            $values = array();

            if ( is_array( $product_ids ) ) {
                foreach ( $craigslist_tags as $craigslist_tag_id => $sku ) {
                    $values[] = '( ' . (int) $craigslist_tag_id . ", " . (int) $product_ids[$sku] . ", 'product' )";
                }


                // Add at up to 500 at a time
                $value_chunks = array_chunk( $values, 500 );

                foreach ( $value_chunks as $vc ) {
                    $this->db->query( "INSERT INTO `craigslist_tags` ( `craigslist_tag_id`, `object_id`, `type` ) VALUES " . implode( ',', $vc ) . " ON DUPLICATE KEY UPDATE `object_id` = VALUES(`object_id`), `type` = VALUES( `type` )" );

                    // Handle any error
                    if ( $this->db->errno() ) {
                        $this->_err( 'Failed to add craigslist tags.', __LINE__, __METHOD__ );
                        return false;
                    }
                }
            }
        }
		
        return true;
    }

    /**
     * Update Tags
     *
     * @return bool
     */
    public function update_tags() {
        // Get tags that need to be updated
        $tag_ids = $this->db->get_col( "SELECT a.`craigslist_tag_id` FROM `analytics_craigslist` AS a LEFT JOIN `craigslist_tags` AS b ON ( a.`craigslist_tag_id` = b.`craigslist_tag_id` ) WHERE a.`date` > DATE_SUB( a.`date`, INTERVAL 30 DAY ) AND b.`craigslist_tag_id` IS NULL" );

        // Handle any error
        if ( $this->db->errno() ) {
            $this->_err( 'Failed to get unliked craigslist tags.', __LINE__, __METHOD__ );
            return false;
        }

        // Create API object
        $craigslist = new Craigslist_API( config::key('craigslist-gsr-id'), config::key('craigslist-gsr-key') );

        // Get the tag responses
        $craigslist_tags = $craigslist->get_tags( $tag_ids );

        // Get the tags
        $tags = array();

        // Form the correct array of tags
        if ( is_array( $craigslist_tags ) )
        foreach ( $craigslist_tags as $ct ) {
            if ( 'item' != $ct->type )
                continue;

            $tags = $ct;
        }

        // Add the tags
        return ( 0 == count( $tags ) ) ? true : $this->add_tags( $tags );
    }

    /**
     * Get unknown tag ids
     *
     * @param array $tag_ids
     * @return bool
     */
    public function report_unknown_tags( $tag_ids ) {
        // SQL Safe
        foreach ( $tag_ids as &$tid ) {
            $tid = (int) $tid;
        }

        // Get tags that we have
        $craiglist_tag_ids = $this->db->get_col( "SELECT `craigslist_tag_id` FROM `craigslist_tags` WHERE `craigslist_tag_id` IN ( " . implode( ',', $tag_ids ) . ')' );

        // Handle any error
        if ( $this->db->errno() ) {
            $this->_err( 'Failed to get craigslist tags.', __LINE__, __METHOD__ );
            return false;
        }

        $unknown_tag_ids = array_diff( $tag_ids, $craiglist_tag_ids );

        return true;
    }
    
    /***** OTHER *****/

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