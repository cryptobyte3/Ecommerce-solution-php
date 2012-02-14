<?php
/**
 * @page Products - Add/Edit
 * @package Imagine Retailer
 */

// Get current user
global $user;

// If user is not logged in
if ( !$user )
	login();

// Instantiate classes
$c = new Categories;
$craigslist = new Craigslist;
$v = new Validator;

// Setup RTE
add_footer( '<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
CKEDITOR.on("dialogDefinition", function( ev ) {
	var dialogName = ev.data.name;  
	var dialogDefinition = ev.data.definition;
	
	switch (dialogName) {
	case "image": //Image Properties dialog
		dialogDefinition.removeContents("advanced");
		//dialogDefinition.removeContents("info");
		dialogDefinition.removeContents("Link");
		break;
	case "link": //image Properties dialog
		dialogDefinition.removeContents("advanced");
		break;
	}
});
</script>' );

// Get categories
$categories = $c->get_list();

$cid = ( isset( $_GET['cid'] ) ) ? $_GET['cid'] : false;

// Get everything
if ( $cid ) {
	$template = $craigslist->get( $cid );
} else {
	$template = array(
		'craigslist_template_id' => ''
		, 'category_id' => ''
		, 'title' => ''
		, 'description' => ''
	);
}

// Add Validation
$v->form_name = 'fAddEdit';
$v->add_validation( 'tTitle', 'req', _('The "Title" field is required"') );

add_footer( $v->js_validation() );

if ( isset( $_POST['_nonce'] ) && nonce::verify( $_POST['_nonce'], 'add-edit-craigslist' ) ) {
	// Server side validation
	$errs = $v->validate();
	if ( empty( $errs ) ) {
		$craigslist_template_id = (int) $_POST['hCraigslistID'];
		$title = (string) $_POST['tTitle'];
		$description = (string) $_POST['taDescription'];
		$category_id = (int) $_POST['sCraigslistCategory'];
		
		// Update or Create the template
		if ( $craigslist_template_id == 0 ){
			$success = $craigslist->create( $category_id, $title, $description );
		} else {
			$success = $craigslist->update( $craigslist_template_id, $category_id, $title, $description );
		}
		
		// If they just created a product, they are now editing
		if ( $success && empty( $_GET['cid'] ) ) {
			url::redirect( '/craigslist/?m=1' );
		} else {
			url::redirect( '/craigslist/?m=2' );
		}
	}
}

css( 'craigslist/add-edit', 'jquery.ui', 'jquery.uploadify' );
javascript( 'validator', 'jquery', 'jquery.ui', 'swfobject', 'jquery.uploadify', 'jquery.tmp-val',  'craigslist/add-edit' );

$selected = 'craigslist';
$title = _('Add/Edit Craigslist Ad Template') . ' | ' . TITLE;
get_header();
?>

<div id="content">
	<h1><?php echo _('Craigslist Ad Template'); ?></h1>
	<br clear="all" /><br />
	<?php get_sidebar( 'craigslist/', 'templates' ); ?>
	<div id="subcontent">
		<?php
		nonce::field( 'create-craigslist', '_ajax_create_craigslist' );
		nonce::field( 'preview-craigslist', '_ajax_preview_craigslist' );
		?>
		<form name="fAddEdit" id="fAddEdit" action="/craigslist/add-edit/" method="post">
		<input type="hidden" id="hCraigslistTemplateID" name="hCraigslistTemplateID" value="<?php echo $template['craigslist_template_id']; ?>" />
        <div id="right-sidebar">
			<!-- Box Categories -->
			<div class="box">
				<h2><?php echo _('Category'); ?></h2>
				<div class="box-content">
					<select name="sCraigslistCategory" id="sCraigslistCategory">
						<option value="">-- <?php echo _('Select a Category'); ?> --</option>
						<?php echo $categories; ?>
					</select>
					<br clear="all" />
					<input type="hidden" name="hCategory" id="hCategory" value="<?php if ( $template['category_id'] ) echo $template['category_id']; ?>"/>
				</div>
			</div>
		<!-- End of Box Categories -->
					
		<!-- Tags Box -->
			<div class="box">
				<h2><?php echo _('Syntax Tags'); ?></h2>
				<div class="box-content">
               	  <p style="line-height:18px;">
                    [<?php echo _('Product Name'); ?>]<br />
                    [<?php echo _('Store Name'); ?>]<br />
                    [<?php echo _('Store Logo'); ?>]<br />
                    [<?php echo _('Category'); ?>]<br />
                    [<?php echo _('Brand'); ?>]<br />
                    [<?php echo _('Product Description'); ?>]<br />
                    [<?php echo _('Product Specs'); ?>]<br />
                    [<?php echo _('SKU'); ?>]<br />
                    [<?php echo _('Photo'); ?>]<br />
    				</p>
					<br clear="all" />
				</div>
			</div>
		<!-- End of Tags Box -->
 			
		<!-- Box Publish  -->
			<div class="box">
				<div class="box-content">
					<div class="box-action"><input type="submit" class="button" id="iSave" value="<?php echo _('Save'); ?>" /></div>
				</div>
				<!-- End of Box Publish -->
			</div>
		</div>

		<div class="page-content">
			<?php 
			if ( isset( $errs ) )
				echo "<p class='red'>$errs</p><br />";
			?>
			<input type="hidden" id="hCraigslistID" name="hCraigslistID" value="<?php echo ( isset( $template['craigslist_template_id'] ) ) ? $template['craigslist_template_id'] : ''; ?>" />
			<div id="dTitleContainer"><input type="text" name="tTitle" id="tTitle" title="<?php echo _('Ad Title'); ?>" value="<?php echo ( isset( $template['title'] ) ) ? str_replace( '"', '&quot;', $template['title'] ) : _('Ad Title'); ?>" maxlength="200" /></div>
			
			<textarea name="taDescription" id="taDescription" rows="12" cols="50"><?php echo $template['description']; ?></textarea>

			<div class="divider"></div>
			<div class="page-widget" id="dPreview">
				<h2><?php echo _('Preview Template'); ?></h2> <a href="#" id="aRefreshPreview"><?php echo _('Refresh'); ?></a>
			</div>
            <div id="dPreviewArea" style="background-color:#fff;border:1px solid black;width:100%; padding:10px; margin-top:10px;">
            	<?php echo _('Hit "refresh" to preview your ad.'); ?>
            </div>
			<br /><br />
			<?php nonce::field( 'add-edit-craigslist' ); ?>
		</div>
        
        <div id="dPreviewData" style="display:none;">
        	<input id="dPreviewProductID" type="hidden" />
        	<input id="dPreviewCategoryID" type="hidden" />
        	<input id="dPreviewProductName" type="hidden" />
        	<input id="dPreviewStoreName" type="hidden" />
            <input id="dPreviewStoreLogo" type="hidden" />
        	<input id="dPreviewCategoryName" type="hidden" />
        	<input id="dPreviewBrand" type="hidden" />
        	<input id="dPreviewProductDescription" type="hidden" />
        	<input id="dPreviewProductSpecs" type="hidden" />
        	<input id="dPreviewPhoto1" type="hidden" />
        	<input id="dPreviewPhoto2" type="hidden" />
        	<input id="dPreviewPhoto3" type="hidden" />
        	<input id="dPreviewPhoto4" type="hidden" />
        	<input id="dPreviewPhoto5" type="hidden" />
        	<input id="dPreviewAttributes" type="hidden" />
        	<input id="dPreviewSKU" type="hidden" />
        </div>
		</form>
		<br clear="all" />
	</div>
</div>

<?php get_footer(); ?>