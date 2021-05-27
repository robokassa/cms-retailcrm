<?
	include 'prolog.php';

Class Retail {

	public $token = NULL;
	public $client_id = NULL;
	private $url = ['api' => ''];

	private $db = NULL;

	function __construct($client_id = '', $site = '', $api_key = '') {
		$this->db = $GLOBALS['db'];

		if (!empty($site) && !empty($api_key)) {
			$this->url['api'] = trim('https://'.str_replace(['https://', 'http://'], '', $site), '/');
			$this->token = $api_key;

			return;
		} else {
			$st = $this->db->prepare('SELECT * FROM rtl_users WHERE client_id=:client_id LIMIT 1');
			$st->execute([':client_id' => $client_id]);
			$row = $st->fetch();
			if ($row == false) {
				return;
			}

			$this->url['api'] = $row['rtl_url'];
			$this->token = $row['rtl_api_key'];
			$this->client_id = $row['client_id'];
		}
	}


	function check_api_ver() {
		$url = $this->url['api'].'/api/api-versions';

		$header = [	'Content-Type: application/json',
					'X-API-KEY: '.$this->token ];

		$opts = ['http' => ['method' => 'GET',
							'ignore_errors' => true,
							'header'	=> $header
				]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
		$data = json_decode($resp, true);

		return $data;
	}


	function shop_list() {
		$url = $this->url['api'].'/api/v5/reference/sites';

		$header = [	'Content-Type: application/json',
					'X-API-KEY: '.$this->token ];

		$opts = ['http' => ['method' => 'GET',
							'ignore_errors' => true,
							'header'	=> $header
				]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
		$data = json_decode($resp, true);

		return $data;
	}


	function register_app($client_id) {
		$url = $this->url['api'].'/api/v5/integration-modules/robokassa-1/edit';

		$body['integrationCode'] = 'robokassa-1';
		$body['code'] = 'robokassa-1';
		$body['clientId'] = $client_id;
		$body['baseUrl'] = 'https://retail.robokassa.ru';
		$body['accountUrl'] = 'https://retail.robokassa.ru/rtl-settings.php';
		$body['active'] = true;
		$body['name'] = 'Робокасса - прием платежей';
		$body['actions']['activity'] = 'rtl-webhook.php';
		$body['integrations']['payment']['actions']['create'] = 'rtl-sale.php?act=create';
		$body['integrations']['payment']['actions']['approve'] = 'rtl-sale.php?act=approve';
		$body['integrations']['payment']['actions']['cancel'] = 'rtl-sale.php?act=cancel';
		$body['integrations']['payment']['actions']['refund'] = 'rtl-sale.php?act=refund';
		$body['integrations']['payment']['currencies'] = ['RUB', 'KZT'];
		$body['integrations']['payment']['invoiceTypes'] = ['link'];

		$res = $this->shop_list();
		foreach($res['sites'] as $sh) {
			$body['integrations']['payment']['shops'][] = ['code' => $sh['code'], 'name' => $sh['name'], 'active' => true];
		}
//		$body['integrations']['payment']['shops'] = [['code' => 'robokassa-ru', 'name' => 'Одежда для всех', 'active' => true]];

		$post_data = http_build_query(['integrationModule' => json_encode($body)]);

		$header = [	'Content-Type: application/x-www-form-urlencoded',
					'X-API-KEY: '.$this->token ];

		$opts = ['http' => ['method' => 'POST',
							'ignore_errors' => true,
							'header'	=> $header,
							'content'	=> $post_data
				]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
		$data = json_decode($resp, true);

		return $data;
	}


	function generate_id() {
		$guid = md5(uniqid('', true));

		return $guid;
	}


	function add_client($site, $api_key = '') {
		$client_id = $this->generate_id();
		$site = 'https://'.str_replace(['https://', 'http://'], '', $site);

		$st = $this->db->prepare('INSERT INTO rtl_users (client_id, rtl_url, rtl_api_key) VALUES (:client_id, :rtl_url, :rtl_api_key)');
		$res = $st->execute([':client_id' => $client_id, ':rtl_url' => $site, ':rtl_api_key' => $api_key]);

		if ($res == false)
			return false;

		return $client_id;
	}


	function settings($pars = []) {
		if (empty($pars)) {
			$st = $this->db->prepare('SELECT * FROM rtl_users WHERE client_id=:client_id LIMIT 1');
			$st->execute([':client_id' => $this->client_id]);
			$row = $st->fetch(PDO::FETCH_ASSOC);

			return $row;
		} else {
			$st = $this->db->prepare('UPDATE rtl_users SET rtl_url=:rtl_url, rtl_api_key=:rtl_api_key, robo_shop=:robo_shop, robo_test=:robo_test, robo_key_1=:robo_key_1, robo_key_2=:robo_key_2, robo_key_test_1=:robo_key_test_1, robo_key_test_2=:robo_key_test_2 WHERE client_id=:client_id');
			$res = $st->execute([	':client_id' => $this->client_id,
									':rtl_url' => $pars['rtl_url'],
									':rtl_api_key' => $pars['rtl_api_key'],
									':robo_shop' => $pars['robo_shop'],
									':robo_test' => $pars['robo_test'],
									':robo_key_1' => $pars['robo_key_1'],
									':robo_key_2' => $pars['robo_key_2'],
									':robo_key_test_1' => $pars['robo_key_test_1'],
									':robo_key_test_2' => $pars['robo_key_test_2'],
								]);

			return $res;
		}
	}


	function set_paid($invoice_id) {
		$url = $this->url['api'].'/api/v5/payment/update-invoice';

		$body['invoiceUuid'] = $invoice_id;
		$body['status'] = 'succeeded';

		$post_data = http_build_query(['updateInvoice' => json_encode($body)]);

		$header = [	'Content-Type: application/x-www-form-urlencoded',
					'X-API-KEY: '.$this->token ];

		$opts = ['http' => ['method' => 'POST',
							'ignore_errors' => true,
							'header'	=> $header,
							'content'	=> $post_data
				]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
		$data = json_decode($resp, true);

		return $data;
	}
}