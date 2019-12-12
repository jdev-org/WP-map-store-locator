<?php
class BaseWidgets extends WP_Widget
{
   
    public $shortCodeId;

    public $registerWidget = false;

    /**
     * CONSTRUCTOR
     */
    function __construct($id, $name, $description, $textdomain) {
        parent::__construct(
            $id,
            esc_html__( $name,  $textdomain ),
            array( 'description' => esc_html__( $description,  $textdomain ))
        );
    } 

    /**
     * REGISTER
     * Register widget into wordpress system.
     */
    function register() {
        /*if($this->registerWidget) {
            add_action('widgets_init', array( $this, 'widgetInit' ) );
        }*/
        add_action('widgets_init', array( $this, 'widgetInit' ) );
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
        print_r($instance);
        $mapCenterXY = $instance['mapCenterXY'];
        $this->htmlComponent($instance);
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
    function htmlComponent($instance) {
        ?>
            <div>Basic Widget!</div>
        <?php
    }

    /**
     * WIDGET ADMIN FORM 
    */
    /*public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
             $title = $instance[ 'title' ];
        } else {
            $title = "Titre";
        }
        ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
        <?php
    }
    public function update( $new_instance, $old_instance ) {

        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }*/

    function form($instance) {
        if ( isset( $instance[ 'title' ] ) ) {
            $mapCenterXY = $instance[ 'title' ];
        } else {
            $mapCenterXY = "1,1";
        }
        ?>      
            <p>
                <label for="<?php esc_attr( $this->get_field_id( 'title' ) ); ?>">Center: </label>
                <input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php esc_attr( $mapCenterXY ); ?>" />
            </p>            
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}
