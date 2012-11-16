<?php
class ResourcesController extends BaseController {
    /**
     * Setup the base for creating template responses
     */
    public function __construct() {
        parent::__construct( );
    }

    /**
     * Handle CSS
     *
     * @return CssResponse
     */
    protected function css() {
        return new CssResponse( $_GET['f'] );
    }

    /**
     * Handle CSS Single File
     *
     * @return CssResponse
     */
    protected function css_single() {
        return new CssResponse( $this->resources->get_css_file( $_GET['f'] ) );
    }

    /**
     * Handle JS
     *
     * @return JavascriptResponse
     */
    protected function js() {
        return new JavascriptResponse( $_GET['f'] );
    }

    /**
     * Handle single JS
     *
     * @return JavascriptResponse
     */
    protected function js_single() {
        return new JavascriptResponse( $this->resources->get_javascript_file( $_GET['f'] ) );
    }

    /**
     * Need different things for an image
     *
     * @return bool
     */
    protected function image() {
        return new ImageResponse( $_SERVER['REQUEST_URI'] );
    }

    /**
     * Override login function
     * @return bool
     */
    protected function get_logged_in_user() {
        return true;
    }
}

