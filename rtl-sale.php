<?
	ob_start();

	include 'prolog.php';

	$log_fname = 'logs/rtl-sale.txt';
	include 'grabber.php';

	$out = [];

	switch($act) {
		case 'create':
			$data = json_decode($_POST['create'], true);
			$v_invoice_id = $data['invoiceUuid'];

			$st = $db->prepare('INSERT INTO rtl_links (client_id, invoice_id, data) VALUES (:client_id, :invoice_id, :data)');
			$res = $st->execute([':client_id' => $_POST['clientId'], ':invoice_id' => $v_invoice_id, ':data' => $_POST['create'] ]);

			if ($res === false) {
				$out['success'] = false;
				$out['errorMsg'] = 'Ошибка #1 при создании ссылки на оплату';
			}

			$rk_id = $db->lastInsertId();

			if (!intval($rk_id)) {
				$out['success'] = false;
				$out['errorMsg'] = 'Ошибка #2 при создании ссылки на оплату';
			} else {
				$out['success'] = true;
				$out['result']['paymentId'] = $rk_id;
				$out['result']['invoiceUrl'] = 'https://retail.robokassa.ru/payment-form.php?id='.$v_invoice_id;
			}

			break;
		case 'cancel':
			$data = json_decode($_POST['cancel'], true);

			$st = $db->prepare('UPDATE rtl_links SET active=0 WHERE id=:id AND client_id=:client_id');
			$res = $st->execute([':id' => $data['paymentId'], ':client_id' => $_POST['clientId']]);
			if ($res === false) {
				$out['success'] = false;
				$out['errorMsg'] = 'Ошибка #3 при отмене счета';
			}

			$out['success'] = true;

			break;
		default:
			$out['success'] = false;
			$out['errorMsg'] = 'Метод не поддерживается';

	}

	$stdout = ob_get_contents();
	ob_end_clean();
	if (!empty($stdout))
		file_put_contents('logs/stdout-rtl-sale.txt', $stdout, FILE_APPEND);

	Header('Access-Control-Allow-Origin: *');
	Header('Content-type: application/json');
	if (!empty($out))
		echo json_encode($out);


