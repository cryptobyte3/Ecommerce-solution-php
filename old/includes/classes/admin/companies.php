<?php
/**
 * Handles all the companies
 *
 * @package Grey Suit Retail
 * @since 1.0
 */
class Companies extends Base_Class {
	/**
	 * Construct initializes data
	 */
	public function __construct() {
		// Need to load the parent constructor
		if ( !parent::__construct() )
			return false;
	}

    /**
     * Get a company by ID
     * 
     * @param int $company_id
     * @return array
     */
    public function get( $company_id ) {
        global $user;

        // Type Juggling
        $company_id = (int) $company_id;

        // Make sure they have permission
        if ( $user['role'] < 8 && $user['company_id'] != $company_id )
            return false;

        $company = $this->db->get_row( "SELECT `name`, `domain` FROM `companies` WHERE `company_id` = $company_id", ARRAY_A );

		// Handle errors
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get company', __LINE__, __METHOD__ );
			return false;
		}

        return $company;
    }

	/**
	 * Get All Companies
	 *
	 * @return array
	 */
	public function get_all() {
        global $user;

        // Make sure they have permission
        $where = ( $user['role'] < 8 ) ? ' WHERE `company_id` = ' . (int) $user['company_id'] : '';

        $companies = $this->db->get_results( "SELECT `company_id`, `name` FROM `companies` $where", ARRAY_A );
		
		// Handle errors
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get companies', __LINE__, __METHOD__ );
			return false;
		}
		
		return $companies;
	}

    /**
     * Get Packages based on Website ID
     *
     * @param int $website_id
     * @return array
     */
    public function get_packages( $website_id ) {
        // Type Juggling
        $website_id = (int) $website_id;
        
        $packages = $this->db->get_results( "SELECT a.`company_package_id`, a.`name` FROM `company_packages` AS a LEFT JOIN `users` AS b ON ( a.`company_id` = b.`company_id` ) LEFT JOIN `websites` AS c ON ( b.`user_id` = c.`user_id` ) WHERE c.`website_id` = $website_id ORDER BY a.`name` ASC", ARRAY_A );
        
        // Handle errors
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get packages', __LINE__, __METHOD__ );
			return false;
		}

        return ( is_array( $packages ) ) ? ar::assign_key( $packages, 'company_package_id', true ) : false;
    }

    /**
	 * Gets the data for an autocomplete company packages
	 *
	 * @param string $query
	 * @return bool
	 */
	public function autocomplete_packages( $query ) {
        global $user;

        if ( $user['role'] < 8 ) {
            // Type Juggling
            $company_id = (int) $user['company_id'];

            $where = " AND `company_id` = $company_id";
        } else {
            $where = '';
        }

		// Get results
		$results = $this->db->prepare( "SELECT `company_package_id` AS object_id, `name` AS package FROM `company_packages` WHERE `name` LIKE ? $where ORDER BY `name` LIMIT 10", 's', $query . '%' )->get_results( '', ARRAY_A );

		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get autocomplete company packages.', __LINE__, __METHOD__ );
			return false;
		}

		return $results;
	}

    /**
     * Create a company
     *
     * @param string $name
     * @param string $domain
     * @return int
     */
    public function create( $name, $domain ) {
        global $user;

        if ( $user['role'] < 8 )
            return false;

        $this->db->insert( 'companies', array( 'name' => $name, 'domain' => $domain, 'date_created' => dt::date('Y-m-d H:i:s') ), 'sss' );

        // Handle errors
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to create company', __LINE__, __METHOD__ );
			return false;
		}

        return $this->db->insert_id;
    }

    /**
     * Updates a company
     *
     * @param int $company_id
     * @param string $name
     * @param string $domain
     * @return bool
     */
    public function update( $company_id, $name, $domain ) {
        global $user;

        if ( $user['role'] < 8 )
            return false;

        $this->db->update( 'companies', array( 'name' => $name, 'domain' => $domain ), array( 'company_id' => $company_id ), 'ss', 'i' );

        // Handle errors
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to update company', __LINE__, __METHOD__ );
			return false;
		}

        return true;
    }

    /**
	 * Returns companies for listing
	 *
	 * @param string $limit
	 * @param string $where
	 * @param string $order_by
	 * @return array
	 */
	public function list_companies( $limit, $where, $order_by ) {
        // Get linked companies
        $companies = $this->db->get_results( "SELECT `company_id`, `name`, `domain`, UNIX_TIMESTAMP( `date_created` ) AS date_created FROM `companies` WHERE `status` = 1 $where ORDER BY $order_by LIMIT $limit", ARRAY_A );

        // Handle any error
        if ( $this->db->errno() ) {
            $this->_err( 'Failed to list companies.', __LINE__, __METHOD__ );
            return false;
        }

		return $companies;
	}

	/**
	 * Returns the number of companies for listing
	 *
	 * @param string $where
	 * @return int
	 */
	public function count_companies( $where ) {
        // Get the company count
        $count = $this->db->get_var( "SELECT COUNT( `company_id` ) FROM `companies` WHERE `status` = 1 $where" );

        // Handle any error
        if ( $this->db->errno() ) {
            $this->_err( 'Failed to count companies.', __LINE__, __METHOD__ );
            return false;
        }

		return $count;
	}
	
	/**
	 * Gets the data for an autocomplete
	 *
	 * @param string $query
	 * @return bool
	 */
	public function autocomplete( $query ) {
		global $user;

        $where = ( $user['role'] < 8 ) ? ' AND `company_id` = ' . (int) $user['company_id'] : '';

		// Get results
		$results = $this->db->prepare( "SELECT `company_id` AS object_id, `name` AS company FROM `companies` WHERE `name` LIKE ? $where ORDER BY `name`", 's', $query . '%' )->get_results( '', ARRAY_A );
		
		// Handle any error
		if ( $this->db->errno() ) {
			$this->_err( 'Failed to get autocomplete entries.', __LINE__, __METHOD__ );
			return false;
		}
		
		return $results;
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