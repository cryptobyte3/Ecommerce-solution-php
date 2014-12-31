<?php
/**
 * @package Grey Suit Retail
 * @page Add/Edit | Craigslist | Email Marketing
 *
 * Declare the variables we have available from other sources
 * @var Resources $resources
 * @var Template $template
 * @var User $user
 * @var CraigslistAd $ad
 * @var string $errs
 * @var string $js_validation
 * @var CraigslistMarket[] $markets
 * @var Craigslist_API $craiglist_api
 */

$title = ( $ad->id ) ? _('Edit') : _('Add');
echo $template->start( $title . ' ' . _('Craigslist Ad') );

if ( !empty( $errs ) )
    echo "<p class='red'>$errs</p>";
?>
<form name="fAddCraigslistTemplate" id="fAddCraigslistTemplate" action="" method="post">
    <input id="hCraigslistAdID" name="hCraigslistAdID" type="hidden" value="<?php if ( $craigslist_ad_id ) echo $craigslist_ad_id; ?>" />
    <input id="hProductID" name="hProductID" type="hidden" value="<?php echo ( isset( $_POST['hProductID'] ) ) ? $_POST['hProductID'] : $ad->product_id;?>" />
    <input id="hProductName" name="hProductName" type="hidden" value="<?php echo ( isset( $_POST['hProductName'] ) ) ? $_POST['hProductName'] : $ad->product_name; ?>" />
    <input id="hProductCategoryID" type="hidden" value="0" />
    <input id="hProductCategoryName" type="hidden" value="" />
    <input id="hProductSKU" type="hidden" value="<?php echo ( isset( $_POST['hProductSKU'] ) ) ? $_POST['hProductSKU'] : $ad->sku; ?>" />
    <input id="hProductBrandName" type="hidden" value="0" />
    <input id="hProductDescription" type="hidden" value="" />
    <textarea id="hProductSpecifications" class="hidden" rows="5" cols="50"></textarea>
    <input id="hStoreName" type="hidden" value="<?php echo $user->account->title; ?>" />
    <input id="hStoreURL" type="hidden" value="<?php echo 'http://', $user->account->domain; ?>" />
    <input id="hStoreLogo" type="hidden" value="<?php echo str_replace( 'logo/', 'logo/large/', $user->account->logo ); ?>" />
    <input name="hPostAd" id="hPostAd" type="hidden" value="0" />
    <textarea name="hCraigslistPost" id="hCraigslistPost" rows="5" cols="50" class="hidden"></textarea>

    <div id="dNarrowSearch">
        <?php
        nonce::field( 'autocomplete', '_autocomplete' );
        nonce::field( 'load_product', '_load_product' );
        ?>
        <h2><?php echo _('Select Product');?></h2>
        <select id="sAutoComplete" tabindex="1">
            <option value="sku"><?php echo _('SKU'); ?></option>
            <option value="product"><?php echo _('Product Name'); ?></option>
        </select>
        <input type="text" class="tb" name="tAutoComplete" id="tAutoComplete" tabindex="2" value="<?php echo ( isset( $_POST['tAutoComplete'] ) ) ? $_POST['tAutoComplete'] : $ad->sku; ?>" placeholder="<?php echo _('Enter SKU'); ?>..." />
        <br /><br />
    </div>

    <div id="dProductPhotos" class="hidden"></div>

    <div id="dCreateAd"<?php if ( !$ad->id ) echo ' class="hidden"'; ?>>
        <h2><?php echo _('Create and Preview Ad'); ?></h2>
        <br />
        <h3><?php echo _('Headlines'); ?>:</h3>
        <br />
        <table>
            <?php
            for ( $i = 0; $i < 10; $i++ ) {
               $headline = ( isset( $ad->headlines[$i] ) ) ? $ad->headlines[$i] : '';
                ?>
                <tr>
                    <td><?php echo $i + 1; ?>)</td>
                    <td><input type="text" class="tb headline" name="tHeadlines[]" id="tHeadline<?php echo $i; ?>" tabindex="<?php echo $i + 3; ?>" value="<?php echo ( isset( $_POST['tHeadlines'][$i] ) ) ? $_POST['tHeadlines'][$i] : $headline; ?>" maxlength="70" /></td>
                </tr>
           <?php } ?>
        </table>

        <br /><br />
        <textarea name="taDescription" id="taDescription" rte="1" tabindex="13"><?php echo ( isset( $_POST['taDescription'] ) ) ? $_POST['taDescription'] : $ad->text; ?></textarea>
        <p>
            <strong><?php echo _('Syntax Tags'); ?>:</strong>
            [<?php echo _('Product Name'); ?>]
            [<?php echo _('Store Name'); ?>]
            [<?php echo _('Store Logo'); ?>]
            [<?php echo _('Category'); ?>]
            [<?php echo _('Brand'); ?>]
            [<?php echo _('Product Description'); ?>]
            [<?php echo _('SKU'); ?>]
            [<?php echo _('Photo'); ?>]
            [<?php echo _('Product Specifications'); ?>]
        </p>
        <label for="tPrice"><strong><?php echo _('Price'); ?>:</strong></label>
        <input type="text" class="tb" name="tPrice" id="tPrice" tabindex="14" value="<?php echo ( isset( $_POST['tPrice'] ) ) ? $_POST['tPrice'] : $ad->price; ?>" />
        <br /><br />

        <label for="sCraigslistMarkets"><strong><?php echo _('Craigslist Markets'); ?>:</strong></label><br />
        <select name="sCraigslistMarkets[]" id="sCraigslistMarkets" tabindex="15" multiple="multiple">
            <?php
            $category_markets = array();
            foreach ( $markets as $market ) {
                if ( !isset( $category_markets[$market->cl_market_id] ) )
                    $category_markets[$market->cl_market_id] = $craigslist_api->get_cl_market_categories( $market->cl_market_id );

                $category = '(No Category)';

                foreach ( $category_markets[$market->cl_market_id] as $cm ) {
                    if ( $cm->cl_category_id == $market->cl_category_id ) {
                        $category = $cm->name;
                        break;
                    }
                }

                $selected = ( in_array( $market->id, $ad->craigslist_markets ) ) ? ' selected="selected"' : '';
                ?>
                <option value="<?php echo $market->id; ?>"<?php echo $selected; ?>><?php echo $market->market, ' / ', $category; ?></option>
            <?php } ?>
        </select>
        <br /><br />

        <input type="submit" class="button" tabindex="16" value="<?php echo _('Save'); ?>" />
        <br /><br />
        <br />
    </div>

    <div id="dPreviewAd"<?php if ( !$craigslist_ad_id ) echo ' class="hidden"'; ?>>
        <h2><?php echo _('Preview'); ?> - &nbsp;<small><a href="#" id="aRefresh" title="<?php echo _('Refresh'); ?>"><?php echo _('Refresh'); ?></a></small></h2>
        <div id="dCraigslistCustomPreview">
            (<?php echo _('Click "Refresh" above to preview your ad'); ?>)
        </div>
        <br />
        <a href="#" class="button" id="aPostAd" title="<?php echo _('Post Ad'); ?>"><?php echo _('Post Ad'); ?></a>
    </div>

    <?php nonce::field('add_edit'); ?>
</form>

<?php echo $template->end(); ?>