<?php
/**
 * This will return the HTML response for templates
 */
class TemplateResponse extends Response {
    /**
     * Hold the View file that we will be including
     * @var string
     */
    private $file_to_render = NULL;
    /**
     * Hold the errors that we will be including
     * @var array
     */
    private $errors = array();

    /**
     * Pass in which file will be the View
     *
     * @param string $file_to_render
     */
    public function __construct( $file_to_render ) {
        $this->file_to_render = $file_to_render;
    }

    /**
     * Check to see if we have an error
     * @return bool
     */
    protected function has_error() {
        return count( $this->errors ) > 0;
    }

    /**
     * Add Error
     *
     * @param string $error
     */
    public function add_error( $error ) {
        $this->errors[] = _($error);
    }

    /**
     * Get Errors
     *
     * @return array
     */
    protected function get_errors() {
        return $this->errors;
    }

    /**
     * Including the file
     */
    public function respond() {
        // Define defaults for resources
        $resources = new Resources();

        require VIEW_PATH . 'header.php';
        require VIEW_PATH . $this->file_to_render . '.php';
        require VIEW_PATH . 'footer.php';
    }

}
