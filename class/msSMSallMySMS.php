<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Gestion SMS via allMySMS <https://www.allmysms.com/>
 *
 * @author            Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib 2020      Maxime   DEMAREST   <maxime@indelog.fr>
 */
class msSMSallMySMS
{

	/**
	 * Nom de la campagne sms
	 * @var string
	 */
	private $_campaign_name;

	/**
	 * Data de la camapgne
	 * @var string
	 */
	private $_campaign_data;

	/**
	 * Réponse de l'api
	 * @var string
	 */
	private $_campaign_answer;

	/**
	 * Message à envoyer
	 * @var string
	 */
	private $_message;

	/**
	 * Nom expéditeur du SMS
	 * @var string
	 */
	private $_tpoa;

	/**
	 * Date d'envoi différé du SMS (YYYY-MM-JJ HH:MM:SS)
	 * @var string
	 */
	private $_date;

	/**
	 * Destinataire(s)
	 * @var array
	 */
	private $_destinataires;

	/**
	 * Notification par mail
	 * @var int
	 */
	private $_mail_notification = 1;

	/**
	 * Dynamic : nombre de paramètres dans le messages
	 * @var int
	 */
	private $_dynamic;

	/**
	 * Timestamp unix pour déterminer le répoertoire de log de la campagne
	 * @var int
	 */
	private $_timestamp4log;

	/**
	 * Répertoire vers le répertoire de log
	 * @var string
	 */
	private $_directory4log;

	/**
	 * Filename pour déterminer le log de la campagne
	 * @var string
	 */
	private $_filename4log;

	/**
	 * Data à ajouter dans le log
	 * @var array
	 */
	private $_addData4log;

	public function __construct() {
		global $p;

		$this->_directory4log = $p['config']['smsLogCampaignDirectory'];
		$this->_filename4log = 'RappelsRDV.json';
	}

	/**
	 * Ajouter des datas au log
	 * @param array $_addData4log
	 * @return msSMSallMySMS
	 */
	public function set_addData4log(array $_addData4log)
	{
		$this->_addData4log = $_addData4log;
		return $this;
	}

	/**
	 * Set filename4log
	 * @param string $_filename4log nom du fichier
	 * @return msSMSallMySMS
	 */
	public function set_filename4log($_filename4log)
	{
		$this->_filename4log = $_filename4log;
		return $this;
	}

	/**
	 * Set timestamp4log
	 * @param int $_timestamp4log timestamp
	 * @return msSMSallMySMS
	 */
	public function set_timestamp4log($_timestamp4log)
	{
		$this->_timestamp4log = $_timestamp4log;
		return $this;
	}

	/**
	 * Set campaign_name
	 * @param string $_campaign_name
	 * @return string campaing name
	 */
	public function set_campaign_name($_campaign_name)
	{
		$this->_campaign_name = $_campaign_name;
		return $this;
	}

	/**
	 * Get campaign_name
	 * @return string nom de la campagne
	 */
	public function get_campaign_name()
	{
		return $this->_campaign_name;
	}

	/**
	 * Set mail notification
	 * @param int $_mail_notification 0/1
	 * @return msSMSallMySMS
	 */
	public function set_mail_notification($_mail_notification)
	{
		$this->_mail_notification = $_mail_notification;
		return $this;
	}

	/**
	 * Set message
	 * @param string $_message message à envoyer
	 * @return msSMSallMySMS
	 */
	public function set_message($_message)
	{
		$this->_message = $_message;
		return $this;
	}

	/**
	 * Set tpoa
	 * @param string $_tpoa Emmeteur du message
	 * @return string tpoa
	 */
	public function set_tpoa($_tpoa)
	{
		$this->_tpoa = $_tpoa;
		return $this;
	}

	/**
	 * Get tpoa
	 * @return string Retourne l'emmeteur du message
	 */
	public function get_tpoa()
	{
		return $this->_tpoa;
	}

	/**
	 * Set date
	 * @param string $_date date d'envoi différé
	 * @return msSMSallMySMS
	 */
	public function set_date($_date)
	{
		$this->_date = $_date;
		return $this;
	}

	/**
	 * Récupération du répertoire vers les logs
	 * @return string
	 */
	public function getDirectory4log()
	{
		return $this->_directory4log;
	}

	/**
	 * Définir un nouveau répertoire pour loguer
	 * @param string $directory4log
	 * @throws Exception
	 */
	public function setDirectory4log($directory4log)
	{
		if (is_dir($directory4log)) {
			$this->_directory4log = $directory4log;
		} else {
			throw new Exception("Le répertoire est invalide");
		}
	}


