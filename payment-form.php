<?
	include 'prolog.php';

	$log_fname = 'logs/payment-form.txt';
	include 'grabber.php';

	$query = 'SELECT * FROM rtl_links WHERE invoice_id=:invoice_id AND active=1 LIMIT 1';
	$st = $db->prepare($query);
	$st->execute([':invoice_id' => $_GET['id']]);
	$row = $st->fetch();
	if (!$row) {
		die('Срок действия ссылка на оплату истёк');
	}

	$data = json_decode($row['data'], true);

//	$receipt['sno'] = 'usn_income';
//	$receipt['payment_method'] = 'full_payment';
//	$receipt['payment_object'] = 'payment';
//	$receipt['tax'] = 'none';

	foreach($data['items'] as $item) {
		$total += $item['quantity'] * $item['price'];
	}

	foreach($data['items'] as $item) {
		$arrReceipt['items'][] = [	'name' => $item['name'],
									'quantity' => $item['quantity'],
									'sum' => $item['quantity'] * $item['price'],
									'tax' => $item['vat'],
									'payment_method' => $item['paymentMethod'],
									'payment_object' => $item['paymentObject']
								];
	}


//	$arrReceipt['sno'] = 'usn_income';

	$form['receipt'] = urlencode(json_encode($arrReceipt));

	$rtl = new Retail($row['client_id']);
	$settings = $rtl->settings();

	$form['robo_shop'] = $settings['robo_shop'];
	$form['invoice']['total'] = floatval($data['amount']);
	$form['invoice']['id'] = $row['id'];
	$form['invoice']['description'] = 'Оплата заказа №'.$data['orderNumber'];
	$form['is_test'] = $settings['robo_test'];
	$form['partner'] = 'api_retailcrm';

	if ($settings['robo_test']) {
		$api_key_1 = $settings['robo_key_test_1'];
		$api_key_2 = $settings['robo_key_test_2'];
	} else {
		$api_key_1 = $settings['robo_key_1'];
		$api_key_2 = $settings['robo_key_2'];
	}

//	$src_sign = $form['robo_shop'].':'.$form['total'].':'.$data['invoice']['name'].($row['robo_fisk'] ? ':'.$receipt : '').':'.$pass_1.':shp_id='.$code;
//	$src_sign = $form['robo_shop'].':'.$form['total'].':'.$form['invoice']['name'].''.':'.$pass_1.':shp_id='.$form['shop_id'];
	$src_sign = $form['robo_shop'].':'.$form['invoice']['total'].':'.$form['invoice']['id'].':'.$form['receipt'].':'.$api_key_1.':shp_partner='.$form['partner'];
	$form['sign'] = md5($src_sign);

?>
<html lang='ru'>
<head>
	<title>Форма оплаты покупки через Робокасса</title>
</head>
<body onload="document.forms[0].submit()">
	<form action='https://auth.robokassa.ru/Merchant/Index.aspx' method="POST">
		<input type="hidden" name="MerchantLogin" value="<?= $form['robo_shop'] ?>">
		<input type="hidden" name="OutSum" value="<?= $form['invoice']['total'] ?>">
		<input type="hidden" name="InvId" value="<?= $form['invoice']['id'] ?>">
		<input type="hidden" name="Description" value="<?= $form['invoice']['description'] ?>">
		<input type="hidden" name="SignatureValue" value="<?= $form['sign'] ?>">
		<input type="hidden" name="isTest" value="<?= $form['is_test'] ?>">
		<input type="hidden" name="Receipt" value="<?= $form['receipt'] ?>">
		<input type="hidden" name="shp_partner" value="<?= $form['partner'] ?>">
<?
	/*
		<? if (!empty($data['email'])): ?>
			<input type="hidden" name="Email" value="<?= $data['email'] ?>">
		<? endif; ?>
	*/
?>
		<input type="submit" value="Оплатить">
	</form>

</body>
</html>