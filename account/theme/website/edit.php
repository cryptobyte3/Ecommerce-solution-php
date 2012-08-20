<?php
/**
 * @page Edit Website Page
 * @package Grey Suit Retail
 */

// Get current user
global $user;

// If user is not logged in
if ( !$user )
	login();

$website_page_id = (int) $_GET['wpid'];

// Send to website listing page
if ( empty( $website_page_id ) )
	url::redirect('/website/');

// Instantiate classes
$w = new Websites;
$wf = new Website_Files;
$v = new Validator;

// Get page
$page = $w->get_page( $website_page_id );

// Get all the website files
$website_files = $wf->get_all();

/***** VALIDATION *****/
$v->form_name = 'fEditPage';

// Products can be blank
if ( 'products' != $page['slug'] )
	$v->add_validation( 'taContent', 'req', _('Page Content is required.') );

// Custom validation
switch ( $page['slug'] ) {
	case 'financing':
		$v->add_validation( 'tApplyNowLink', 'URL', _('The "Apply Now Link" field must contain a valid link') );
	break;
	
	case 'current-offer':
		$v->add_validation( 'tEmail', 'req', _('The "Email" field is required') );
		$v->add_validation( 'tEmail', 'email', _('The "Email" field must contain a valid email') );
	break;
	
	default:break;
}

// Add the javascript vlidation
add_footer( $v->js_validation() );

/***** HANDLE SUBMIT *****/

// Initialize variable
$success = false;

// Make sure it's a valid request
if ( isset( $_POST['_nonce'] ) && nonce::verify( $_POST['_nonce'], 'edit-page' ) ) {
	$errs = $v->validate();
	
	// if there are no errors
	if ( empty( $errs ) ) {
        // Home page can't update their slug
        $slug = ( 'home' == $page['slug'] ) ? 'home' : $_POST['tPageSlug'];
        $title = ( _('Page Title...') == $_POST['tTitle'] ) ? '' : $_POST['tTitle'];
		$mobile = (int) ( $_POST['cbIsMobile'] == "on" );
        
		// Update the page
		$success = $w->update_page( $website_page_id, $slug, $title, $_POST['taContent'], $_POST['tMetaTitle'], $_POST['tMetaDescription'], $_POST['tMetaKeywords'], $mobile );
		
		// Update custom meta
		switch ( $page['slug'] ) {
			case 'contact-us':
				$pagemeta = array( 'addresses' => htmlspecialchars( $_POST['hAddresses'] ) );
			break;
			
			case 'current-offer':
				$pagemeta = array(
                    'email' => $_POST['tEmail']
                    , 'display-coupon' => $_POST['cbDisplayCoupon']
                    , 'email-coupon' => ( isset( $_POST['cbEmailCoupon'] ) ) ? 'yes' : 'no'
                );
			break;
			
			case 'financing':
				$pagemeta = array( 'apply-now' => $_POST['tApplyNowLink'] );
			break;

			case 'products':
				$pagemeta = array( 'top' => $_POST['sTop'] );

                if ( $_POST['tTitle'] == _('Page Title...') ) $_POST['tTitle'] = '';
                    $pagemeta['page-title'] = $_POST['tTitle'];
			break;
		}

		// Set pagemeta
		if ( is_array( $pagemeta ) )
			$w->set_pagemeta( $website_page_id, $pagemeta );
		
		// Get new page
		$page = $w->get_page( $website_page_id );
	}
}

/***** ATTACHMENTS & PAGEMETA *****/

css( 'jquery.uploadify' );
javascript( 'mammoth', 'swfobject', 'jquery.uploadify', 'website/page' );

