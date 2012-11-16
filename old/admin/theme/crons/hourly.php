<?php
/**
 * @page Update Scheduled Emails
 * @package Grey Suit Retail
 */

// Set it as a background job
newrelic_background_job();

$e = new Emails;
$e->update_scheduled_emails();

$t = new Tickets;
$t->clean_uploads();

// Send Autoposts
$sm = new Social_Media;

$posts = $sm->get_posting_posts();

if ( is_array( $posts ) ) {
	$fb = new FB( '268649406514419', '6ca6df4c7e9d909a58d95ce7360adbf3' );
	
	$sm_posting_post_ids = $sm_error_ids = array();
	
	foreach ( $posts as $p ) {
		$fb->setAccessToken( $p['access_token'] );

		// Information:
		// http://developers.facebook.com/docs/reference/api/page/#posts
		try {
            $fb->api( $p['fb_page_id'] . '/feed', 'POST', array( 'message' => $p['post'], 'link' => $p['link'] ) );
        } catch ( Exception $e ) {
			$error_message = $e->getMessage();
			
			$sm_error_ids[$p['sm_posting_post_id']] = $error_message;
			
            fn::mail( $p['email'], $p['company'] . ' - Unable to Post to Facebook', "We were unable to send the following post to Facebook:\n\n" . $p['post'] . "\n\nFor the following reason(s):\n\n" . $error_message . "\n\nTo fix this, please login to the dashboard, go to Social Media > Posting, then delete this post and recreate it following the rules above.\n\n" . $p['account'] . "\nhttp://admin." . $p['domain'] . "/accounts/control/?wid=" . $p['website_id'] . "\n\nHave a great day!", $p['company'] . ' <noreply@' . $p['domain'] . '>' );
            continue;
        }
	}
	
	// Mark post errors
	$sm->mark_posting_post_errors( $sm_error_ids );
}
?>