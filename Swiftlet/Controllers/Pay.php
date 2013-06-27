<?php

namespace Swiftlet\Controllers;

class Pay extends \Swiftlet\Controller
{
	protected
		$title = 'Pay what you want',
		$config = array(),
		$userId
		;

	/**
	 * Constructor
	 * @param object $app
	 * @param object $view
	 */
	public function __construct(\Swiftlet\Interfaces\App $app, \Swiftlet\Interfaces\View $view)
	{
		parent::__construct($app, $view);

		$this->userId = $this->app->getSingleton('helper')->ensureValidUser();

		require('config/pin.net.au.php');

		$this->config = $this->app->getConfig('pinNetAu');

		$this->view->set('countries', array(
			'US' => 'United States',
			'AU' => 'Australia',
			''	 => '',
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AC' => 'Ascension Island',
			//'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BW' => 'Botswana',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, Democratic Republic',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'EU' => 'European Union',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'FX' => 'France, Metropolitan',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'MK' => 'Macedonia',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GB' => 'Great Britain',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard and McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IM' => 'Isle of Man',
			'IT' => 'Italy',
			'JE' => 'Jersey',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea (North)',
			'KR' => 'Korea (South)',
			'XK' => 'Kosovo',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LI' => 'Liechtenstein',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macau',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MC' => 'Monaco',
			'MD' => 'Moldova',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NT' => 'Neutral Zone',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand (Aotearoa)',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory, Occupied',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'GS' => 'S. Georgia and S. Sandwich Isls.',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'VC' => 'Saint Vincent &amp; the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SI' => 'Slovenia',
			'SK' => 'Slovak Republic',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard &amp; Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'UK' => 'United Kingdom',
			//'US' => 'United States',
			'UM' => 'US Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican City State',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'British Virgin Islands',
			'VI' => 'Virgin Islands',
			'WF' => 'Wallis and Futuna Islands',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZR' => 'Zaire',
			'ZW' => 'Zimbabwe'
			));
	}