switch ( $page['slug'] ) {
	case 'contact-us':
		css('website/pages/contact-us');
		javascript('website/pages/contact-us');
		list( $contacts, $multiple_location_map, $hide_all_maps ) = array_values( $w->get_pagemeta_by_key( $website_page_id, 'addresses', 'multiple-location-map', 'hide-all-maps' ) );
	break;
	
	case 'current-offer':
		// Need to get an attachment
		$wa = new Website_Attachments;
		javascript( 'website/pages/current-offer' );
		$coupon = $wa->get_by_name( $website_page_id, 'coupon' );
		
		$metadata = $w->get_pagemeta_by_key( $website_page_id, 'email', 'display-coupon', 'email-coupon' );
	break;
	
	case 'financing':
		// Need to get an attachment
		$wa = new Website_Attachments;
		javascript('website/pages/financing');
		
		$apply_now = $wa->get_by_name( $website_page_id, 'apply-now' );
		$apply_now_link = $w->get_pagemeta_by_key( $website_page_id, 'apply-now' );
	break;
	
	case 'products':
		$top = $w->get_pagemeta_by_key( $website_page_id, 'top' );
	break;
	
	default:break;
}

if ( 'products' == $page['slug'] ) {
    $page_title = $w->get_pagemeta_by_key( $website_page_id, 'page-title' );
} else {
    $page_title = $page['title'];
}

/***** NORMAL PAGE FUNCTIONS *****/

$selected = "website";
css('website/page');
$title = _('Edit Page') . ' | ' . TITLE;
get_header();
?>

