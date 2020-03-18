<?php 
/**
 * Create Widget.
 *
 */
class WPALRP_Login_Area extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'WPALRP_Login_Area', 'description' => __( "Display Login or Register button.") );
        parent::__construct('wpalrb-recent-posts', __('Ajax Login or Register'), $widget_ops);
        $this->alt_option_name = 'WPALRP_Login_Area';

        add_action( 'save_post', array($this, 'flush_widget_cache') );
        add_action( 'deleted_post', array($this, 'flush_widget_cache') );
        add_action( 'switch_theme', array($this, 'flush_widget_cache') );
    }

    public function widget($args, $instance) {
        $cache = array();
        if ( ! $this->is_preview() ) {
            $cache = wp_cache_get( 'wpalrb_widget_recent_posts', 'widget' );
        }

        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }

        ob_start();

        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Login or Register' );

        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

?>
<?php echo $args['before_widget']; ?>
<?php if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        } 
		return wpalrp_get_data_from_settings();
		
		echo $args['after_widget']; ?>
<?php

        if ( ! $this->is_preview() ) {
            $cache[ $args['widget_id'] ] = ob_get_flush();
            wp_cache_set( 'wpalrb_widget_recent_posts', $cache, 'widget' );
        } else {
            ob_end_flush();
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $this->flush_widget_cache();

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['WPALRP_Login_Area']) )
            delete_option('WPALRP_Login_Area');

        return $instance;
    }

    public function flush_widget_cache() {
        wp_cache_delete('wpalrb_widget_recent_posts', 'widget');
    }

    public function form( $instance ) {
        $title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
?>

<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>">
    <?php _e( 'Title:' ); ?>
  </label>
  <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<?php
    }
}

function wpalrp_widgets_init() {
    if ( !is_blog_installed() )
        return;

    register_widget('WPALRP_Login_Area');
    do_action( 'widgets_init' );
}

add_action( 'init', 'wpalrp_widgets_init', 2 );
