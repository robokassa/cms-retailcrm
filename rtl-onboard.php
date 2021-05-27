<?
	include 'prolog.php';

	$log_fname = 'logs/rtl-onboard.txt';
	include 'grabber.php';

//	$rtl = new Retail();
//	$client_id = $rtl->add_client(filter_input(INPUT_GET, 'account', FILTER_SANITIZE_URL));

	$context = 'onboard';

	include 'rtl-settings.php';


