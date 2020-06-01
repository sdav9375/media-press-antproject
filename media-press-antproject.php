<?php

/**
 * Plugin Name: AntProject Topics/Projects
<<<<<<< HEAD
 * Plugin URI: http://recuethemes.com
=======
 * Plugin URI: http://buddydev.com
>>>>>>> 1632d957bad861b62516e3393a68cc8e3fc869a4
 * Description: This plugin extends MediaPress to support projects/topics.
 * Version: 1.0.0
 * Author: Rescuethemes
 * Author URI: https://recuethemes.com
 */

// exit if file access directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MPP_Gallery_Helper
 */
class MPP_Gallery_Helper {

	/**
     * Class instance
     *
	 * @var MPP_Gallery_Helper
	 */
	private static $instance = null;

	/**
	 * The constructor.
	 */
	private function __construct() {
	

		$this->setup();
	}

	/**
     * Class instance
     *
	 * @return MPP_Gallery_Helper
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/**
	 * setup the requirements
	 */
	public function setup() {
	
		//add interface to gallery screens
		add_action( 'mpp_after_edit_gallery_form_fields', array( $this, 'add_interface' ) );
		add_action( 'mpp_before_create_gallery_form_submit_field', array( $this, 'add_interface' ) );
		// save meta to gallery
		add_action( 'mpp_gallery_created', array( $this, 'save_gallery_meta' ) );
		add_action( 'mpp_gallery_updated', array( $this, 'save_gallery_meta' ), 100 );
		add_filter( 'bp_attachments_get_max_upload_file_size', array( $this, 'ant_limit_buddypress_file_size') , 10,2 );

			
		// Plugin version
		define( 'AntProject_VER', '1.0' );

		// Plugin path
		define( 'AntProject_DIR', plugin_dir_path( __FILE__ ) );
		
		define( 'Topic_PostType', 'anttopic' ); 

	}
	
	/**
	 * Filter max upload size in Buddypress
	 */
	function ant_limit_buddypress_file_size( $size, $type ) {

    	return 51200000; // 50mb
	} 
 
	/**
	 * Add interface to the gallery
	 */
	public function add_interface() {

		$gallery_id = mpp_get_current_gallery_id();
		$post_meta = get_post_meta( $gallery_id );
		$topics_assigned = $post_meta['topics_assigned'];
	    $produced_time = date('F jS, Y', strtotime($post_meta['date_produced'][0]));

	?>
	
	<div class="mpp-u-1 mpp-gallery-date-produced">
		<label for="mpp-gallery--date-produced"><?php _e( 'Date Produced', 'mediapress' ); ?></label>
		<input type="date" id="mpp-gallery-date-produced" class="mpp-input-1" placeholder="<?php _ex( 'Date Produced', 'Placeholder for project date produced', 'mediapress' ); ?>" name="mpp-gallery-date-produced" value="<?php echo esc_attr( $produced_time ); ?>"/>
	</div>

	<script>
		jQuery(document).ready(function($) {
        $("#mpp-gallery-date-produced").datepicker();
    });

	</script>
		
	<div class="mpp-u-1 mpp-gallery-medium">
		<label for="mpp-gallery-medium"><?php _e( 'Medium', 'mediapress' ); ?></label>
		<input type="text" id="mpp-gallery-medium" class="mpp-input-1" placeholder="<?php _ex( 'Medium', 'Placeholder for project edit medium', 'mediapress' ); ?>" name="mpp-gallery-medium" value="<?php echo esc_attr( $post_meta['medium'][0] ); ?>"/>
	</div>
	<div class="mpp-u-1 mpp-gallery-topic">
		<label for="mpp-gallery-topic"><?php _e( 'Topics', 'mediapress' ); ?></label>
	<?php
		
		$query = new WP_Query( array( 'post_type' => Topic_PostType , 'posts_per_page' => -1 ) );
		$posts = $query->posts;	

		walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $posts ), 0, (object) $args );

		$walker = new AntProject_Walker_Nav_Menu_Checklist( false );
		$args2['selected_topics'] = $topics_assigned;
		$output = '';
		$output .= $walker->walk( $posts, 0, $args2 );
		echo $output; ?>
	</div>
		<?php
	}

	   
	/**
     * Save gallery meta
     *
	 * @param int $gallery_id Gallery id.
	 */
	public function save_gallery_meta( $gallery_id ) {
  
  		$topics = [];
  		
		foreach($_POST['topic'] as $value) {
			$topics[] = (int) $value;
            
        }
        $date = sanitize_text_field( $_POST['mpp-gallery-date-produced'] );
        	
        update_post_meta( $gallery_id, 'date_produced', date( 'Y-m-d', strtotime( $date ) ) );
   		update_post_meta( $gallery_id, 'medium', sanitize_text_field( $_POST['mpp-gallery-medium'] ) );
		update_post_meta( $gallery_id, 'topics_assigned', $topics );
		
	}

}

