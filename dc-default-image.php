<?php
/*
 * Plugin Name: DC Default Image
 * Description: Add default image to category
 * Author: Dynamic Creative
 * Author URI: https://www.dynamic-creative.com
 * Version: 1.0.0
 */
class Dc_Default_Image {
    public function __construct() {
	    // Hook into the admin menu
	    add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );	    
	    //
	    add_action( 'admin_init', array( $this, 'setup_sections' ) );
	    add_action( 'admin_init', array( $this, 'setup_fields' ) );
	}

	public function create_plugin_settings_page() {
	    // Add the menu item and page
	    $page_title = 'DC Default Image Settings';
	    $menu_title = 'DC Default Image';
	    $capability = 'manage_options';
	    $slug = 'dc_default_image';
	    $callback = array( $this, 'plugin_settings_page_content' );
	    $icon = 'dashicons-admin-plugins';
	    $position = 100;

	    //add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
	    add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
	}

	public function plugin_settings_page_content() { ?>
	    <div class="wrap">
	        <h2>DC Default Image Settings Page</h2><?php
	        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'dc_default_image' );
                    do_settings_sections( 'dc_default_image' );
                    submit_button();
                ?>
    		</form>
    	</div> <?php
	}

	public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }

	public function setup_sections() {
        add_settings_section( 'our_first_section', '', array( $this, 'section_callback' ), 'dc_default_image' );
        //add_settings_section( 'our_second_section', 'My Second Section Title', array( $this, 'section_callback' ), 'dc_default_image' );
        //add_settings_section( 'our_third_section', 'My Third Section Title', array( $this, 'section_callback' ), 'dc_default_image' );
    }

    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'our_first_section':
    			echo 'Ajoute une <i>image à la une</i>, lorsque celle-ci n\'est pas renseigné, aux articles d\'une catégorie.<br>Indiquer l\'ID de la catégorie et celui de l\'image par défaut que vous souhaitez afficher';
    			break;
    		case 'our_second_section':
    			echo 'This one is number two';
    			break;
    		case 'our_third_section':
    			echo 'Third time is the charm!';
    			break;
    	}
    }

	public function setup_fields() {
	    $fields = array(
	        array(
	            'uid' => 'id_image_field',
	            'label' => 'ID Image',
	            'section' => 'our_first_section',
	            'type' => 'text',
	            'options' => false,
	            'placeholder' => 'ID Image',
	            'helper' => '',
	            'supplemental' => '',
	            'default' => ''
	        ),
	        array(
	            'uid' => 'id_cat_field',
	            'label' => 'ID Catégorie',
	            'section' => 'our_first_section',
	            'type' => 'text',
	            'options' => false,
	            'placeholder' => 'ID Categorie',
	            'helper' => '',
	            'supplemental' => '',
	            'default' => ''
	        )
	    );
	    foreach( $fields as $field ){
	        add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'dc_default_image', $field['section'], $field );
	        register_setting( 'dc_default_image', $field['uid'] );
    	}

	}

	public function field_callback( $arguments ) {
	    $value = get_option( $arguments['uid'] ); // Get the current value, if there is one
	    if( ! $value ) { // If no value exists
	        $value = $arguments['default']; // Set to our default
	    }

	    // Check which type of field we want
	    switch( $arguments['type'] ){
		    case 'text': // If it is a text field
		        printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
		        break;
		    case 'textarea': // If it is a textarea
		        printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
		        break;
		    case 'select': // If it is a select dropdown
		        if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
		            $options_markup = ’;
		            foreach( $arguments['options'] as $key => $label ){
		                $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
		            }
		            printf( '<select name="%1$s" id="%1$s">%2$s</select>', $arguments['uid'], $options_markup );
		        }
		        break;
		}

	    // If there is help text
	    if( $helper = $arguments['helper'] ){
	        printf( '<span class="helper"> %s</span>', $helper ); // Show it
	    }

	    // If there is supplemental text
	    if( $supplimental = $arguments['supplemental'] ){
	        printf( '<p class="description">%s</p>', $supplimental ); // Show it
	    }
	}

	/*public function dcdi_menu() {
	add_options_page( 'DC Default Image Options', 'DC Default Image', 'manage_options', 'dc_default_image', 'dc_default_image' );
	}*/


}
new Dc_Default_Image();

/*
 * front
 */
if (get_option('id_image_field') && get_option('id_cat_field')) {
	function my_filter_thumbnail_id( $thumbnail_id, $post = null ) {
	    $id = $post->ID;
	    $categories = get_the_category($id);
	    if ( wp_attachment_is_image( get_option('id_image_field') ) ) {
		    if ($post->post_type == 'post' && $categories[0]->term_id == get_option('id_cat_field')) {
		        if ( ! $thumbnail_id ) {
		                $thumbnail_id = get_option('id_image_field'); //id of default featured image
	            }
	            return  $thumbnail_id;
	        }
	    }	
	}

	add_filter( 'post_thumbnail_id', 'my_filter_thumbnail_id', 20, 5 );
}

/**
 * Add settings link on plugin page
 */

function dcdi_settings_link($links) { 
	  $settings_link = sprintf('<a href="options-general.php?page=dc_default_image">%s</a>', __("Settings", "dc_default_image")); 
	  array_unshift($links, $settings_link); 
	  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'dcdi_settings_link' );


