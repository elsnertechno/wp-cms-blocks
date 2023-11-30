<?php
/*
Plugin Name: CMS Blocks
Description: Simple content block which can be used anywhere using Shortcode
Version: 1.1
Author: Elsner Technologies Pvt. Ltd.
Author URI: http://www.elsner.com
*/

add_action( 'init', 'elsner_cms_block_init' );
function elsner_cms_block_init() {
	$labels = array(
		'name'               => _x( 'CMS Blocks', 'post type general name', 'elsnercb' ),
		'singular_name'      => _x( 'CMS Block', 'post type singular name', 'elsnercb' ),
		'menu_name'          => _x( 'CMS Blocks', 'admin menu', 'elsnercb' ),
		'name_admin_bar'     => _x( 'CMS Block', 'add new on admin bar', 'elsnercb' ),
		'add_new'            => _x( 'Add New', 'book', 'elsnercb' ),
		'add_new_item'       => __( 'Add New CMS Block', 'elsnercb' ),
		'new_item'           => __( 'New CMS Block', 'elsnercb' ),
		'edit_item'          => __( 'Edit CMS Block', 'elsnercb' ),
		'view_item'          => __( 'View CMS Block', 'elsnercb' ),
		'all_items'          => __( 'All CMS Blocks', 'elsnercb' ),
		'search_items'       => __( 'Search CMS Blocks', 'elsnercb' ),
		'parent_item_colon'  => __( 'Parent CMS Blocks:', 'elsnercb' ),
		'not_found'          => __( 'No CMS Blocks found.', 'elsnercb' ),
		'not_found_in_trash' => __( 'No CMS Blocks found in Trash.', 'elsnercb' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'exclude_from_search' => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'cms-block' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor')
	);

	register_post_type( 'cms-block', $args );
}

add_filter( 'manage_edit-cms-block_columns', 'edit_cms_block_columns' ) ;
function edit_cms_block_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Block' ),
		'shortcode' => __( 'Shortcode' ),
		'date' => __( 'Date' )
	);

	return $columns;
}

add_action( 'manage_cms-block_posts_custom_column', 'manage_cms_block_columns', 10, 2 );
function manage_cms_block_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {
		case 'shortcode' :
			echo '[cmsblock id="'.$post_id.'"]';
			break;
			
		default :
			break;
	}
}


add_shortcode( 'cmsblock', 'cms_block_func' );

function cms_block_func( $atts ) 
{
	$block = get_post($atts['id']);
	
	if($block)
	{
		return apply_filters('the_content', $block->post_content);
	}
}


// Widget 
class elsnerCMSBlockWidget extends WP_Widget 
{
	function __construct() 
	{
		parent::__construct(
		'elsnerCMSBlock', 
		__('CMS Block', 'elsnercb'), 
		array( 'description' => __( 'Load CMS Block into the sidebar', 'elsnercb' ), ) 
		);
	}

	public function widget( $args, $instance ) 
	{
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		
		if($instance['cmsblockid'] != '')
		{
			$block = get_post($instance['cmsblockid']);
			if($block)
			{
				echo apply_filters('the_content', $block->post_content);
			}
		}
		
		echo $args['after_widget'];
	}
			
	public function form( $instance ) 
	{
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'cms-block',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$cmsblocks = get_posts( $args );

		if ( isset( $instance[ 'title' ] ) ) 
			$title = $instance[ 'title' ];
		else 
			$title = '';
		
		if ( isset( $instance[ 'cmsblockid' ] ) ) 
			$cmsblockid = $instance[ 'cmsblockid' ];
		else 
			$cmsblockid ='';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'elsnercb' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'cmsblockid' ); ?>"><?php _e( 'Block:', 'elsnercb' ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'cmsblockid' ); ?>" name="<?php echo $this->get_field_name( 'cmsblockid' ); ?>">
			<option value=""><?php _e( 'Select Block', 'elsnercb' ); ?></option>
			<?php 
			if(!empty($cmsblocks)){
				foreach($cmsblocks as $val){
					$sel = '';
					if($cmsblockid == $val->ID)
						$sel = 'selected="selected"';
					echo '<option value="'.$val->ID.'" '.$sel.'>'.$val->post_title.'</option>';
				}
			}
			?>
		</select>	
		</p>
		<?php 
	}
		
	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['cmsblockid'] = ( ! empty( $new_instance['cmsblockid'] ) ) ? strip_tags( $new_instance['cmsblockid'] ) : '';
		return $instance;
	}
} 

// Register and load the widget
function elsnerCMSBlock_load_widget() {
	register_widget( 'elsnerCMSBlockWidget' );
}
add_action( 'widgets_init', 'elsnerCMSBlock_load_widget' );

