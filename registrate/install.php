<?php
register_activation_hook(__FILE__, 'registrate_install');

$registrate_db_version = 1.2;

function registrate_install(){
	global $wpdb;
	
	$table = $wpdb->prefix . "registrate_events";
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$sql = "CREATE TABLE " . $table . " (
			  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(16) NOT NULL,
			  `form` varchar(16) NOT NULL,
			  `description` text NOT NULL,
			  `registrations` int(7) NOT NULL,
			  `status` int(3) NOT NULL,
			  `begin` datetime NOT NULL,
			  `end` datetime NOT NULL,
			  `settings` text NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `name` (`name`),
			  KEY `form` (`form`)
			) DEFAULT CHARSET=utf8;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		add_option("registrate_db_version", 1.0);
	
		add_option("registrate_forms", '', '', 'no');
	}
	
	if(registrate_db()->version < 1.1) {
		$sql = "CREATE TABLE " . $wpdb->prefix . "registrate_log (
				`id` int(8) unsigned NOT NULL AUTO_INCREMENT,
				`form` varchar(16),
				`event` int(8),
				`item` int(8),
				`message` varchar(128) NOT NULL,
				`data` text,
				`timestamp` datetime NOT NULL,
				`user` bigint(20) unsigned,
				PRIMARY KEY (`id`),
				KEY `timestamp` (`timestamp`)
			) DEFAULT CHARSET=utf8;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		registrate_db()->version = 1.1;
		update_option("registrate_db_version", registrate_db()->version);
	}
	if(registrate_db()->version < 1.2) {
		registrate_db()->version = registrate_install_1_2();
		update_option("registrate_db_version", registrate_db()->version);
	}
}
function registrate_install_1_2(){
	// introducing Registrate_Field_Status
	$field = Registrate_Field::create('status');
	foreach(registrate_form() as $form){
		$form = registrate_form_load($form);
		$form['fields']['status'] = $field;
		registrate_form_save($form);
	}
	return 1.2;
}