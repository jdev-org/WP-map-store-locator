<?php
class BaseWidgets extends WP_Widget
{
    public $options;

    public $widget_ID;

    public $id;

	public $widget_name;

	public $widget_options = array();

    public $control_options = array();
    
    public $shortCodeId;

    public $registerWidget = false;

    /**
     * CONSTRUCTOR
     */
    function __construct($id, $name, $options, $isRegister) {
        $this->registerWidget = $isRegister;
        $this->widget_ID = $id;
        $this->widget_name = $name;
        $this->widget_options = array(
            'classname' => $this->widget_ID,
            'description' => $this->widget_name,
            'customize_selective_refresh' => true
        );
        if ($options) {
            $this->controls_options = $options;
        }
    }

    /**
     * REGISTER
     * Register widget into wordpress system.
     */
    function register() {
        parent::__construct( $this->widget_ID, $this->widget_name, $this->widget_options, $this->control_options );        
        if($this->registerWidget) {
            add_action('widgets_init', array( $this, 'widgetInit' ) );
        }
        $this->setId(null);
    }

    /**
     * INIT
     * Initialize widget.
     */
    function widgetInit() {
        register_widget( $this );
    }

    /**
     * WIDGET
     * Return content display by widget.
     * Ex : HTML code or full HTML page.
     */
    function widget( $args, $instance) {
        echo $args['before_widget'];
        $this->htmlComponent();
        echo $args['after_widget'];
    }

    /**
     * SHORTCODE
     */
    function getShortCodeAction() {
        $this->htmlComponent();
    }

    function setShortCodeId($id) {
        $this->shortCodeId = $id;
    }

    function initShortCode($id, $methodName) {
        $this->setShortCodeId($id);
        add_shortcode($id, array($this,$methodName));  
    }    

    /**
     * ID
     */
    function setId() {
        $this->id = uniqid();
    }

    function getId() {
        return $this->id;
    }

    /**
     * HTML COMPONENT
     * Return by widget() function.
     */
    function htmlComponent() {
        ?>
            <div>Basic Widget!</div>
        <?php
    }

    /**
     * WIDGET ADMIN FORM 
    */
    public function form($instance) {
        return $instance;
    }

    /**
     * UPDATE
     * Usefull to compare or realize action on instance update.
     * Trigger on widget form modifications.
    */
    public function update( $new_instance, $old_instance) {
        $instance = $old_instance;        
        return $instance;
    }
}