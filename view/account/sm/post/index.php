<?php

$upload_url = '/website/upload-file/?_nonce=' . nonce::create( 'upload_file' );
$search_url = '/website/get-files/?_nonce=' . nonce::create( 'get_files' );
$delete_url = '/website/delete-file/?_nonce=' . nonce::create( 'delete_file' );

?>

<form method="post" id="post-form" role="form">
    <div class="row-fluid">
        <div class="col-lg-8">
            <section class="panel">
                <header class="panel-heading">
                    Create Post
                </header>

                <div class="panel-body">


                    <div class="form-group">
                        <textarea class="form-control" rows="5" placeholder="Your Message" name="content"></textarea>
                    </div>

                    <div class="form-group">
                        <input type="text" class="form-control" name="link" placeholder="Link"/>
                    </div>

                    <p class="image-selector" id="photo">
                        <img src="//placehold.it/150x150&amp;text=No+Image" />
                        <br>
                        <input type="hidden" name="photo" value="" />
                        <button type="button" class="btn btn-xs btn-default" title="Open Media Manager"
                                data-media-manager
                                data-upload-url="<?php echo $upload_url ?>"
                                data-search-url="<?php echo $search_url ?>"
                                data-delete-url="<?php echo $delete_url ?>"
                                data-image-target="#photo">
                            Select a Photo
                        </button>
                    </p>

                    <div class="datetime-container">
                        <div class="form-group">
                            <input type="text" class="form-control" name="post-at[day]" id="post-at" placeholder="Post now or select date"/>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="post-at[hour]" >
                                <?php for( $i=0; $i<24; $i++ ): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT ) ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT ) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="post-at[minute]" >
                                <?php for( $i=0; $i<60; $i++ ): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT ) ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT ) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <p>
                        <?php nonce::field( 'index' ) ?>
                        <button type="submit" class="btn btn-primary">Post</button>
                    </p>

                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel">
                <header class="panel-heading">
                    Post to Account(s):
                    <div class="pull-right"><a href="/sm/" class="btn btn-primary">Manage Accounts</a></div>
                </header>

                <div class="panel-body">
                    <?php foreach ( $website_sm_accounts as $website_sm_account ): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="website_sm_accounts[<?php echo $website_sm_account->id ?>]" value="<?php echo $website_sm_account->id ?>">
                                <i class="fa fa-<?php echo $website_sm_account->sm ?>"></i> <?php echo $website_sm_account->title ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

    </div>

</form>

<div class="row-fluid">
    <div class="col-lg-12">
        <section class="panel">
            <header class="panel-heading">
                Posts
                <select class="form-control" id="show-posted">
                    <option value="">Scheduled &amp; Posted</option>
                    <option value="1">Only Posted</option>
                    <option value="0">Only Scheduled</option>
                </select>
                <select class="form-control" id="show-account">
                    <option value="">All Social Media Accounts</option>
                    <?php foreach ( $website_sm_accounts as $website_sm_account ): ?>
                        <option value="<?php echo $website_sm_account->id ?>"><?php echo $website_sm_account->title ?></option>
                    <?php endforeach; ?>
                </select>
            </header>
        </section>
    </div>
</div>

<div id="post-list">
</div>
