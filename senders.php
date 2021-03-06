<?php
	require_once 'include/auth.php';
	require_once 'include/db_connect.php';
	
	require_once 'include/classes/paging.php';
	require_once 'libs/Smarty.class.php';
	$smarty = new Smarty();
	require_once 'include/view_config.php';


	$per_page = intval($GLOBALS['json_obj']->accounts_number);

	$_PAGING = new Paging($pdo,$per_page);

	$r = $_PAGING->get_page("SELECT * FROM senders");

	$senders = array();

	foreach ($r as $sender) {
			
			$senders[] = array('id'       => $sender['id'],
							   'email'    => $sender['email'],
							   'password' => $sender['password'],
							   'login'    => $sender['login'],
							   'service'  => $sender['service'],
							   'lastsending' => $sender['lastsending']);
	}

	$smarty->assign("senders",$senders);

	paging($_PAGING,$smarty);  // set variables and setting for paging, from view_config.php	

	$smarty->display("senders.tpl");
?>