<?php
class TicketComment extends ActiveRecordBase {
    // The columns we will have access to
    public $id, $ticket_comment_id, $ticket_id, $user_id, $comment, $private, $date_created;

    // Hold the uploads
    public $uploads;

    /**
     * Setup the account initial data
     */
    public function __construct() {
        parent::__construct( 'ticket_comments' );

        // We want to make sure they match
        if ( isset( $this->ticket_comment_id ) )
            $this->id = $this->ticket_comment_id;
    }

    /**
	 * Get a Comment
	 *
	 * @param int $ticket_comment_id
	 */
	public function get( $ticket_comment_id ) {
		$this->prepare( 'SELECT `ticket_comment_id`, `user_id`, `comment`, `private`, `date_created` FROM `ticket_comments` WHERE `ticket_comment_id` = :ticket_comment_id'
            , 'i'
            , array( ':ticket_comment_id' => $ticket_comment_id )
        )->get_row( PDO::FETCH_INTO, $this );

        $this->id = $this->ticket_comment_id;
	}

    /**
	 * Get Comments
	 *
	 * @param int $ticket_id
	 * @return array
	 */
	public function get_by_ticket( $ticket_id ) {
		return $this->prepare( 'SELECT a.`ticket_comment_id`, a.`user_id`, a.`comment`, a.`private`, a.`date_created`, b.`contact_name` AS name FROM `ticket_comments` AS a LEFT JOIN `users` AS b ON ( a.`user_id` = b.`user_id` ) WHERE a.`ticket_id` = :ticket_id ORDER BY a.`date_created` DESC'
            , 'i'
            , array( ':ticket_id' => $ticket_id )
        )->get_results( PDO::FETCH_CLASS, 'TicketComment' );
	}



    /**
     * Create
     */
    public function create() {
        // Set the time it was created
        $this->date_created = dt::now();

        $this->insert( array(
            'ticket_id' => $this->ticket_id
            , 'user_id' => $this->user_id
            , 'comment' => $this->comment
            , 'private' => $this->private
            , 'date_created' => $this->date_created
        ), 'iisis' );

        $this->id = $this->ticket_comment_id = $this->get_insert_id();
    }

    /**
     * Add upload links
     *
     * @param array $links
     */
    public function add_upload_links( array $links ) {
        // Declare variables
        $value = '( ' . (int) $this->id . ', ? )';
        $link_count = count( $links );

		// Form the data to link
		$values = $value . str_repeat( ',' . $value, $link_count - 1 );

		// Link it
		$this->prepare(
            "INSERT INTO `ticket_comment_upload_links` ( `ticket_comment_id`, `ticket_upload_id` ) VALUES $values"
            , str_repeat( 'i', $link_count )
            , $links
        )->query();
    }

    /**
     * Delete the ticket comment
     */
    public function delete() {
        parent::delete( array( 'ticket_comment_id' => $this->id ), 'i' );
    }

    /**
     * Delete Links
     */
    public function delete_upload_links() {
        $this->prepare(
            'DELETE FROM `ticket_comment_upload_links` WHERE `ticket_comment_id` = :ticket_comment_id'
            , 'i'
            , array( ':ticket_comment_id' => $this->id )
        )->query();
    }
}