<?php
class TestController extends BaseController {
    /**
     * Setup the base for creating template responses
     */
    public function __construct() {
        // Pass in the base for all the views
        parent::__construct();

        // Tell what is the base for all login
        $this->view_base = 'test/';
    }

    /**
     * List Accounts
     *
     *
     * @return TemplateResponse
     */
    protected function index() {
        $account = new Account();

        library('sendgrid-api');

        // Get accounts with email marketing
        $accounts = $account->get_results('SELECT w.*, u.`email`, u.`contact_name`, COALESCE( u.`work_phone`, u.`cell_phone` ) AS phone FROM `websites` AS w LEFT JOIN `users` AS u ON ( u.`user_id` = w.`user_id` ) WHERE w.`status` = 1 AND w.`email_marketing` = 1 AND `website_id` <> 96', PDO::FETCH_CLASS, 'Account' );

        /**
         * @var Account $account
         */
        foreach ( $accounts as $account ) {
            $sendgrid = new SendGridAPI( $account );
            $sendgrid->setup_subuser();

            $username = format::slug( $account->title );

            $password = substr( $account->id . md5(microtime()), 0, 10 );
            list( $first_name, $last_name ) = explode( ' ', $account->contact_name, 2 );

            $settings = $account->get_settings( 'address', 'city', 'state', 'zip' );
            $phone = ( empty( $account->phone ) ) ? '8185551234' : $account->phone;
            $sendgrid->subuser->add( $username, $password, $account->email, $first_name, $last_name, $settings['address'], $settings['city'], $settings['state'], $settings['zip'], 'USA', $phone, $account->domain, $account->title );

            $account->set_settings( array( 'sendgrid-username' => $username, 'sendgrid-password' => $password ) );
        }

        /*
        library('Excel_Reader/Excel_Reader');
        $er = new Excel_Reader();
        // Set the basics and then read in the rows
        $er->setOutputEncoding('ASCII');
        $er->read( ABS_PATH . 'temp/map-price-list.xls' );

        $rows = array_slice( $er->sheets[0]['cells'], 3 );

        foreach ( $rows as $row ) {
            break;
            $product = new Product();
            $product->get_by_sku( $row[3] );
            if ( $product->id ) {
                $product->price = $row[15];
                fn::info( $product );exit;
                $product->save();
            }
        }*/

        return new HtmlResponse( 'heh' );
    }
}