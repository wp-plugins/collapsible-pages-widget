<?php
/**
 * Plugin Name: Collapsible Pages Widget
 * Plugin URI: https://github.com/adamschoenemann/collapsible-pages-widget
 * Description: A brief description of the plugin.
 * Version: 1.0.6
 * Author: Adam Schønemann
 * Author URI: https://github.com/adamschoenemann/
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
		echo $this->print_pages_recursive($pages, array(
				'show_threshold' => 0,
				'color' => $instance['color']
			)
		)->toHtml(true);
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
		$title = isset($instance['title']) ? $instance['title'] : "New title";
		$color = isset($instance['color']) ? $instance['color'] : '#fff';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo 'Title:'; ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">

			<label for="<?php echo $this->get_field_id( 'color' ); ?>"><?php echo 'Color:'; ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'color' ); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" type="text" value="<?php echo esc_attr( $color ); ?>">
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
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['color'] = (!empty($new_instance['color'])) ? strip_tags($new_instance['color']) : '';

		return $instance;
	}


	private function get_pages_with_parent($id, $columns = array()) {
		global $wpdb;
		$colstring = implode($columns, ',');
		$pages = get_pages(array(
			'parent' => $id
		));
		// print_r($pages);
		// $pages = $wpdb->get_results(
		// 	"SELECT ID, " . $colstring . " FROM $wpdb->posts WHERE post_type='page' AND post_parent={$id}"
		// );
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
				'class' => array('page_item', 'page-item-' . $page->ID),
				'data-page-id' => $page->ID
			));
			$ul->addChild($li);
			$toggle_item = new Node('div', array('class' => 'toggle-item'));
			$li->addChild($toggle_item);
			$a = new Node('a', array(
					'href' => get_page_link($page->ID)
				)
			);
			$a->addText($page->post_title);
			$li->addChild($a);
			if(isset($page->children)) {
				$li->addClass('page_item_has_children');

				$plus_toggle = new Node('span', array('class' => 'toggle icon-plus'));
				$plus_svg = new Node('svg');
				$plus_svg->addText('
					<rect rx="1" id="svg_2" height="25%" width="100%" y="37.5%" x="0%" fill="' . $options['color'] . '"/>
					<rect rx="1" id="svg_3" height="100%" width="25%" y="0%" x="37.5%"  fill="' . $options['color'] . '"/>
				');
				$plus_toggle->addChild($plus_svg);

				$minus_toggle = new Node('span', array('class' => 'toggle icon-minus hidden'));
				$minus_svg = new Node('svg');
				$minus_svg->addText('
					<rect rx="1" id="svg_1" height="25%" width="100%" y="37.5%" x="0%" fill="' . $options['color'] . '" />
				');
				$minus_toggle->addChild($minus_svg);

				$toggle_item->addChild($plus_toggle);
				$toggle_item->addChild($minus_toggle);

				$childUl = $this->print_pages_recursive($page->children, $options, $level + 1);
				$childUl->addClass('children');
				$li->addChild($childUl);
			}
		}

		return $ul;
	}

}

add_action('wp_enqueue_scripts', function(){
	wp_enqueue_style('cpw-style', plugins_url('style.css', __FILE__));
	wp_enqueue_script('cpw-js', plugins_url('script.js', __FILE__), array('jquery'));
});


add_action('widgets_init', function(){
	register_widget('CollapsiblePagesWidget');
});
