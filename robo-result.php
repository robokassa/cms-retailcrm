<?
	ob_start();

	include 'prolog.php';

	$log_fname = 'logs/robo-result.txt';
	include 'grabber.php';

	$st = $db->prepare('SELECT robo_test, robo_key_2, robo_key_test_2, invoice_id, us.client_id FROM rtl_users us, rtl_links li WHERE us.client_id=li.client_id AND li.id=:id');
	$st->execute([':id' => $_POST['InvId']]);
	if (($row = $st->fetch()) === false)
		die('Securiry error #1');

	$api_key_2 = $row['robo_test'] ? $row['robo_key_test_2'] : $row['robo_key_2'];

	$sign = MD5($_POST['OutSum'].':'.$_POST['InvId'].':'.$api_key_2);
//	if ($sign <> $_POST['SignatureValue'])
//		die('Securiry error #2');

	switch($_GET['a']) {
		case 'result':
			$rtl = new Retail($row['client_id']);
			$rtl->set_paid($row['invoice_id']);

			echo "OK".$_POST['InvId'];

			break;
		case 'success':
			echo "Оплата прошла успешно";

			break;
		case 'fail':
			echo "Ошибка при оплате";

			break;
	}

	$stdout = ob_get_contents();
	ob_end_clean();
	if (!empty($stdout))
		file_put_contents('logs/stdout-rtl-sale.txt', $stdout, FILE_APPEND);

	echo $stdout;