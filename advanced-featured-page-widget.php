<?php
/*
Plugin Name: Advanced Featured Page Widget
Plugin URI:
Description: Add a featured page in the sidebar using a widget.
Version: 1.2
Author: Jeremy Murimi
Author URI:
License: A "Slug" license name e.g. GPL2
*/
?>
<?php


class advanced_featured_page_widget extends WP_Widget {
protected $defaults;
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'advanced-featured-page-widget', // Base ID
			'Advanced-Featured-Page-Widget', // Name
			array( 'description' => __( 'A Foo Widget', 'text_domain' ), ) // Args
		);
				$this->defaults = array(
			'title'           => '',
			'page_id'         => '',
			'show_image'      => 0,
			'image_alignment' => '',
			'image_size'      => '',
			'show_title'      => 0,
			'show_byline'     => 0,
			'show_content'    => 0,
			'content_limit'   => '',
			'more_text'       => '',
		);
				$control_ops = array(
			'width'   => 200,
			'height'  => 250,
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
		public function widget( $args, $instance ) {

		extract( $args );

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo $before_widget;

		/** Set up the author bio */
		if ( ! empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

		$featured_page = new WP_Query( array( 'page_id' => $instance['page_id'] ) );
		if ( $featured_page->have_posts() ) : while ( $featured_page->have_posts() ) : $featured_page->the_post();
			echo '<div class="' . implode( ' ', get_post_class() ) . '">';

			if ( ! empty( $instance['show_image'] ) )
				printf(
					'<a href="%s" title="%s" class="%s">%s</a>',
					get_permalink(),
					the_title_attribute( 'echo=0' ),
					esc_attr( $instance['image_alignment'] ),
					get_image( array( 'format' => 'html', 'size' => $instance['image_size'], ) )
				);

			if ( ! empty( $instance['show_title'] ) )
				printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );

			if ( ! empty( $instance['show_byline'] ) ) {
				echo '<p class="byline">';
				the_time( 'F j, Y' );
				echo ' ' . __( 'by', 'text_domain' ) . ' ';
				the_author_posts_link();
				echo g_ent( ' &middot; ' );
				comments_popup_link( __( 'Leave a Comment', 'text_domain' ), __( '1 Comment', 'text_domain' ), __( '% Comments', 'text_domain' ) );
				echo ' ';
				edit_post_link( __( '(Edit)', 'text_domain' ), '', '' );
				echo '</p>';
			}

			if ( ! empty( $instance['show_content'] ) ) {
				if ( empty( $instance['content_limit'] ) )
					the_content( $instance['more_text'] );
				else
					the_content_limit( (int) $instance['content_limit'], esc_html( $instance['more_text'] ) );
			}

			echo '</div><!--end post_class()-->' . "\n\n";

			endwhile;
		endif;

		echo $after_widget;
		wp_reset_query();

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
		function update( $new_instance, $old_instance ) {

		$new_instance['title']     = strip_tags( $new_instance['title'] );
		$new_instance['more_text'] = strip_tags( $new_instance['more_text'] );
		return $new_instance;

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'text_domain' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'page_id' ); ?>"><?php _e( 'Page', 'text_domain' ); ?>:</label>
			<?php wp_dropdown_pages( array( 'name' => $this->get_field_name( 'page_id' ), 'selected' => $instance['page_id'] ) ); ?>
		</p>

		<hr class="div" />

		<p>
			<input id="<?php echo $this->get_field_id( 'show_image' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_image' ); ?>" value="1"<?php checked( $instance['show_image'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_image' ); ?>"><?php _e( 'Show Featured Image', 'text_domain' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size', 'text_domain' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
				<option value="thumbnail">thumbnail (<?php echo get_option( 'thumbnail_size_w' ); ?>x<?php echo get_option( 'thumbnail_size_h' ); ?>)</option>
				<?php
				$sizes = advanced_page_widget_get_additional_image_sizes();
				foreach ( (array) $sizes as $name => $size )
					echo '<option value="' . $name . '" ' . selected( $name, $instance['image_size'], FALSE ) . '>' . $name . ' (' . $size['width'] . 'x' . $size['height'] . ')</option>';
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'image_alignment' ); ?>"><?php _e( 'Image Alignment', 'text_domain' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'image_alignment' ); ?>" name="<?php echo $this->get_field_name( 'image_alignment' ); ?>">
				<option value="alignnone">- <?php _e( 'None', 'text_domain' ); ?> -</option>
				<option value="alignleft" <?php selected( 'alignleft', $instance['image_alignment'] ); ?>><?php _e( 'Left', 'text_domain' ); ?></option>
				<option value="alignright" <?php selected( 'alignright', $instance['image_alignment'] ); ?>><?php _e( 'Right', 'text_domain' ); ?></option>
			</select>
		</p>

		<hr class="div" />

		<p>
			<input id="<?php echo $this->get_field_id( 'show_title' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_title' ); ?>" value="1"<?php checked( $instance['show_title'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Page Title', 'text_domain' ); ?></label>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'show_byline' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_byline' ); ?>" value="1"<?php checked( $instance['show_byline'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_byline' ); ?>"><?php _e( 'Show Page Byline', 'text_domain' ); ?></label>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'show_content' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_content' ); ?>" value="1"<?php checked( $instance['show_content'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_content' ); ?>"><?php _e( 'Show Page Content', 'text_domain' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'content_limit' ); ?>"><?php _e( 'Content Character Limit', 'text_domain' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'content_limit' ); ?>" name="<?php echo $this->get_field_name( 'content_limit' ); ?>" value="<?php echo esc_attr( $instance['content_limit'] ); ?>" size="3" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'more_text' ); ?>"><?php _e( 'More Text', 'text_domain' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'more_text' ); ?>" name="<?php echo $this->get_field_name( 'more_text' ); ?>" value="<?php echo esc_attr( $instance['more_text'] ); ?>" />
		</p>
		<?php

	}

} // get additional image sizes define in you function.php file use add_image_size() to define image sizes
function advanced_page_widget_get_additional_image_sizes() {

	global $_wp_additional_image_sizes;

	if ( $_wp_additional_image_sizes )
		return $_wp_additional_image_sizes;

	return array();

}
function get_image( $args = array() ) {

	global $post;

	$defaults = array(
		'format'	=> 'html',
		'size'		=> 'full',
		'num'		=> 0,
		'attr'		=> '',
	);

	$defaults = apply_filters( 'get_image_default_args', $defaults );

	$args = wp_parse_args( $args, $defaults );

	/** Allow child theme to short-circuit this function */
	$pre = apply_filters( 'pre_get_image', false, $args, $post );
	if ( false !== $pre )
		return $pre;

	/** Check for post image (native WP) */
	if ( has_post_thumbnail() && ( 0 === $args['num'] ) ) {
		$id = get_post_thumbnail_id();
		$html = wp_get_attachment_image( $id, $args['size'], false, $args['attr'] );
		list( $url ) = wp_get_attachment_image_src( $id, $args['size'], false, $args['attr'] );
	}
	/** Else pull the first (default) image attachment */
	else {
		$id = get_image_id( $args['num'] );
		$html = wp_get_attachment_image( $id, $args['size'], false, $args['attr'] );
		list( $url ) = wp_get_attachment_image_src( $id, $args['size'], false, $args['attr'] );
	}

	/** Source path, relative to the root */
	$src = str_replace( home_url(), '', $url );

	/** Determine output */
	if ( 'html' === strtolower( $args['format'] ) )
		$output = $html;
	elseif ( 'url' === strtolower( $args['format'] ) )
		$output = $url;
	else
		$output = $src;

	// Return FALSE if $url is blank
	if ( empty( $url ) ) $output = false;

	/** Return FALSE if $src is invalid (file doesn't exist) */
//	if ( ! file_exists( ABSPATH . $src ) )
//		$output = false;

	/** Return data, filtered */
	return apply_filters( 'get_image', $output, $args, $id, $html, $url, $src );
}
function get_image_id( $index = 0 ) {

	global $post;

	$image_ids = array_keys(
		get_children(
			array(
				'post_parent'    => $post->ID,
				'post_type'	     => 'attachment',
				'post_mime_type' => 'image',
				'orderby'        => 'menu_order',
				'order'	         => 'ASC',
			)
		)
	);

	if ( isset( $image_ids[$index] ) )
		return $image_ids[$index];

	return false;

}
function the_content_limit( $max_characters, $more_link_text = '(more...)', $stripteaser = false ) {

	$content = get_the_content_limit( $max_characters, $more_link_text, $stripteaser );
	echo apply_filters( 'the_content_limit', $content );

}
function get_the_content_limit( $max_characters, $more_link_text = '(more...)', $stripteaser = false ) {

	$content = get_the_content( '', $stripteaser );

	/** Strip tags and shortcodes so the content truncation count is done correctly */
	$content = strip_tags( strip_shortcodes( $content ), apply_filters( 'get_the_content_limit_allowedtags', '<script>,<style>' ) );

	/** Inline styles / scripts */
	$content = trim( preg_replace( '#<(s(cript|tyle)).*?</\1>#si', '', $content ) );

	/** Truncate $content to $max_char */
	$content = truncate_phrase( $content, $max_characters );

	/** More link? */
	if ( $more_link_text ) {
		$link   = apply_filters( 'get_the_content_more_link', sprintf( '%s <a href="%s" class="more-link">%s</a>', '', get_permalink(), $more_link_text ), $more_link_text );
		$output = sprintf( '<p>%s %s</p>', $content, $link );
	} else {
		$output = sprintf( '<p>%s</p>', $content );
	}

	return apply_filters( 'get_the_content_limit', $output, $content, $link, $max_characters );

}
function truncate_phrase( $text, $max_characters ) {

	$text = trim( $text );

	if ( strlen( $text ) > $max_characters ) {
		/** Truncate $text to $max_characters + 1 */
		$text = substr( $text, 0, $max_characters + 1 );

		/** Truncate to the last space in the truncated string */
		$text = trim( substr( $text, 0, strrpos( $text, ' ' ) ) );
	}

	return $text;
}
// Additional image sizes
add_image_size( '50 by 50 image',  50, 50, true );
add_image_size( '100 by 100 image',  100, 100, true );
?>
<?php
// register advanced_featured_page_widget  widget
/*add_action( 'widgets_init', function(){
     return register_widget( 'advanced_featured_page_widget' );
});*/
add_action( 'widgets_init', create_function( '', 'register_widget( "advanced_featured_page_widget" );' ) ); 
?>