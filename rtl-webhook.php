<?
	ob_start();

	include 'prolog.php';

	$log_fname = 'logs/rtl-webhook.txt';
	include 'grabber.php';

	$out = [];


	$stdout = ob_get_contents();
	ob_end_clean();
	if (!empty($stdout))
		file_put_contents('logs/stdout-rtl-webhook.txt', $stdout, FILE_APPEND);

	Header('Access-Control-Allow-Origin: *');
	Header('Content-type: application/json');
	if (!empty($out))
		echo json_encode($out);


