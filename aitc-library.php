<?php
/**
* Plugin Name: AITC Library
* Plugin URI: http://oregonaitc.org
* Description: A custom library for AITC resources
* Version: 1.1
* Author: Josh Armentano
* Author URI: http://abidewebdesign.com
**/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require WP_CONTENT_DIR . '/plugins/plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/AbideWebDesign/oregonaitc_library',
	__FILE__,
	'oregonaitc_library'
);
$myUpdateChecker->setBranch('master'); 

// Register the Custom Resource Post Type
 
function register_aitc_library_item() {
 
    $labels = array(
        'name' => _x( 'Resources', 'resource' ),
        'singular_name' => _x( 'Resource', 'resource' ),
        'add_new' => _x( 'Add New', 'resource' ),
        'add_new_item' => _x( 'Add New Resource', 'resource' ),
        'edit_item' => _x( 'Edit Resource', 'resource' ),
        'new_item' => _x( 'New Resource', 'resource' ),
        'view_item' => _x( 'View Resource', 'resource' ),
        'search_items' => _x( 'Search Resources', 'resource' ),
        'not_found' => _x( 'No Resources found', 'resource' ),
        'not_found_in_trash' => _x( 'No Resources found in Trash', 'resource' ),
        'parent_item_colon' => _x( 'Parent Resource:', 'resource' ),
        'menu_name' => _x( 'Resource Library', 'resource' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Resources filterable by category',
        'supports' => array( 'title', 'custom-fields',  'comments', 'revisions', 'page-attributes' ),
        'taxonomies' => array( 'resource_category', 'resource_type' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-book-alt',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
 
    register_post_type('resource', $args);
}
 
add_action('init', 'register_aitc_library_item');

function register_aitc_library_order() {
 
    $labels = array(
        'name' => _x( 'Orders', 'resource_order' ),
        'singular_name' => _x('Order', 'resource_order'),
        'add_new' => _x('Add New', 'resource_order'),
        'add_new_item' => _x('Add New Order', 'resource_order'),
        'edit_item' => _x('Edit Order', 'resource_order'),
        'new_item' => _x('New Order', 'resource_order'),
        'view_item' => _x('View Orders', 'resource_order'),
        'search_items' => _x('Search Orders', 'resource_order'),
        'not_found' => _x( 'No Orders found', 'resource_order'),
        'not_found_in_trash' => _x('No Orders found in Trash', 'resource_order'),
        'parent_item_colon' => _x('Parent Orders:', 'resource_order'),
        'menu_name' => _x('Orders', 'resource_order'),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Orders filterable by category',
        'supports' => array( 'title', 'custom-fields', 'page-attributes' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-book-alt',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
 
    register_post_type('resource_order', $args);
}
add_action('init', 'register_aitc_library_order');

function resource_category_taxonomy() {
    register_taxonomy(
        'resource_category',
        'resource',
        array(
            'hierarchical' => true,
            'label' => 'Resource Categories',
            'query_var' => true,
            'show_admin_column' => true,
            'rewrite' => array(
                'slug' => 'resource_category',
                'with_front' => false
            )
        )
    );
}
add_action( 'init', 'resource_category_taxonomy');

function resource_type_taxonomy() {
    register_taxonomy(
        'resource_type',
        'resource',
        array(
            'hierarchical' => true,
            'label' => 'Resource Type',
            'query_var' => true,
            'show_admin_column' => true,
            'rewrite' => array(
                'slug' => 'resource_type',
                'with_front' => false
            )
        )
    );
}
add_action( 'init', 'resource_type_taxonomy');

function submit_library_order() {
		$current_user = wp_get_current_user();
			
		// Send email notification
		$message = "<h2>Library Order Details</h2><p><strong>Library User:</strong><br>" 
					. $current_user->user_firstname 
					. " " 
					. $current_user->user_lastname
					. "<br>" 
					. $current_user->school
					. "<br><br>"
					. $current_user->addr1
					. " "
					. $current_user->addr2
					. "<br>" 
					. $current_user->city
					. ", "
					. $current_user->thestate
					. " "
					. $current_user->zip
					. "<br><br>"
					. $current_user->user_email
					. "<br>"
					. $current_user->phone1								
					. "</p><p><strong>Comments</strong><br>"
					. $_POST['comment']
					. "</p>"
					. "<table border='0' cellpadding='10' style='border: 1px solid #ccc;' cellpadding=10>
						<tr>
							<td><strong>Resource Name</strong></td>
							<td><strong>Quantity</strong></td>
							<td><strong>Link</strong></td>
						</tr>";
		$headers = array('Content-Type: text/html; charset=UTF-8');
		
		// Create Order Var
		$order = $message;
		
		// Run through order
		foreach($_SESSION['cart'] as $id=>$value) {
			$resource = get_field_object('resource_name', $id);
			$name = $resource['value'];
			$link = get_permalink( $id );				
			
			// Update quantity available and checkout total
			$available = get_field_object('total_available', $id);
			$a = $available['value'] - 1;
			
			$total = get_field_object('checked_out_total', $id);
			$types = get_the_terms($id, 'resource_type');
			$quantity = 1;
			
			foreach($types as $type) {
				if ($type->name == "Kits") {
					$t = $total['value'] + $_POST['q'.$id];
					$quantity = $_POST['q'.$id];
				} else {
					$t = $total['value'] + 1;
				}
			}
			
			$usertotal = get_user_meta($current_user->ID, 'total_library_checkouts'); 
			$ut = (int)$usertotal + 1;
			
			update_field('total_available', $a, $id);
			update_field('checked_out_total', $t, $id);
			
			// Update user checkout total
			update_user_meta($current_user->ID, 'total_library_checkouts', $ut);
			
			// Add to email
			$message .= 
			"
			<tr>
				<td>$name</td><td>$quantity</td><td><a href='$link' target='_blank'>Resource Link</a></td>
			</tr>
			";
			
			// Update order
			$order .= $message;
		}
		
		// Send notification
		wp_mail( 'josh@abidewebdesign.com', 'Library Hold Placed', $message, $headers);
		
		// Create new Order post		
		$post_data = array(
			'post_type'     => 'resource_order',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'post_title'    => 'temp',
		);
		
		// Insert the post into Wordpress
	 	$post_id = wp_insert_post($post_data, true);
	 	
		if($post_id) {		
			$title = 'Order #' . $post_id;
			update_post_meta($post_id, 'post_title', $title);
			update_field('details', $order, $post_id); 
			update_field('user', $current_user, $post_id);
		} else {
			$error = true;
		}
}
?>