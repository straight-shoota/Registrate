<?php
add_action('init', 'registrate_init');
require_once dirname(__FILE__) . '/install.php';
//add_action('wp_footer', 'registrate_messages_save');

/*define('ONEV_EVENT_STATUS_ACTIVE', 'active');
define('ONEV_EVENT_STATUS_INACTIVE', 'inactive');
define('ONEV_EVENT_STATUS_CLOSED', 'closed');
define('ONEV_EVENT_STATUS_WAITING', 'waiting');*/

function registrate_init() {
	include_once 'lib/Registrate/registrate.php';
	include_once 'app/log.php';
	
	Registrate_Field::loadClasses(REGISTRATE_DIR . 'fields');
	
	include_once 'lib/Registrate/Db/WpDb.php';
	registrate_db(new Registrate_Storage_WpDb());
	/*$registrate_messages += array(
		'not_set' => 'Folgende Felder müssen noch ausgefüllt werden: !field',
		'csrf_token'	=> "Deine Sitzungsdaten können nicht validiert werden. Bitte sende das Formular erneut ab."
	);*/
	
	include_once dirname(__FILE__) . '/admin.php';
	
	add_shortcode('registrate', 'registrate_shortcode_form');
	wp_enqueue_style('registrate', '/wp-content/plugins/registrate/registrate.css');
	
	if(! defined('REGISTRATE_LANG_DIR')){
		define('REGISTRATE_LANG_DIR', dirname(__FILE__) . '/lang/');
	}
	registrate_messages_init('de');
	
	registrate_install_1_2();
	
	/*if(registrate_db()->version < $registrate_db_version){
		
	}*/
}


function registrate_shortcode_form($atts, $content = "") {
	$atts = shortcode_atts(array(
		'event' => null
	), $atts);
	
	$event = registrate_event($atts['event']);
	if(! $event){
		$status = REGISTRATE_STATUS_ERROR;
	}else{
		$vars = registrate_form_handle($event);
		$status = $vars['status'];
	}
	
	
	switch($status){
		case REGISTRATE_STATUS_SUBMITTED:
			return '<div class="registrate registrate-thx-message">' . $event['settings']['thx-message'] . '</div>';
		break;
		case REGISTRATE_STATUS_INVALID:
		case REGISTRATE_STATUS_NULL:
			ob_start();
			registrate_form_view($vars);
			return ob_get_clean();
		break;
		case REGISTRATE_STATUS_ERROR:
		case REGISTRATE_STATUS_FORBIDDEN:
		case REGISTRATE_STATUS_FAILED_SUBMISSION:
			$texts = array(
				REGISTRATE_STATUS_ERROR => registrate_message('This registration form is not available. Please contact the side administrator.'),
				REGISTRATE_STATUS_FAILED_SUBMISSION => registrate_message('Uuups. Unfortunately your registration could not be saved due to an internal error. Please contact the site administrator.'),
				REGISTRATE_STATUS_FORBIDDEN => registrate_message('Sorry, but this registration form is restricted.')
			);
			return '<div class="registrate-messages"><div class="message error">'
			. $texts[$status]
			. '<!-- cause: ' . $status . ' --></div></div>';
		break;
			
	}
}