	/**
	 * Default action
	 */
	public function index()
	{
		$amount      = isset($_POST['amount'])           ? $_POST['amount']           : '';
		$currency    = isset($_POST['currency'])         ? $_POST['currency']         : '';
		$months      = isset($_POST['months'])           ? $_POST['months']           : '';
		$name        = isset($_POST['name'])             ? $_POST['name']             : '';
		$number      = isset($_POST['number'])           ? $_POST['number']           : '';
		$expiryMonth = isset($_POST['expiry-month'])     ? $_POST['expiry-month']     : '';
		$expiryYear  = isset($_POST['expiry-year'])      ? $_POST['expiry-year']      : '';
		$cvc         = isset($_POST['cvc'])              ? $_POST['cvc']              : '';
		$address1    = isset($_POST['address-line-1'])   ? $_POST['address-line-1']   : '';
		$address2    = isset($_POST['address-line-2'])   ? $_POST['address-line-2']   : '';
		$city        = isset($_POST['address-city'])     ? $_POST['address-city']     : '';
		$postcode    = isset($_POST['address-postcode']) ? $_POST['address-postcode'] : '';
		$state       = isset($_POST['address-state'])    ? $_POST['address-state']    : '';
		$country     = isset($_POST['address-country'])  ? $_POST['address-country']  : '';

		$expires = strtotime('+' . $months . ' month');

		if ( !empty($_POST['form']) ) {
			if ( $_POST['form'] == 'pay-partial' ) {
				$this->view->set('amount',   $amount);
				$this->view->set('currency', $currency);
				$this->view->set('months',   $months);

				return;
			}

			$success = false;
			$error   = array();

			if ( !$number ) {
				$error[] = 'Please provide a valid credit card number';

				$this->view->set('error-number', true);
			}

			if ( !$expiryMonth || !$expiryYear ) {
				$error[] = 'Please select a valid expiry date';

				$this->view->set('error-expiry', true);
			}

			if ( !$cvc ) {
				$error[] = 'Please provide a valid security code';

				$this->view->set('error-cvc', true);
			}

			if ( !$name ) {
				$error[] = 'Please provide your name';

				$this->view->set('error-name', true);
			}

			if ( !$address1 ) {
				$error[] = 'Please provide your address';

				$this->view->set('error-address', true);
			}

			if ( !$city ) {
				$error[] = 'Please provide your city';

				$this->view->set('error-address-city', true);
			}

			if ( !$postcode ) {
				$error[] = 'Please provide your postcode';

				$this->view->set('error-address-postcode', true);
			}

			if ( !$state ) {
				$error[] = 'Please provide your state or province';

				$this->view->set('error-address-state', true);
			}

			if ( !$country ) {
				$error[] = 'Please select your country';

				$this->view->set('error-address-country', true);
			}

			if ( !$error ) {
				$result = $this->curl('charges', array(
					'amount'                 => (int) $amount * (int) $months * 100,
					'currency'               => $currency,
					'description'            => $this->app->getConfig('siteName') . ' - Thank you!',
					'email'                  => $this->app->getSingleton('session')->get('email'),
					'ip_address'             => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
					'[card]number'           => $number,
					'[card]expiry_month'     => $expiryMonth,
					'[card]expiry_year'      => $expiryYear,
					'[card]cvc'              => $cvc,
					'[card]name'             => $name,
					'[card]address_line1'    => $address1,
					'[card]address_line2'    => $address2,
					'[card]address_city'     => $city,
					'[card]address_postcode' => $postcode,
					'[card]address_state'    => $state,
					'[card]address_country'  => $country
					));

				if ( isset($result->response) ) {
					$dbh = $this->app->getSingleton('pdo')->getHandle();

					$sth = $dbh->prepare('
						INSERT INTO payments (
							user_id,
							amount,
							currency,
							description,
							email,
							ip_address,
							created_at,
							expires_at
						) VALUES (
							:user_id,
							:amount,
							:currency,
							:description,
							:email,
							:ip_address,
							:created_at,
							:expires_at
						)
						');

					$createdAt = date('Y-m-d H:i:s', strtotime($result->response->created_at));
					$expiresAt = date('Y-m-d H:i:s', $expires);

					$sth->bindParam('user_id',     $this->userId,             \PDO::PARAM_INT);
					$sth->bindParam('amount',      $result->response->amount, \PDO::PARAM_INT);
					$sth->bindParam('currency',    $result->response->currency);
					$sth->bindParam('description', $result->response->description);
					$sth->bindParam('email',       $result->response->email);
					$sth->bindParam('ip_address',  $result->response->ip_address);
					$sth->bindParam('created_at',  $createdAt);
					$sth->bindParam('expires_at',  $expiresAt);

					$sth->execute();

					$this->app->getSingleton('helper')->localize($expiresAt);

					$success =
						'Thank you!<br><br>' .
						'Your credit card has been charged <strong>' . $result->response->currency . ' ' . number_format($result->response->amount / 100, 2) . '</strong>. ' .
						'Your payment is valid until <strong>' . date('F j, Y', $expiresAt) . '</strong>.'
						;

					/*
					$message =
						"Name: " . $name . "\n" .
						"Payment date: " . date('F j, Y', strtotime($result->response->created_at)) . "\n\n" .
						"This is your receipt of payment in the amount of " . $result->response->currency . " " . number_format($result->response->amount / 100, 2) . ".\n\n" .
						"Thank you for your support!\n\n" .
						"If you have any questions, please reply to this email.\n\n" .
						"Sincerely,\n\n" .
						$this->app->getConfig('siteName') . "\n" .
						$this->app->getConfig('websiteUrl')
						;

					$this->app->getSingleton('helper')->sendMail($this->app->getSingleton('session')->get('email'), 'Payment receipt', $message);
					$this->app->getSingleton('helper')->sendMail($this->app->getConfig('emailFrom'),                'Payment receipt', $message);
					*/
				}

				if ( isset($result->error) ) {
					foreach ( $result->messages as $message ) {
						$error[] = $message->message;

						$this->view->set('error-' . str_replace('_', '-', $message->param), true);
					}
				}
			}

			if ( $success ) {
				$this->view->set('success', $success);
			} else {
				$this->view->set('error', implode('<br>', $error));

				$this->view->set('amount',           $amount);
				$this->view->set('months',           $months);
				$this->view->set('name',             $name);
				$this->view->set('number',           $number);
				$this->view->set('expiry-month',     $expiryMonth);
				$this->view->set('expiry-year',      $expiryYear);
				$this->view->set('cvc',              $cvc);
				$this->view->set('address-line-1',   $address1);
				$this->view->set('address-line-2',   $address2);
				$this->view->set('address-city',     $city);
				$this->view->set('address-postcode', $postcode);
				$this->view->set('address-state',    $state);
				$this->view->set('address-country',  $country);
			}
		}

		if ( !$this->view->get('amount') ) {
			$this->view->set('amount', 5);
		}

		if ( !$this->view->get('months') ) {
			$this->view->set('months', 12);
		}
	}

	protected function curl($path, $params)
	{
		$response = new \stdClass;

		$ch = curl_init($this->config['url'] . $path);

		curl_setopt_array($ch, array(
			CURLOPT_USERPWD        => $this->config['privateKey'] . ':',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $params
			));

		$result = curl_exec($ch);

		if ( curl_errno($ch) !== CURLE_OK ) {
			//throw new Exception(curl_error($ch), self::CURL_ERROR);
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ( $httpCode != 200 ) {
			//throw new Exception('cURL request returned HTTP code ' . $httpCode, self::SERVER_ERROR);
		}

		return json_decode($result);
	}

}
