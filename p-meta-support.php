<?php
/** 
 * Plugin Name: Plife Nectar Metabox Support 
 * Description: This plugin as a bridge between Nectar Metabox and Custom Type Post page. 
 * Version: 1.0 
 * Author: Plife 
 * Author URI: https://plife.se 
 * License: GPL v2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt */

if ( !defined("ABSPATH") ) {
    exit;
}

class PlifeNectarMetaBoxSupport
{
    public function __construct()
    {

        add_filter('nectar_metabox_post_types_page_header', array($this, 'my_cptui_add_post_types_to_nectar_metabox_post'));
        add_filter('nectar_metabox_post_types_fullscreen_rows', array($this, 'my_cptui_add_post_types_to_nectar_metabox_post'));
        add_filter('nectar_metabox_post_types_navigation_transparency', array($this, 'my_cptui_add_post_types_to_nectar_metabox_post'));
        
        add_action("wp_head", array($this, "hide_navigation"));
        add_action('admin_enqueue_scripts', array($this, 'required_scripts'));  
        add_action('add_meta_boxes', array($this, 'custom_hide_nav_meta_box'));
        add_action('save_post', array($this, 'save_hide_nav_check_meta_box_data'));

    }
    function required_scripts()
    {

        wp_register_script('p-meta-support', plugin_dir_url(__FILE__) . 'assets/p-meta-support.js', array('jquery'), '1.0', true);
        wp_enqueue_script('p-meta-support');

    }


    function my_cptui_add_post_types_to_nectar_metabox_post($post_types)
    {
        if (is_plugin_active('custom-post-type-ui/custom-post-type-ui.php')) {
            $cptui_post_types = cptui_get_post_type_slugs();
            return array_merge(
                $post_types,
                $cptui_post_types
            );

        }
        return $post_types;
    }
    function hide_navigation()
    {
        global $post;
        if (function_exists('cptui_get_post_type_slugs')) {
            $post_type = get_post_type($post->ID);
            $cptui_post_types = cptui_get_post_type_slugs();
            if (in_array($post_type, $cptui_post_types) && esc_attr(get_post_meta($post->ID, '_hide_nav_check', true)) == "yes") {
            ?>
                <style>
                    nav {
                            display: none !important;
                        }
                </style>
            <?php
            }
        }
    }

    function custom_hide_nav_meta_box()
    {
        if (function_exists('cptui_get_post_type_slugs')) {
            $screens = cptui_get_post_type_slugs();
            foreach ($screens as $screen) {
                add_meta_box(
                    'custom-hide-navigation',
                    __('Navigation hide option on page', 'buzzlemedia'),
                    array($this, 'custom_hide_nav_meta_box_callback'),
                    $screen
                );
            }
        }
    }


    function custom_hide_nav_meta_box_callback($post)
    {

        // Add a nonce field so we can check for it later.
        wp_nonce_field('hide_nav_check_nonce', 'hide_nav_check_nonce');
        $value = get_post_meta($post->ID, '_hide_nav_check', true);
        
        ?>
        <p>
            <input id="hide_nav_check" type="checkbox" name="hide_nav_check"
                    value="<?php echo (($value == 'yes') ? 'yes' : 'no'); ?>" 
                    <?php echo (($value == 'yes') ? 'checked="checked"' : ''); ?>
                />
            <label for="hide_nav_check"><strong>Hide navigation on page ?</strong> </label>
          
        </p>
    <?php
    }

    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function save_hide_nav_check_meta_box_data($post_id)
    {

        // Check if our nonce is set.
        if (!isset($_POST['hide_nav_check_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['hide_nav_check_nonce'], 'hide_nav_check_nonce')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

            if (!current_user_can('edit_page', $post_id)) {
                return;
            }

        } else {

            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        // Sanitize user input.
        $dt = $_POST['hide_nav_check'] == "yes" ? "yes" : "no";

        // Update the meta field in the database.
        update_post_meta($post_id, '_hide_nav_check', sanitize_text_field($dt));
    }

}

new PlifeNectarMetaBoxSupport();
