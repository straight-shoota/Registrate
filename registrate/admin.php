<?php
include_once 'app/admin/admin-functions.php';

add_action('wp_ajax_registrate_list', 'registrate_admin_list_ajax');
add_action('wp_ajax_registrate_event', 'registrate_admin_event');
add_action('wp_ajax_registrate_item', 'registrate_admin');

add_action('admin_menu', 'registrate_admin_menu');
add_action('admin_init', 'registrate_admin_init');
add_action('wp_dashboard_setup', 'registrate_dashboard_widgets');

$author = get_role('author');
$author->add_cap('view_registrations');

$editor = get_role('editor');
$editor->add_cap('view_registrations');
$editor->add_cap('edit_registrations');

$admin = get_role('administrator');
$admin->add_cap('view_registrations');
$admin->add_cap('edit_registrations');
$admin->add_cap('registrate_options');

define('SAVEQUERIES', true);

function registrate_admin_menu() {
	
	add_menu_page('Registrate', 'Registrate', 'view_registrations', 'registrate_options', 'registrate_admin');
	//$registrationsPage = add_submenu_page('registrate_options', "Registrations", "Registrations", 1, "regisrtate_list", "registrate_admin_list_html");
	//add_action('admin_head-' . $registrationsPage, 'registrate_admin_header');

	add_submenu_page('registrate_options', 'Events', 'Events', 'registrate_options', 'registrate_event', 'registrate_admin');
	add_submenu_page('registrate_options', 'Log', 'Log', 'view_registrations', 'registrate_log', 'registrate_admin');
	//add_submenu_page('registrate_options', 'Registrate Forms', 'Forms', 1, 'registrate_form', 'registrate_admin');
	
	// the following code enables the page registrate_page but is hidden from the admin menu
	// this is necessary because event and form code should be separated but they share a common overview
	global $_registered_pages;
	$hook = get_plugin_page_hookname('registrate_form', 'admin.php');
	$_registered_pages[$hook] = true;
	add_action($hook, 'registrate_admin');
	$hook = get_plugin_page_hookname('registrate_item', 'admin.php');
	$_registered_pages[$hook] = true;
	add_action($hook, 'registrate_admin');
	$hook = get_plugin_page_hookname('registrate_stats', 'admin.php');
	$_registered_pages[$hook] = true;
	add_action($hook, 'registrate_admin');
}
function registrate_admin_init() {
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-dialog');
	
	wp_enqueue_style('registrate_admin', WP_PLUGIN_URL . '/registrate/admin.css');
	wp_enqueue_style('registrate_jquery_smothness', WP_PLUGIN_URL . '/registrate/css/smoothness/jquery-ui-1.7.2.custom.css');
	
	wp_enqueue_script('registrate_admin', WP_PLUGIN_URL . '/registrate/js/admin.js');
	wp_enqueue_script('registrate_admin_list', WP_PLUGIN_URL . '/registrate/js/list.js');
	wp_enqueue_script('registrate_admin_form', WP_PLUGIN_URL . '/registrate/js/form.js');
	wp_enqueue_script('registrate_admin_stats', WP_PLUGIN_URL . '/registrate/js/stats.js');
}

function registrate_dashboard_widgets() {
	require_once 'app/admin/dashboard.php';
	require_once 'app/admin/widget/recent.php';
	wp_add_dashboard_widget('recent-registrations', 'Recent registrations', 'registrate_widget_recent', 'registrate_widget_recent_controls');
}