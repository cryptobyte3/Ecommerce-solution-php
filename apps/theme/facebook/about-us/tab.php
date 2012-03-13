<?php
/**
 * @page About Us
 * @package Imagine Retailer
 */

global $user;

// Instantiate Classes
$fb = new FB( '233746136649331', '298bb76cda7b2c964e0bf752cf239799', true );
$au = new About_Us;

// Get the signed request
$signed_request = $fb->getSignedRequest();

// Get User
$user_id = $fb->user;

// Get the website
$tab = $au->get_tab( $signed_request['page']['id'] );

// If it's secured, make the images secure
if ( security::is_ssl() )
    $tab = ( stristr( $tab, 'websites.retailcatalog.us' ) ) ? preg_replace( '/(?<=src=")(http:\/\/)/i', 'https://s3.amazonaws.com/', $tab ) : preg_replace( '/(?<=src=")(http:)/i', 'https:', $tab );

$title = _('About Us') . ' | ' . _('Online Platform');
get_header('facebook/tabs/');
?>

<div id="content">
	<?php if( $signed_request['page']['admin'] ) { ?>
		<p><strong>Admin:</strong> <a href="#" onclick="top.location.href='http://apps.facebook.com/op-about-us/?app_data=<?php echo url::encode( array( 'uid' => security::encrypt( $user_id, 'SecREt-Us3r!' ), 'pid' => security::encrypt( $signed_request['page']['id'], 'sEcrEt-P4G3!' ) ) ); ?>';">Update Settings</a></p>
	<?php 
	}
	
	echo $tab;
	?>
</div>

<?php get_footer('facebook/tabs/'); ?>