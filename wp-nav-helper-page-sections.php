<?php
/**
 * Plugin Name: WP Nav Helper - Page Sections
 * Description: Adds nav helper on menu page to link to sections of a page
 * Version:     0.1.0
 * Author:      Nathanel Schweinberg
 * Author URI:  http://github.com/nathanielks
 * License:     MIT
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Nav Menu helper
add_action('admin_enqueue_scripts', function(){
    if (get_current_screen()->id === 'nav-menus'){
        wp_enqueue_script('nhps/admin/nav-menu', plugins_url('assets/js/nav-menu.js', __FILE__), ['jquery', 'nav-menu'], null, true);
        wp_enqueue_style('nhps/admin/nav-menu-css', plugins_url('assets/css/nav-menu.css', __FILE__), [], null);
    }
});

add_action('admin_init', function (){
    add_meta_box('add-page-section', __('Page Sections'), 'nhps_nav_menu_item_page_section_meta_box', 'nav-menus', 'side', 'default');
});

function nhps_nav_menu_item_page_section_meta_box(){
    global $_nav_menu_placeholder, $nav_menu_selected_id;
    $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
    ?>
    <div class="page-section-div" id="page-section-div">
        <input type="hidden" value="page-section" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" />
        <input id="page-section-menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" type="hidden" value="http://" />
        <input type="hidden" id="page-section-nonce" name="page-section-nonce" value="<?php echo wp_create_nonce('nav-menu-page-section'); ?>"/>

        <p id="menu-item-page-wrap" class="wp-clearfix">
            <label class="howto" for="page-section-menu-item-page">
                <span><?php _e( 'Select Page' ); ?></span>
                <?php
                nhps_dropdown_pages_with_sections(array(
                    'name' => "menu-item[{$_nav_menu_placeholder}][menu-item-page]",
                    'id' => 'page-section-menu-item-page',
                    'show_option_none' => 'Select a Page',
                    'option_none_value' => -1
                ));
                ?>
            </label>
        </p>

        <p id="menu-item-section-title-wrap" class="wp-clearfix">
            <label class="howto" for="page-section-menu-item-section-title">
                <span><?php _e( 'Select Section' ); ?></span>
                <select id="page-section-menu-item-section-title" name="page-section-menu-item-section-title" disabled></select>
            </label>
        </p>

        <p id="menu-item-name-wrap" class="wp-clearfix">
            <label class="howto" for="page-section-menu-item-name">
                <span><?php _e( 'Link Text' ); ?></span>
                <input id="page-section-menu-item-name" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" type="text" class="menu-item-textbox" />
            </label>
        </p>

        <p class="button-controls wp-clearfix">
            <span class="add-to-menu">
                <input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-page-section-menu-item" id="submit-page-section-div" />
                <span class="spinner"></span>
            </span>
        </p>

    </div><!-- /.page-section-div -->
    <?php
}

function nhps_dropdown_pages_with_sections($args = array()){

    global $wpdb;
    $ids = $wpdb->get_col( $wpdb->prepare(
        "
select post_id
from {$wpdb->postmeta}
where meta_key = %s
and meta_value > %d
group by post_id
        ",
        'page_sections',
        0
    ));


    wp_dropdown_pages(wp_parse_args( $args, array(
        'include' => $ids
    )));
}

add_action('wp_ajax_select_page_sections', function(){

    $valid = check_ajax_referer('nav-menu-page-section', 'nonce', false);
    if(!$valid){
        nhps_json_error(array(
            'id' => 'invalid-nonce',
            'message' => __('You\'re not allowed to do that.')
        ), 403);
    }

    $page_id = intval($_POST['page_id']);
    if(empty($page_id)){
        nhps_json_error(array(
            'id' => 'missing-page-id',
            'message' => __('Page ID missing.')
        ));
    }

    $page_sections = get_field('page_sections', $page_id);
    if(empty($page_sections)){
        nhps_json_error(array(
            'id' => 'no-page-sections',
            'message' => __('There are no page sections for the requested page.')
        ));
    }

    $permalink = get_permalink($page_id);
    $sections = array();
    foreach($page_sections as $section){
        $key = sanitize_title($section['title']);
        $sections[$key] = $section['title'];
    }
    nhps_json_success(array(
        'permalink' => $permalink,
        'sections' => $sections
    ));
});

function nhps_json_success($data, $code = 200){
    nhps_json_response(array(
        'data' => $data
    ), $code);
}

function nhps_json_error($data, $code = 400){
    nhps_json_response(array(
        'error' => $data
    ), $code);
}

function nhps_json_response($response, $code = 200){
    http_response_code($code);
    echo wp_json_encode($response);
    exit;
}