	/**
	 * Get full path of campaign log
	 * @return string chemin du log de la campagne sms
	 * @throws Exception
	 */
	public function get_fullpath4log()
	{
		if (!isset($this->_filename4log)) $this->_filename4log = 'RappelsRDV.json';
		if (empty($this->_directory4log)) throw new Exception('$this->_directory4log n\'est pas définie');
		if (empty($this->_timestamp4log)) throw new Exception('$this->_timestamp4log n\'est pas définie');
		return $this->_directory4log . date('Y/m/d/', $this->_timestamp4log) . $this->_filename4log;
	}

	/**
	 * Ajouter un destinataire à la campagne
	 * @param string $tel n° de téléphone sans espace
	 * @param array $params paramètres à inclure dans message
	 * @return void
	 */
	public function ajoutDestinataire($tel, $params = [])
	{
		$tel = trim(str_ireplace(array(' ', '/', '.'), '', $tel));
		if (preg_match('/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/mix', $tel) === 1) {
			$destinataire['mobilePhone'] = $tel;
			if (count($params) > 0) {

				if (!isset($this->_dynamic)) $this->_dynamic = count($params);

				foreach ($params as $k => $v) {
					$destinataire[$k] = $v;
				}

			}
			$this->_destinataires[] = $destinataire;
		}
	}

	/**
	 * Générer la requète curl pour la campagne
	 * @return void [type] [description]
	 * @throws Exception
	 */
	private function _generateCampaign()
	{
		if (!isset($this->_campaign_name)) throw new Exception('Campaign_name n\'est pas définie');
		if (!isset($this->_message)) throw new Exception('Message n\'est pas définie');
		if (!isset($this->_destinataires)) throw new Exception('Destinataires n\'est pas définie');
		if (!isset($this->_dynamic)) $this->_dynamic = 0;

		$c['DATA'] = array(
			"CAMPAIGN_NAME" => $this->_campaign_name,
			"MESSAGE" => $this->_message,
			"DYNAMIC" => $this->_dynamic,
			"MAIL_NOTIF" => $this->_mail_notification
		);

		if (isset($this->_tpoa)) $c['DATA']['TPOA'] = $this->_tpoa;
		if (isset($this->_date)) $c['DATA']['DATE'] = $this->_date;

		$c['DATA']['SMS'] = $this->_destinataires;

		$this->_campaign_data = $c;
	}

	/**
	 * Envoyer la campagne
	 * @param boolean $simulate Simuler la campagne (default false)
	 * @param boolean $force Force la re-émission de la campage même si celle-ci existe déjà (default false)
	 * @return string              Retour JSON de l'api
	 */
	public function sendCampaign($simulate = 0, $force = 0)
	{
		global $p;

		// check si la campagne pour le jour est déjà envoyé
		$logFile = $this->_directory4log . date('Y/m/d/', $this->_timestamp4log) . $this->_filename4log;
		if (file_exists($logFile) && $force != true) {
			return false;
		}

		if (is_array($this->_destinataires) && count($this->_destinataires) > 0) {
			try {
				$this->_generateCampaign();
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}

			$url = 'https://api.allmysms.com/sms/send/bulk/';

			$auth_token = base64_encode($p['config']['allMySmsLogin'] . ':' . $p['config']['allMySmsApiKey']);

			$array_data = array(
				'text' => $this->_campaign_data['DATA']['MESSAGE'] . " \r\nStop au 36180",
				'to' => $this->_campaign_data['DATA']['SMS'],
				'from' => $this->_campaign_data['DATA']['TPOA'],
				'campaignName' => $this->_campaign_data['DATA']['CAMPAIGN_NAME'],
				'alerting' => 1,
				'simulate' => intval(!empty($simulate)),
			);
			$post_data = json_encode($array_data);

			//open connection
			$ch = curl_init();

			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $post_data,
				CURLOPT_HTTPHEADER => array(
					"Authorization: Basic " . $auth_token,
					"Content-Type: application/json",
					"cache-control: no-cache"
				),
			));

			$result = curl_exec($ch);

