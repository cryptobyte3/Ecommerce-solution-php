<?php
/**
 * @package Grey Suit Retail
 * @page Header
 *
 * Declare the variables we have available from other sources
 * @var Resources $resources
 * @var Template $template
 * @var User $user
 */

$resources->css('style');
$resources->javascript( 'sparrow', 'header' );

// Encoded data to get css
if ( !empty( $selected ) )
	$$selected = ' class="selected"';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $title; ?></title>
<link type="text/css" rel="stylesheet" href="/resources/css/?f=<?php echo $resources->get_css_file(); ?>" />
<script type="text/javascript" src="/resources/js/?f=<?php echo $resources->get_javascript_file( 'head' ); ?>"></script>
    <link rel="icon" href="<?php echo ( 'imagineretailer.com' == DOMAIN ) ? '/favicon.ico' : '/images/favicons/' . DOMAIN . '.ico'; ?>" type="image/x-icon" />
<?php $template->get_head(); ?>
</head>
<body>
<?php $template->get_top(); ?>
<div id="wrapper">
	<div id="header">
		<?php $margin = floor( ( 108 - LOGO_HEIGHT ) / 2 ); ?>
		<div id="logo"><img src="/images/logos/<?php echo DOMAIN; ?>.png" width="<?php echo LOGO_WIDTH; ?>" height="<?php echo LOGO_HEIGHT; ?>" alt="<?php echo TITLE, ' ', _('Logo'); ?>" style="margin: <?php echo $margin; ?>px 0" /></div>

        <?php if ( $user ) { ?>
		<a href="/logout/" id="aLogout" title="<?php echo _('Log out'); ?>"><?php echo _('Log out'); ?></a>
		<?php } ?>
	</div>
	<div id="nav">
		<div id="nav-links">
			<?php if ( $user ) { ?>
                <!--<a href="/" title="<?php echo _('Home'); ?>"<?php if ( isset( $home ) ) echo $home; ?>><?php echo _('Home'); ?></a>-->
                <a href="/accounts/" title="<?php echo _('Accounts'); ?>"<?php if ( isset( $accounts ) ) echo $accounts; ?>><?php echo _('Accounts'); ?></a>
                <a href="/products/" title="<?php echo _('Products'); ?>"<?php if ( isset( $products ) ) echo $products; ?>><?php echo _('Products'); ?></a>
                <?php if ( $user->has_permission(7) ) { ?>
                    <a href="/users/" title="<?php echo _('Users'); ?>"<?php if ( isset( $users ) ) echo $users; ?>><?php echo _('Users'); ?></a>
                <?php } ?>
                <a href="/checklists/" title="<?php echo _('Checklists'); ?>"<?php if ( isset( $checklists ) ) echo $checklists; ?>><?php echo _('Checklists'); ?></a>
                <a href="/tickets/" title="<?php echo _('Tickets'); ?>"<?php if ( isset( $tickets ) ) echo $tickets; ?>><?php echo _('Tickets'); ?></a>
                <a href="/craigslist/" title="<?php echo _('Craigslist'); ?>"<?php if ( isset( $craigslist ) ) echo $craigslist; ?>><?php echo _('Craigslist'); ?></a>
                <?php if ( $user->has_permission(7) ) { ?>
                <a href="/reports/" title="<?php echo _('Reports'); ?>"<?php if ( isset( $reports ) ) echo $reports; ?>><?php echo _('Reports'); ?></a>
                <?php } ?>
                <a href="http://admin.<?php echo str_replace( 'testing.', '', DOMAIN ); ?>/help/" title="<?php echo _('Help'); ?>"><?php echo _('Help'); ?></a>
			<?php } ?>
		</div>
	</div>