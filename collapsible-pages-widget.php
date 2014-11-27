<?php
/**
 * Plugin Name: Collapsible Pages Widget
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: A brief description of the plugin.
 * Version: 0.0.1
 * Author: Adam Schønemann
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: GPL2
 */

defined('ABSPATH') or die("No script kiddies please!");

require dirname(__FILE__) . '/Node.php';

class CollapsiblePagesWidget extends WP_Widget
{
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'collapsible_pages_widget',
			'Collapsible Pages',
				array('description' => 'For creating a list of pages that is collapsible')
			);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		$pages = $this->get_pages_recursive(0, array('post_title'));
		echo $this->print_pages_recursive($pages, array('show_threshold' => 0))->toHtml(true);
		echo '
			<script>
				jQuery(document).on("collapsible_pages_ready",function(){
					expand_to_page(' . get_the_ID() . ')
				});
				console.log("hey");
			</script>
		';
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form($instance) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = 'New title';
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo 'Title:'; ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
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
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}


	private function get_pages_with_parent($id, $columns = array()) {
		global $wpdb;
		$colstring = implode($columns, ',');
		$pages = $wpdb->get_results(
			"SELECT ID, " . $colstring . " FROM wp_posts WHERE post_type='page' AND post_parent={$id}"
		);
		return $pages;
	}

	private function get_pages_recursive($id = 0, $columns = array()) {
		$pages = $this->get_pages_with_parent($id, $columns);

		foreach($pages as $page) {
			$children = $this->get_pages_recursive($page->ID, $columns);
			if(count($children) > 0)
				$page->children = $children;
		}
		return $pages;
	}

	private function print_pages_recursive($pages, $options, $level = 0) {
		$ul = new Node('ul');
		if($level > $options["show_threshold"])
			$ul->addClass('hidden');

		foreach ($pages as $page) {

			$li = new Node('li', array(
				'class' => array('page_item', 'page-item-' . $page->ID)
			));
			$ul->addChild($li);
			$img = new Node('img', array('class' => 'toggle-item'));
			$li->addChild($img);
			$a = new Node('a', array(
					'href' => get_page_link($page->ID)
				)
			);
			$a->addText($page->post_title);
			$li->addChild($a);
			if(isset($page->children)) {
				$li->addClass('page_item_has_children');
				$img->addClass('toggle icon-plus');
				$img->addAttribute('src', plugin_dir_url(__FILE__) . 'images/icon-plus.svg');
				$childUl = $this->print_pages_recursive($page->children, $options, $level + 1);
				$childUl->addClass('children');
				$li->addChild($childUl);
			} else {
				$img->addAttribute('style', 'visibility:hidden;');
			}
		}

		return $ul;

		/*
		$ulClasses = array();
		if($level > $options["show_threshold"])
			$ulClasses[] = 'hidden';
		if($level > 0)
			$ulClasses[] = 'children';

		$out = array('<ul class="' . implode($ulClasses, ' ') . '">');
		foreach ($pages as $page) {
			$hasChildren = ($page->children !== null ? true : false);
			$liClasses = array("page_item", "page-item-" . $page->ID);
			if($hasChildren) $liClasses[] = 'page_item_has_children';
			$out[] = '<li class="' . implode($liClasses, ' ') . '" data-page-id="' . $page->ID . '">';
			$out[] = '<img ';
			$imgClasses = array('toggle-item');
			if ($hasChildren) {
				$imgClasses[] = 'toggle icon-plus';
				$out[] = 'src="' . plugin_dir_url(__FILE__) . 'images/icon-plus.svg" ';
			} else {
				$out[] = 'style="visibility:hidden" ';
			}

			$out[] = 'class="' . implode($imgClasses, ' ');
			$out[] = '">';
			$out[] = '</img>';

			$out[] = '<a href="' . get_page_link($page->ID) . '">' . $page->post_title . '</a>';
			if($hasChildren) {
				// $out[] = '<span class="toggle-plus toggle"></span>';
				$out[] = $this->print_pages_recursive($page->children, $options, $level + 1);
			}
			$out[] = "</li>";
		}
		$out[] = "</ul>";
		return implode($out, '');
		*/
	}

}

add_action('wp_enqueue_scripts', function(){
	wp_enqueue_style('cpw-style', plugins_url('style.css', __FILE__));
	wp_enqueue_script('cpw-js', plugins_url('script.js', __FILE__), array('jquery'));
});


add_action('widgets_init', function(){
	register_widget('CollapsiblePagesWidget');
});
