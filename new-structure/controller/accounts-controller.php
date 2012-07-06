<?php
class AccountsController extends BaseController {
    protected function new_account() {
        //Here I'll prepare the objects, getting HTTP data(GET,POST,etc) and creating objects(filling required data) from it

        $error_message = NULL;
        $this->begin_transaction();
        try {
            /**
             * Here I'll do lots of DB operations, inserts, updates, bla bla bla
             */
            $this->commit(); //If the code reaches this point, everything worked so far, so send it to db
        } catch( ModelException $mex ) {
            $this->rollback(); //Something went wrong, what exactly we don't know, but this does not matter for the user too, then abort every DB operation
            $error_message = _('Some Error message here');
        }

        return new TemplateResponse( '/view/account/new-account.php', $error_message );
    }

}