<div id="content">
	<h1><?php echo _('Edit Page'); ?></h1>
	<br clear="all" /><br />
	<?php get_sidebar( 'website/', 'edit_page' ); ?>
	<div id="subcontent">
		<?php if ( $success ) { ?>
		<div class="success">
			<p><?php echo _('Your page has been updated.'); ?> <a href="http://<?php if ( !empty( $user['website']['subdomain'] ) ) echo $user['website']['subdomain'], '.'; echo $user['website']['domain'], '/', $page['slug']; ?>/" title="<?php echo _('View Page'); ?>" target="_blank"><?php echo _('View the page.'); ?></a></p>
			<p><a href="/website/" title="<?php echo _('Edit Other Pages'); ?>"><?php echo _('Click here to edit other pages.'); ?></a></p>
		</div>
		<?php
		}
		
		if ( isset( $errs ) )
			echo "<p class='red'>$errs</p>";
		?>
		<form name="fEditPage" action="/website/edit/?wpid=<?php echo $website_page_id; ?>" method="post">
            <div id="dTitleContainer">
                <input name="tTitle" id="tTitle" class="tb" value="<?php echo ( ( isset( $page_title ) && $page_title != '' ) ? $page_title : '' );?>" tmpval="<?php echo _('Page Title...'); ?>" />
            </div>
            <?php if ( 'home' != $page['slug'] ) { ?>
            <div id="dPageSlug">
            	<span><strong><?php echo _('Link:'); ?></strong> http://<?php echo $user['website']['domain']; ?>/<span id="sPageSlug"><?php echo $page['slug']; ?></span><input type="text" name="tPageSlug" id="tPageSlug" maxlength="50" class="tb hidden" value="<?php echo $page['slug']; ?>" />/</span>
                &nbsp;
                <a href="javascript:;" id="aCancelPageSlug" title="Cancel" class="hidden"><?php echo _('Cancel'); ?></a>
                <a href="javascript:;" id="aEditPageSlug" title="<?php echo _('Edit Link'); ?>"><?php echo _('Edit'); ?></a>&nbsp;
                <a href="javascript:;" id="aSavePageSlug" title="<?php echo _('Save Link'); ?>" class="button hidden round"><?php echo _('Save'); ?></a>
            </div>
            <?php } ?>
            <br />
            <textarea name="taContent" id="taContent" cols="50" rows="3" rte="1"><?php echo $page['content']; ?></textarea>
            <p><a href="javascript:;" id="aMetaData" title="<?php echo _('Meta Data'); ?>"><?php echo _('Meta Data'); ?> [ + ]</a> | <a href="#dUploadFile" title="<?php echo _('Upload File (Media Manager)'); ?>" rel="dialog"><?php echo _('Upload File'); ?></a></p>
            <br />
            <div id="dMetaData" class="hidden">
                <p>
                    <label for="tMetaTitle"><?php echo _('Meta Title'); ?></label> <small>(<?php echo _('Recommended not to exceed 70 characters'); ?>)</small><br />
                    <input type="text" class="tb" name="tMetaTitle" id="tMetaTitle" value="<?php echo $page['meta_title']; ?>" />
                </p>
                <p>
                    <label for="tMetaDescription"><?php echo _('Meta Description'); ?></label> <small>(<?php echo _('Recommended not to exceed 250 characters'); ?>)</small><br />
                    <input type="text" class="tb"  name="tMetaDescription" id="tMetaDescription" value="<?php echo $page['meta_description']; ?>" />
                </p>
                <p>
                    <label for="tMetaKeywords"><?php echo _('Meta Keywords'); ?></label> <small>(<?php echo _('Recommended not to exceed 250 characters'); ?>)</small><br />
                    <input type="text" class="tb" name="tMetaKeywords" id="tMetaKeywords" value="<?php echo $page['meta_keywords']; ?>" />
                </p>
                <br />
            </div>

            <?php if ( $user['website']['mobile_marketing'] ) { ?>
                <p><input type="checkbox" class="cb" name="cbIsMobile" id="cbIsMobile" <?php if ( $page['mobile'] ) echo "checked"; ?> /> <label for="cbIsMobile"><?php echo _('Link to Mobile Website'); ?></label></p>
                <br />
            <?php
            }

            if ( in_array( $page['slug'], array( 'contact-us', 'current-offer', 'financing', 'products' ) ) )
                require theme_inc( 'website/pages/' . $page['slug'] );
            ?>
            <br /><br />
            <br /><br />
            <p><input type="submit" id="bSubmit" value="<?php echo _('Save'); ?>" class="button" /></p>
            <?php nonce::field( 'edit-page' ); ?>
		</form>
		<br />
		
		<div id="dUploadFile" class="hidden">
			<ul id="ulUploadFile">
				<?php
				if ( is_array( $website_files ) ) {
					// Set variables
					$ajax_delete_file_nonce = nonce::create('delete-file');
					$confirm = _('Are you sure you want to delete this file?');
					
					foreach ( $website_files as $wf ) {
						$file_name = f::name( $wf['file_path'] );
						echo '<li id="li' . $wf['website_file_id'] . '"><a href="', $wf['file_path'], '" id="aFile', $wf['website_file_id'], '" class="file" title="', $file_name, '">', $file_name, '</a><a href="/ajax/website/page/delete-file/?_nonce=' . $ajax_delete_file_nonce . '&amp;wfid=' . $wf['website_file_id'] . '" class="float-right" title="' . _('Delete File') . '" ajax="1" confirm="' . $confirm . '"><img src="/images/icons/x.png" width="15" height="17" alt="' . _('Delete File') . '" /></a></li>';
					}
				} else {
					echo '<li class="no-files">', _('You have not uploaded any files.') . '</li>';
				}
				?>
			</ul>
			<br />
			
			<input type="text" class="tb" id="tFileName" tmpval="<?php echo _('Enter File Name'); ?>..." error="<?php echo _('You must type in a file name before uploading a file.'); ?>" style="position:relative; bottom: 11px;" /> 
			<input type="file" name="fUploadFile" id="fUploadFile" />
			<br /><br />
			<div id="dCurrentLink" class="hidden">
				<p><strong><?php echo _('Current Link'); ?>:</strong></p>
				<p><input type="text" class="tb" id="tCurrentLink" value="<?php echo _('No link selected'); ?>" style="width:100%;" /></p>
			</div>
		</div>
		<?php 
		nonce::field( 'upload-file', '_ajax_upload_file' );
		nonce::field( 'upload-image', '_ajax_upload_image' );
		?>
		<input type="hidden" id="hWebsiteID" value="<?php echo $user['website']['website_id']; ?>" />
		<input type="hidden" id="hWebsitePageID" value="<?php echo $website_page_id; ?>" />
	</div>
	<br /><br />
</div>

<?php get_footer(); ?>