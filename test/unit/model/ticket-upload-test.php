<?php

require_once 'test/base-database-test.php';

class TicketUploadTest extends BaseDatabaseTest {
    /**
     * @var TicketUpload
     */
    private $ticket_upload;

    /**
     * Will be executed before every test
     */
    public function setUp() {
        $this->ticket_upload = new TicketUpload();
    }

    /**
     * Test Getting a ticket comment
     */
    public function testGet() {
        // Declare variables
        $key = '123/321/feeling.rig';

        // Insert
        $ticket_upload_id = $this->phactory->insert( 'ticket_uploads', compact( 'key' ), 's' );

        // Get
        $this->ticket_upload->get( $ticket_upload_id );

        $this->assertEquals( $this->ticket_upload->key, $key );

        // Clean up
        $this->phactory->delete( 'ticket_uploads', compact( 'ticket_upload_id' ), 'i' );
    }

    /**
     * Test Getting all the uploads for a ticket message
     */
    public function testGetByTicket() {
        // Declare variables
        $ticket_id = 33;

        // Get uploads
        $uploads = $this->ticket_upload->get_by_ticket( $ticket_id );

        $this->assertEquals( $uploads[0], '19/160/33/gsr-home.jpg' );
    }

    /**
     * Test Getting all the uploads for a ticket's comments
     */
    public function testGetByComments() {
        // Declare variables
        $ticket_id = -99;
        $key = 'hey-hey';

        // Create a comment
        $ticket_comment_id = $this->phactory->insert( 'ticket_comments', compact( 'ticket_id' ), 'ii' );
        $ticket_upload_id = $this->phactory->insert( 'ticket_uploads', compact( 'ticket_comment_id', 'key' ), 'iis' );

        // Get uploads
        $uploads = $this->ticket_upload->get_by_comments( $ticket_id );

        $this->assertTrue( current( $uploads ) instanceof TicketUpload );

        // Clean Up
        $this->phactory->delete( 'ticket_comments', compact( 'ticket_id' ), 'i' );
        $this->phactory->delete( 'ticket_uploads', compact( 'ticket_upload_id' ), 'i' );
    }

    /**
     * Test Getting all the uploads for a ticket comment
     */
    public function testGetByComment() {
        // Declare variables
        $ticket_comment_id = -459;
        $key = 'hey-hey';

        // Create a comment
        $ticket_upload_id = $this->phactory->insert( 'ticket_uploads', compact( 'ticket_comment_id', 'key' ), 'iis' );

        // Get uploads
        $uploads = $this->ticket_upload->get_by_comment( $ticket_comment_id );

        $this->assertTrue( current( $uploads ) instanceof TicketUpload );

        // Clean up
        $this->phactory->delete( 'ticket_uploads', compact( 'ticket_upload_id' ), 'i' );
    }

    /**
     * Test creating a ticket upload
     *
     * @depends testGet
     */
    public function testCreate() {
        // Declare variables
        $key = 'url/path/file.jpg';

        // Create ticket upload
        $this->ticket_upload->key = $key;
        $this->ticket_upload->create();

        $this->assertNotNull( $this->ticket_upload->id ) );

        // Make sure it's in the database
        $this->ticket_upload->get( $this->ticket_upload->id );

        $this->assertEquals( $key, $this->ticket_upload->key );

        // Delete the upload
        $this->phactory->delete( 'ticket_uploads', array( 'ticket_upload_id' => $this->ticket_upload->id ), 'i' );
    }

    /**
     * Test Getting keys by uncreated tickets (so that we can remove uploads)
     *
     * @depends testCreate
     */
    public function testGetKeysByUncreatedTickets() {
        // Create ticket
        $ticket_id = $this->phactory->insert( 'tickets', array( 'status' => -1, 'date_created' => '2012-10-09 00:00:00' ), 'is' );

        // Create ticket uploads
        $this->ticket_upload->ticket_id = $ticket_id;
        $this->ticket_upload->key = 'url/path/file.jpg';
        $this->ticket_upload->create();

        $this->ticket_upload->key = 'url/path/file2.jpg';
        $this->ticket_upload->create();

        // Now, let's get the keys
        $keys = array_reverse( $this->ticket_upload->get_keys_by_uncreated_tickets() );

        $this->assertTrue( in_array( $this->ticket_upload->key, $keys ) );

        // Now delete everything
        $this->phactory->query( "DELETE tu.*, t.* FROM `ticket_uploads` AS tu LEFT JOIN `tickets` AS t ON ( t.`ticket_id` = tu.`ticket_id` ) WHERE t.`ticket_id` = $ticket_id AND t.`status` = -1");
    }

    /**
     * Test Adding Ticket Links
     *
     * @depends testCreate
     */
    public function testAddRelations() {
        // Create ticket
        $ticket_id = $this->phactory->insert( 'tickets', array( 'status' => -1, 'date_created' => '2012-10-09 00:00:00' ), 'is' );

        // Create ticket uploads
        $this->ticket_upload->key = 'url/path/file.jpg';
        $this->ticket_upload->create();

        // Declare variables
        $ticket_upload_ids = array( $this->ticket_upload->id );
        $this->ticket_upload->add_relations( $ticket_id, $ticket_upload_ids );

        // Now check it
        $fetched_ticket_upload_ids = $this->phactory->get_col( "SELECT `ticket_upload_id` FROM `ticket_uploads` WHERE `ticket_id` = " . (int) $ticket_id );

        $this->assertEquals( $ticket_upload_ids, $fetched_ticket_upload_ids );

        // Delete links and ticket
        $this->phactory->delete( 'tickets', compact( 'ticket_id' ), 'i' );
        $this->phactory->delete( 'ticket_uploads', compact( 'ticket_id' ), 'i' );
    }

    /**
     * Test Deleting
     *
     * @depends testCreate
     */
    public function testDeleteUpload() {
        // Create ticket upload
        $this->ticket_upload->key = 'url/path/file.jpg';
        $this->ticket_upload->create();

        $ticket_upload_id = (int) $this->ticket_upload->id;

        // Delete ticket upload
        $this->ticket_upload->delete_upload();

        // Check
        $fetched_ticket_upload_id = $this->phactory->get_var( "SELECT `ticket_upload_id` FROM `ticket_uploads` WHERE `ticket_upload_id` = $ticket_upload_id" );

        $this->assertFalse( $fetched_ticket_upload_id );
    }

    /**
     * Will be executed after every test
     */
    public function tearDown() {
        $this->ticket_upload = null;
    }
}
