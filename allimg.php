<?php
/**
Plugin Name: Selected Colorbox Image
Plugin URI: http://www.vipulhadiya.com/selected-colorbox-image/
Description:Select images just with a single click to show theme in colorbox popup from admin ajax grid.
Version: 1.0.0
Author: Vipul Hadiya
Author URI: http://vipulhadiya.com
Text Domain: allimg
License: GPL2
*/

/*  Copyright 2015  Vipul Hadiya  (email : vip@vipulhadiya.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action( 'admin_enqueue_scripts', 'allimg_loadscript' );
function allimg_loadscript()
{
    wp_register_script( 'allimg-datatable', plugin_dir_url(__FILE__).'/js/jquery.dataTables.min.js', array('jquery'));
    wp_enqueue_script( 'allimg-datatable' );
    wp_register_script( 'allimg-datatable-boot', plugin_dir_url(__FILE__).'/js/dataTables.bootstrap.js', array('jquery','allimg-datatable'));
    wp_enqueue_script( 'allimg-datatable-boot' );
    wp_register_script( 'default-script', plugin_dir_url(__FILE__).'/js/default.js', array('jquery'));
    $allimg_url  = array(
        'url' => plugin_dir_url(__FILE__)
    );
    wp_localize_script( 'default-script', 'allimg_url', $allimg_url );
    wp_enqueue_script( 'default-script' );

    wp_register_style( 'default-boot', plugin_dir_url( __FILE__ ).'css/bootstrap.min.css' );
 	wp_enqueue_style( 'default-boot' );
    wp_register_style( 'default-datatable-boot', plugin_dir_url( __FILE__ ).'css/dataTables.bootstrap.css' );
 	wp_enqueue_style( 'default-datatable-boot' );
    wp_register_style( 'default-style', plugin_dir_url( __FILE__ ).'css/allimg-default.css' );
 	wp_enqueue_style( 'default-style' );

}
add_action('wp_enqueue_scripts', 'allimg_frontscript');
function allimg_frontscript()
{

    wp_register_script( 'allimg-colorbox-js', plugin_dir_url(__FILE__).'/js/jquery.colorbox.js', array('jquery'));
    $allimg_url  = array(
        'url' => plugin_dir_url(__FILE__).'/img/'
    );
    wp_localize_script( 'allimg-colorbox-js', 'allimg_url', $allimg_url );
    wp_enqueue_script( 'allimg-colorbox-js' );
    wp_register_style( 'allimg-colorbox-css', plugin_dir_url( __FILE__ ).'/css/colorbox.css' );
 	wp_enqueue_style( 'allimg-colorbox-css' );
}
add_action('admin_menu', 'register_img_menu');
function register_img_menu()
{
    add_menu_page('Select Images', 'Colorbox', 'manage_options', 'allimg', 'printallImg','dashicons-images-alt2');
}

function printallImg()
{
    echo '<br /><br />
        <div id="allimg_container">
            <table width="100%" class="wp-list-table widefat striped" id="allimgtable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
        				<th>Title</th>
                        <th>Post</th>
                        <th>Active</th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
        				<th>Title</th>
                        <th>Post</th>
                        <th>Active</th>
                    </tr>
                </tfoot>
            </table>
        </div>';
}
add_action( 'wp_ajax_allimgs', 'ajaxAllimg' );
function ajaxAllimg()
{
    global $wpdb;
    $data = array();
    $count = $wpdb->get_results('SELECT * FROM `'.$wpdb->prefix.'posts` WHERE `post_type`=\'attachment\'');

    $sql = 'SELECT * FROM `'.$wpdb->prefix.'posts` WHERE `post_type`=\'attachment\' ORDER BY `post_parent` LIMIT '.$_GET['start'].', '.$_GET['length'];
    $tmp = $wpdb->get_results($sql);
    foreach($tmp as $t)
    {
        $data[] = array(
            'ID' =>$t->ID,
            'guid' =>$t->guid,
            'post_title' =>$t->post_title,
            'post_parent' =>$t->post_parent,
            'state' => allimg_check_attachment_state($t->ID)
        );
    }
    $result = array(
        'data' => $data,
        'recordsTotal' => count($count),
        'recordsFiltered' => count($count)
    );
    echo json_encode($result);
    wp_die();
}

add_action( 'wp_ajax_cb_allimg', 'ajaxCballimg' );
function ajaxCballimg()
{
    echo (int)attachmentOption($_POST['id_attachment'], (int)$_POST['allimg_state']);
    wp_die();
}
function allimg_check_attachment_state($id_attachment=null)
{
    if($id_attachment == null)
        return 0;
    global $wpdb;
    return (int)$wpdb->get_var( "SELECT `meta_value` FROM $wpdb->postmeta WHERE `post_id`=$id_attachment AND `meta_key`='allimg_state'" );
}
function attachmentOption($post_id, $option=1)
{
    global $wpdb;
    switch($option)
    {
        case 1:
            return $wpdb->insert(
            	$wpdb->prefix.'postmeta',
            	array(
            		'post_id' => $post_id,
            		'meta_key' => 'allimg_state',
                    'meta_value' => $option
            	),
            	array(
            		'%d','%s','%d'
            	)
            );
        break;
        case 0:
           return $wpdb->query(
            	$wpdb->prepare(
            		"DELETE FROM $wpdb->postmeta
            		 WHERE post_id = %d
            		 AND meta_key = %s
            		",$post_id, 'allimg_state'
                )
            );
        break;
        default:
            return 'Invalid input';
        break;
    }
}
function allimg_addfooter_script() {
    if(!is_single() && !is_page())
        return;
    $htm = '<script type="text/javascript">';
    $allimgs = allimg_having_colorbox();
    foreach($allimgs as $img)
    {
        $htm .='jQuery("img[src=\''.$img->guid.'\']").unwrap().wrap("<a href=\''.$img->guid.'\'   class=\'colorbox\'></a>");'."\n";
    }
    $htm .='jQuery(\'a.colorbox\').colorbox({rel:\'gal\',maxWidth:\'970px\'});</script>';
    echo $htm;
}

    add_action('wp_footer', 'allimg_addfooter_script');
function allimg_having_colorbox()
{
    global $wpdb;
    $sql = "SELECT p.guid FROM `".$wpdb->prefix."posts` p
    LEFT JOIN `".$wpdb->prefix."postmeta` pm ON (p.`ID`=pm.`post_id`)
    WHERE p.`post_type`='attachment'
    AND pm.`meta_key`='allimg_state'
    AND pm.`meta_value`=1";
    return $wpdb->get_results($sql);
}