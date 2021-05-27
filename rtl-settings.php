<?
	include 'prolog.php';

	if ($context != 'onboard') {
		$log_fname = 'logs/rtl-settings.txt';

		include 'grabber.php';

		if ($_POST['act'] == 'save') {
			$rtl = new Retail($_POST['client_id']);

			$pars = [	'rtl_url' => filter_input(INPUT_POST, 'd_retail_url', FILTER_SANITIZE_URL),
						'rtl_api_key' => filter_input(INPUT_POST, 'd_retail_api_key', FILTER_SANITIZE_STRING),
						'robo_shop' => filter_input(INPUT_POST, 'd_shop_id', FILTER_SANITIZE_STRING),
						'robo_test' => filter_input(INPUT_POST, 'd_testmode', FILTER_SANITIZE_STRING),
						'robo_key_1' => filter_input(INPUT_POST, 'd_shop_key_1', FILTER_SANITIZE_STRING),
						'robo_key_2' => filter_input(INPUT_POST, 'd_shop_key_2', FILTER_SANITIZE_STRING),
						'robo_key_test_1' => filter_input(INPUT_POST, 'd_shop_key_test_1', FILTER_SANITIZE_STRING),
						'robo_key_test_2' => filter_input(INPUT_POST, 'd_shop_key_test_2', FILTER_SANITIZE_STRING)
					];
			$res = $rtl->settings($pars);
			if (!$res) {
				$error = 'Ошибка при сохранении параметров';
			} else {
				$alert = 'Изменения сохранены';
			}
			$settings = $rtl->settings();
			$client_id = $settings['client_id'];

		} else {
			$client_id = filter_input(INPUT_POST, 'clientId', FILTER_SANITIZE_STRING);

			$rtl = new Retail($client_id);
			$settings = $rtl->settings();

			if ($settings == false)
				die('Ошибка #13');
		}
	} else {
		if ($_POST['act'] == 'connect') {
			$v_site = trim(filter_input(INPUT_POST, 'd_retail_url', FILTER_SANITIZE_URL), '/');
			$v_api_key = filter_input(INPUT_POST, 'd_retail_api_key', FILTER_SANITIZE_STRING);

			if (empty($v_site) || empty($v_api_key)) {
				$error = 'Укажите сайт-аккаунт в RetailCRM и API-ключ';
			} else {
				$rtl = new Retail('', $v_site, $v_api_key);
				$res = $rtl->check_api_ver();

				if (!$res['success']) {
					$error = 'Ошибка подключения к API магазина. Неверный API-ключ';
				} elseif (!in_array(5, $res['versions'])) {
					$error = 'Ошибка подключения к API магазина. Минимальная версия для работы: 5.0';
				} elseif (0) { // можно проверить права

				} else { // если всё ок - то создаем учетку у себя и ставим
					$client_id = $rtl->add_client($v_site, $v_api_key);
					if (empty($client_id)) {
						$error = 'Ошибка при установке приложения #11';
					} else {
						$res = $rtl->register_app($client_id);
						if ($res['success']) {
							Header('Location: '.$v_site.'/admin/integration/robokassa-1/edit');
						} else {
							$error = $res['errorMsg'];
						}
					}
				}
			}

		} else {
			$v_site = parse_url(filter_input(INPUT_GET, 'account', FILTER_SANITIZE_URL), PHP_URL_HOST);
		}
	}

?>
<!DOCTYPE html>
<html lang='ru'>
<head>
	<title>Робокасса - настройки модуля</title>

	<meta charset='utf-8'>

	<link rel="shortcut icon" type="image/x-icon" href="<?=sURL?>images/favicon.ico"/>

	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
	<div class='wrap'>
		<center><img src='images/logo.png'></center>
		<h1>Настройки модуля</h1>
		<?
			if (!empty($error))
				echo "<div class='error'>".$error."</div>";
			if (!empty($alert))
				echo "<div class='alert'>".$alert."</div>";


			if ($context == 'onboard') {
				$act = 'connect';
				$action = 'rtl-onboard.php';

				if (empty($settings['rtl_url'])) {
					if (isset($_GET['account']))
						$settings['rtl_url'] = filter_input(INPUT_GET, 'account', FILTER_SANITIZE_URL);
				}
			} else {
				$act = 'save';
				$action = 'rtl-settings.php';
			}
		?>
		<form action='<?=  $action ?>' method='POST'>

			<input type='hidden' name='act' value='<?= $act ?>'>
			<input type='hidden' name='client_id' value='<?= $client_id ?>'>

			<h2>Настройки RetailCRM</h2>

			<div class='field'>
				<label>Аккаунт RetailCRM</label>
				<input type='text' name='d_retail_url' value='<?= $settings['rtl_url'] ?>'>
			</div>
			<div class='field'>
				<label>Ключ API доступа к RetailCRM</label>
				<input type='text' name='d_retail_api_key' value='<?= $settings['rtl_api_key'] ?>'>
			</div>
		<?
			if ($context != 'onboard'):
		?>
			<h2>Настройки Робокассы</h2>

			<div class='field'>
				<label>ID магазина</label>
				<input type='text' name='d_shop_id' value='<?= $settings['robo_shop'] ?>'>
			</div>
			<div class='cols'>
				<div class='field'>
					<label>Пароль 1</label>
					<input type='text' name='d_shop_key_1' value='<?= $settings['robo_key_1'] ?>'>
				</div>
				<div class='field'>
					<label>Пароль 2</label>
					<input type='text' name='d_shop_key_2' value='<?= $settings['robo_key_2'] ?>'>
				</div>
			</div>

			<div class='field testing'>
				<label>Тестирование оплаты</label>
				<div class='cols'>
					<label class='radio'>
						<input type='radio' name='d_testmode' value='1' <?= $settings['robo_test'] == 1 ? 'checked' : '' ?>> Влючить
					</label>
					<label class='radio'>
						<input type='radio' name='d_testmode' value='0' <?= $settings['robo_test'] == 0 ? 'checked' : '' ?>> Отключить
					</label>

				</div>
			</div>

			<div class='cols'>
				<div class='field'>
					<label>Тестовый пароль 1</label>
					<input type='text' name='d_shop_key_test_1' value='<?= $settings['robo_key_test_1'] ?>'>
				</div>
				<div class='field'>
					<label>Тестовый пароль 2</label>
					<input type='text' name='d_shop_key_test_2' value='<?= $settings['robo_key_test_2'] ?>'>
				</div>
			</div>
		<?
			endif;
		?>
			<div class='field'>
				<?
					if ($context == 'onboard'):
				?>
						<button>Подключить</button>
				<?
					else:
				?>
						<button>Сохранить</button>
				<?
					endif;
				?>
			</div>
		</form>
	</div>
</body>
</html>