			//close connection
			curl_close($ch);

		} else {
			$result = [];
			$result['status'] = '0';
			$result['statusText'] = "Pas de destinataires pour cette campagne - API AllMySMS non sollicitée";
			$result = json_encode($result);
		}

		$this->_campaign_answer = $result;

		return $result;
	}

	/**
	 * Loguer la campagne sous forme de fichier json
	 * @return void
	 * @throws Exception
	 */
	public function logCampaign()
	{
		if (!isset($this->_campaign_answer)) throw new Exception('Campaign_answer n\'est pas définie');
		if (!isset($this->_timestamp4log)) $this->_timestamp4log = time();

		$tab = json_decode($this->_campaign_answer, true);
		$tab['timestamp_send'] = time();
		$tab['campaign_data'] = $this->_campaign_data;

		if (isset($this->_addData4log)) {
			foreach ($this->_addData4log as $k => $v) {
				$tab[$k] = $v;
			}
		}

		$tab = json_encode($tab);

		//log json
		$logFileDirectory = $this->_directory4log . date('Y/m/d/', $this->_timestamp4log);
		msTools::checkAndBuildTargetDir($logFileDirectory);
		file_put_contents($logFileDirectory . $this->_filename4log, $tab, FILE_APPEND);

	}

	/**
	 * Loguer le crédit SMS restant
	 * @return void
	 * @throws Exception
	 */
	public function logCreditsRestants()
	{
		global $p;
		if (!isset($this->_campaign_answer)) throw new Exception('Campaign_answer n\'est pas définie');
		$campain_answer = json_decode($this->_campaign_answer, true);
		if (isset($campain_answer['balance']) && isset($campain_answer['cost'])) {
			$credits = round($campain_answer['balance'] / ($campain_answer['cost'] / $campain_answer['nbSms']));
			file_put_contents($p['config']['workingDirectory'] . $p['config']['smsCreditsFile'], $credits);
		}
	}

	/**
	 * Ajouter les infos accusé de réception au log
	 * @param string $logFile fichier de log à amender
	 * @return array|null
	 */
	public function addAcksToLogs($logFile)
	{
		if (is_file($logFile)) {
			$data = json_decode(file_get_contents($logFile), true);

			// Les campagne simulés n'ont pas d'accusé de récéption
			if (isset($data['campaignId']) && empty($data['simulate'])) {
				$acks = $this->getAcksRecep($data['campaignId']);
				if (is_array($acks)) {
					$data['acks'] = $acks;
					file_put_contents($logFile, json_encode($data), FILE_APPEND);
					return $acks;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}
	}

	/**
	 * Obtenir les infos accusés de réception
	 * @param  string $campId ID dans le campagne
	 * @return array          Tableau des infos accusé de réception
	 */
	public function getAcksRecep($campId)
	{
		global $p;

		$url = 'https://api.allmysms.com/report/campaign/' . $campId;

		//set auth_token
		$auth_token = base64_encode($p['config']['allMySmsLogin'] . ':' . $p['config']['allMySmsApiKey']);

		//open connection
		$ch = curl_init();

		// fetch data
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Authorization: Basic " . $auth_token,
				"Content-Type: application/json",
				"cache-control: no-cache"
			),
		));
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);

		$data = json_decode($result, true);

		if (!empty($data['code']) && !empty($data['reports'])) {
			$res = array();
			foreach ($data['reports'] as $elem) {
				$res[] = array(
					'phoneNumber' => $elem['phoneNumber'],
					'status' => $elem['code'],
					'comment' => $elem['description'],
					'receptionDate' => $elem['receptionDate'],
				);
			}
			return $res;
		} else {
			return null;
		}
	}

	/**
	 * Obtenir les donnée d'une campagne envoyé par sms
	 * @param string $date    Date d'evoie de la campagne au format 'Y-m-d'
	 * @param string $logDir  Dossier de stockage des retours de campagne sms (paramettre "smsLogCampaignDirectory"), si aucune varleur n'est fournis utilisara la valeur de la configuration
	 * @return array          Tableau des information sur la campagne envoyée
	 */
	public function getSendedCampaignData($date, $logDir = '')
	{
		$smsReturnTab = [];

		if (empty($logDir)) {
			$logFile = $this->_directory4log. date('Y/m/d/', $date) . $this->_filename4log;
		} else {
			$logFile = $logDir . date('Y/m/d/', $date) . $this->_filename4log;
		}

		if (is_file($logFile)) {
			if ($data = file_get_contents($logFile)) {
				$data = json_decode($data, true);

				//obtenir les accusés réception si non présents
				if (!isset($data['acks'])) {
					$acksdata = $this->addAcksToLogs($logFile);
					if (is_array($acksdata)) $data['acks'] = $acksdata;
				}

				//boucle sur liste patients
				if (isset($data['patientsList'])) {
					foreach ($data['patientsList'] as $v) {
						$dataw[$v['heure']] = $v;
					}
				}

				//boucle sur liste des envois
				if (isset($data['campaign_data']['DATA']['SMS'])) {
					foreach ($data['campaign_data']['DATA']['SMS'] as $v) {
						$v['telDisplay'] = preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1 \2 \3 \4 \5', $v['mobilePhone']);
						$dataw[$v['param2']] = array_merge($dataw[$v['param2']], $v);
					}
				}

				//accusés récept
				if (isset($data['acks'])) {
					if (is_array($data['acks'])) {
						foreach ($data['acks'] as $v) {
							$v['phoneNumberFr'] = preg_replace("#^33([0-9]{9})$#", "0$1", $v['phoneNumber']);
							$dataAcc[$v['phoneNumberFr']] = $v;
						}

						//intégartion Acc recep
						foreach ($dataw as $k => $v) {
							if (isset($v['mobilePhone'])) {
								if (isset($dataAcc[$v['mobilePhone']])) {
									$dataw[$k]['accRecep'] = $dataAcc[$v['mobilePhone']];
								}
							}
						}
					}
				}

				//générer le tableau de retour, partie sms
				$i = 0;
				foreach ($dataw as $k => $v) {
					$smsReturnTab[$i] = array(
						"id" => $v['id'],
						"identite" => $v['identite'],
						"typeCs" => $v['type'],
						"heureRdv" => $v['heure'],
					);
					if (isset($v['mobilePhone'])) $smsReturnTab[$i]['telFr'] = $v['mobilePhone'];
					if (isset($v['telDisplay'])) $smsReturnTab[$i]['telFrDisplay'] = $v['telDisplay'];
					if (isset($v['accRecep'])) {
						$smsReturnTab[$i]['accRecepStatus'] = $v['accRecep']['status'];
						$smsReturnTab[$i]['accRecepComment'] = $v['accRecep']['comment'];
						$smsReturnTab[$i]['accRecepRecepDate'] = $v['accRecep']['receptionDate'];
					}
					$i++;
				}

				//générer le tableau de retour, partie sms
				$campaignReturnTab = @array(
					'status' => $data['code'],
					'statusText' => $data['description'],
					'statusSimulate' => $data['simulate'],
					'invalidNumbers' => $data['invalidNumbers'],
					'campaignId' => $data['campaignId'],
					'creditsUsed' => $data['cost'],
					'nbContacts' => $data['nbContacts'],
					'nbSms' => $data['nbSms'],
					'credits' => $data['balance'],
				);

				return array("campaign" => $campaignReturnTab, "sms" => $smsReturnTab);

			}
		}
	}

	/**
	 * Envoi d'un SMS simple - destinataire unique
	 * @param int $simulate
	 * @return bool|string
	 */
	public function sendSMSSimple($simulate = 0)
	{
		global $p;

		$nbDestinataires = count($this->_destinataires);
		if (is_array($this->_destinataires) && $nbDestinataires == 1) {
			try {
				$this->_generateCampaign();
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}

			$url = 'https://api.allmysms.com/sms/send/';

			$auth_token = base64_encode($p['config']['allMySmsLogin'] . ':' . $p['config']['allMySmsApiKey']);

			$array_data = array(
				'text' => $this->_campaign_data['DATA']['MESSAGE'] . " \r\nStop au 36180",
				'to' => str_replace("+", "", $this->_campaign_data['DATA']['SMS'][0]["mobilePhone"]),
				'from' => $this->_campaign_data['DATA']['TPOA'],
				'campaignName' => $this->_campaign_data['DATA']['CAMPAIGN_NAME'],
				'alerting' => 1,
				'simulate' => intval(!empty($simulate)),
			);
			$post_data = json_encode($array_data);

			$ch = curl_init();

			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $post_data,
				CURLOPT_HTTPHEADER => array(
					"Authorization: Basic " . $auth_token,
					"Content-Type: application/json",
					"cache-control: no-cache"
				),
			));

			$result = curl_exec($ch);
			$err = curl_error($ch);
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($err || $responseCode != 201) {
				$apiResponse = [];
				$apiResponse['status'] = '0';
				$apiResponse['statusText'] = "Une erreur s'est produite lors de l'envoi.";

				if (isset($result['description']) && !empty($result['description']))
					$apiResponse['statusText'] .= "Description : ".$result['description'];

				$result = json_encode($apiResponse);
			}
		} else {
			$result = [];

			if ($nbDestinataires < 1) {
				$result['status'] = '0';
				$result['statusText'] = "Pas de destinataires pour cette campagne - API AllMySMS non sollicitée";
				$result = json_encode($result);
			} elseif ($nbDestinataires > 1) {
				$result['status'] = '0';
				$result['statusText'] = "Votre envoi contient plus d'un destinataire , veuillez utiliser la fonction sendCampaign";
				$result = json_encode($result);
			}
		}

		$this->_campaign_answer = $result;

		return $result;
	}
}