mpp_gallery_helper();

function mpp_gallery_helper() {
	return MPP_Gallery_Helper::get_instance();
}

 /**
 * Create a Checklist of topics.
 *
 * Borrowed heavily from {@link Walker_Nav_Menu_Checklist}, but modified so as not
 * to require an actual post type or taxonomy, and to force certain CSS classes.
 *
 * @since 1.9.0
 */
class AntProject_Walker_Nav_Menu_Checklist extends Walker_Nav_Menu {

	/**
	 * Constructor.
	 *
	 * @see Walker_Nav_Menu::__construct() for a description of parameters.
	 *
	 * @param array|bool $fields See {@link Walker_Nav_Menu::__construct()}.
	 */
	public function __construct( $fields = false ) {
		if ( $fields ) {
			$this->db_fields = $fields;
		}
	}

	/**
	 * Create the markup to start a tree level.
	 *
	 * @see Walker_Nav_Menu::start_lvl() for description of parameters.
	 *
	 * @param string $output See {@Walker_Nav_Menu::start_lvl()}.
	 * @param int    $depth  See {@Walker_Nav_Menu::start_lvl()}.
	 * @param array  $args   See {@Walker_Nav_Menu::start_lvl()}.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class='children'>\n";
	}

	/**
	 * Create the markup to end a tree level.
	 *
	 * @see Walker_Nav_Menu::end_lvl() for description of parameters.
	 *
	 * @param string $output See {@Walker_Nav_Menu::end_lvl()}.
	 * @param int    $depth  See {@Walker_Nav_Menu::end_lvl()}.
	 * @param array  $args   See {@Walker_Nav_Menu::end_lvl()}.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent</ul>";
	}

	/**
	 * Create the markup to start an element.
	 *
	 * @see Walker::start_el() for description of parameters.
	 *
	 * @param string       $output Passed by reference. Used to append additional
	 *                             content.
	 * @param object       $item   Menu item data object.
	 * @param int          $depth  Depth of menu item. Used for padding.
	 * @param object|array $args   See {@Walker::start_el()}.
	 * @param int          $id     See {@Walker::start_el()}.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_nav_menu_placeholder;

		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
		$possible_object_id = isset( $item->post_type ) && 'nav_menu_item' == $item->post_type ? $item->object_id : $_nav_menu_placeholder;
		$possible_db_id = ( ! empty( $item->ID ) ) && ( 0 < $possible_object_id ) ? (int) $item->ID : 0;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$output .= $indent . '<li>';
		$output .= '<label class="topic-items">';
		
		if ( property_exists( $item, 'label' ) ) {
			$title = $item->label;
		}

		if ( in_array($item->ID, $args['selected_topics'])) {
			$output .= '<input type="checkbox" checked name="topic[]" class="topic-item-checkbox"';
		} else {
			$output .= '<input type="checkbox" name="topic[]" class="topic-item-checkbox"';
		}

		$output .= ' value="'. esc_attr( $item->object_id ) .'" /> ';
		$output .= isset( $title ) ? esc_html( $title ) : esc_html( $item->title );
		$output .= '</label>';

		if ( empty( $item->url ) ) {
			$item->url = $item->guid;
		}
	}
}
            
function ant_filter_notifications_get_registered_components( $component_names = array() ) {
 
    // Force $component_names to be an array
    if ( ! is_array( $component_names ) ) {
        $component_names = array();
    }
 
    // Add 'custom' component to registered components array
    array_push( $component_names, 'ant_project' );
 
    // Return component's with 'custom' appended
    return $component_names;
}
add_filter( 'bp_notifications_get_registered_components', 'ant_filter_notifications_get_registered_components' );

            
function ant_format_buddypress_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
 
    // New custom notifications
    if ( 'topic_created' === $action ) {
    
        $post = get_post( $item_id );
       
        $custom_title = 'The Ant Project posted a new Topic ' . $post->post_title ;
        $custom_link  = get_permalink( $post );
        $custom_text = 'The Ant Project posted a new Topic ' . $post->post_title ;
 
        // WordPress Toolbar
        if ( 'string' === $format ) {
            $return = apply_filters( 'custom_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );
 
        // Deprecated BuddyBar
        } else {
            $return = apply_filters( 'custom_filter', array(
                'text' => $custom_text,
                'link' => $custom_link
            ), $custom_link, (int) $total_items, $custom_text, $custom_title );
        }
        
        return $return;
        
    }
    if ( 'project_comment' === $action ) {
               
                $gallery_id = mpp_activity_get_gallery_id( $item_id  );
                $gallery = mpp_get_gallery( $gallery_id );
                $user_that_commented = ant_get_display_name( $secondary_item_id );
               
               $custom_title = $user_that_commented . 'posted a new comment on' . $gallery->post_title ;
               $custom_link  = bp_activity_get_permalink( $item_id );
               $custom_text = $user_that_commented .'posted a new comment on' . $gallery->post_title ;
        
               // WordPress Toolbar
               if ( 'string' === $format ) {
                   $return = apply_filters( 'custom_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );
        
               // Deprecated BuddyBar
               } else {
                   $return = apply_filters( 'custom_filter', array(
                       'text' => $custom_text,
                       'link' => $custom_link
                   ), $custom_link, (int) $total_items, $custom_text, $custom_title );
               }
               
               return $return;
    }
    
}
add_filter( 'bp_notifications_get_notifications_for_user', 'ant_format_buddypress_notifications', 10, 5 );

function ant_custom_add_notification( $post_id, $post, $update ) {
//     If this is a revision, don't send the email.
    if ( wp_is_post_revision( $post_id ) )
        return;
    if ( $update )
        return;
    
    if ( $post->post_type == 'anttopic' ) {
        $active_users = bp_core_get_users( array("per_page"=>10000));
        
        foreach ( $active_users['users'] as $user ) {
               if ( bp_is_active( 'notifications' ) ) {
        
                    bp_notifications_add_notification( array(
                        'user_id'           => $user->ID,
                        'item_id'           => $post_id,
                        'component_name'    => 'ant_project',
                        'component_action'  => 'topic_created',
                        'date_notified'     => bp_core_current_time(),
                        'is_new'            => 1,
                    ) );
               }
        }
    }
    
}
add_action( 'wp_insert_post', 'ant_custom_add_notification', 99, 3 );

function ant_bp_activity_custom_update($content, $user_id, $activity_id ) {
      
        $project_author = 0;
        //$project_or_media_id = 0;
       
        $gallery_id = mpp_activity_get_gallery_id( $activity_id );
       
        if ( $gallery_id != 0 ) {
             $gallery = mpp_get_gallery( $gallery_id );
             $project_author = $gallery->post_author;
            // $project_or_media_id = $gallery_id;
             
        } else {
             $media_id = mpp_activity_get_media_id( $activity_id );
             $media = mpp_get_media( $media_id );
             $project_author = $media->user_id;
            // $project_or_media_id = $media_id;
        }
        
       
    
//        if ( $gallery ) {
            //$original_activity = new BP_Activity_Activity( $activity_id );
               bp_notifications_add_notification( array(
                   'user_id'           => $project_author, // Owner of the project
                   'item_id'           => $activity_id,
                   'component_name'    => 'ant_project',
                   'component_action'  => 'project_comment',
                   'secondary_item_id' => $user_id, // This is who posted the comment
                   'date_notified'     => bp_core_current_time(),
                   'is_new'            => 1,
               ) );
//        }
}
//add_filter( 'bp_activity_posted_update', 'ant_bp_activity_custom_update',  10, 3 );

