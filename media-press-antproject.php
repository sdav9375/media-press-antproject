<?php

/**
 * Plugin Name: AntProject Topics/Projects
 * Plugin URI: http://buddydev.com
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
		
			
		// Plugin version
		define( 'AntProject_VER', '1.0' );

		// Plugin path
		define( 'AntProject_DIR', plugin_dir_path( __FILE__ ) );
		
		define( 'Topic_PostType', 'anttopic' ); 


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
