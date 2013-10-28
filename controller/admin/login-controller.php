<?php
class LoginController extends BaseController {
    /**
     * Setup the base for creating template responses
     */
    public function __construct() {
        parent::__construct( );

        // Tell what is the base for all login
        $this->view_base = 'login/';
        $this->section = 'Login';
    }

    /**
     * Login Page
     *
     * @return CustomResponse|RedirectResponse
     */
    protected function index() {
        // Get the template response
        $custom_response = new CustomResponse( $this->resources, $this->view_base . 'index', _('Login') );

        $v = new Validator( 'fLogin' );
        $v->add_validation( 'email', 'email', _('The "Email" field must contain a valid email address') );

        $errs = false;
        $validation = $v->js_validation();

        // If posted
        if ( $this->verified() && !stristr( $_POST['referer'], '//' )  ) {
            $errs = $v->validate();

            if ( empty( $errs ) ) {
                // Try to login
                if ( $this->user->login( $_POST['email'], $_POST['password'] ) ) {
                    // Record the login
                    $this->user->record_login();

                    // Two Weeks : Two Days
                    $expiration = ( isset( $_POST['remember-me'] ) ) ? 1209600 : 172800;
                    set_cookie( AUTH_COOKIE, base64_encode( security::encrypt( $this->user->email, security::hash( COOKIE_KEY, 'secure-auth' ) ) ), $expiration );

                    if( !isset( $_POST['referer'] ) || isset( $_POST['referer'] ) && empty( $_POST['referer'] ) ) {
                        return new RedirectResponse('/');
                    } else {
                        return new RedirectResponse( $_POST['referer'] );
                    }
                } else {
                    $errs .= _('Your email and password do not match. Please try again.');
                }
            }
        }

        $custom_response->set( compact( 'errs', 'validation' ) );

        return $custom_response;
    }

    /**
     * Override login function
     * @return bool
     */
    protected function get_logged_in_user() {
        $this->user = new User();
        return true;
    }
}


