<?php

namespace Source\App;

use Source\Models\Drink;
use Source\Models\User;
use Source\Support\Pager;

class Drinks extends Api
{
	/**
	 * Drinks constructor.
	 * @throws \Exception
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function drink(array $data)
	{
		$auth = $this->auth();
		if (!$auth) {
			exit;
		}

		$data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

		if (!isset($data['id']) || !$data['id']) {
			$this->call(
				422,
				"unprocessable_entity",
				'O ID do usuário é obrigatório'
			)->back();
			return;
		}

		if (!filter_var($data['id'], FILTER_VALIDATE_INT)) {
			$this->call(
				422,
				"unprocessable_entity",
				'O ID de usuário deve ser um número inteiro'
			)->back();
			return;
		}

		$user = (new User())->findById($data['id']);

		if (!$user) {
			$this->call(
				404,
				"not_found",
				'Usuário não encontrado'
			)->back();
		}

		if (!isset($data['drink_ml']) || !$data['drink_ml']) {
			$this->call(
				422,
				"unprocessable_entity",
				'Os ml consumidos são obrigatórios'
			)->back();
			return;
		}

		if (!filter_var($data['drink_ml'], FILTER_VALIDATE_INT)) {
			$this->call(
				422,
				"unprocessable_entity",
				'Os ml consumidos devem ser um número inteiro'
			)->back();
			return;
		}


		$drink = new Drink();
		$drink->user_id = $user->id;
		$drink->ml = $data['drink_ml'];

		if (!$drink->save()) {
			$this->call(
				400,
				"invalid_data",
				$drink->message()->getText()
			)->back();
			return;
		}

		$user = $user->data();
		$drink = $drink->data();

		unset($user->password, $user->token, $user->created_at, $user->updated_at);
		unset($drink->created_at);

		$drink_counter = (new Drink())->find('user_id = :user_id AND DATE(created_at) = DATE_FORMAT(NOW(), "%Y-%m-%d")', "user_id={$user->id}", 'SUM(ml) AS drink_counter');

		if ($drink_counter->count()) {
			$user->drink_counter = $drink_counter->fetch()->drink_counter;
		}

		$response["message"] = "Consumo de água cadastrado com sucesso.";
		$response["user"] = $user;
		$response["drink"] = $drink;

		$this->back($response);
	}

	public function userHistory(array $data): void
	{
		$auth = $this->auth();
		if (!$auth) {
			exit;
		}

		$data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

		if (!$data['id']) {
			$this->call(
				422,
				"unprocessable_entity",
				'O ID do usuário é obrigatório'
			)->back();
			return;
		}

		if (!filter_var($data['id'], FILTER_VALIDATE_INT)) {
			$this->call(
				422,
				"unprocessable_entity",
				'O ID do usuário deve ser um número inteiro'
			)->back();
			return;
		}

		$user = (new User())->findById($data['id']);

		if (!$user) {
			$this->call(
				404,
				"not_found",
				'Usuário não encontrado'
			)->back();
			return;
		}

		$user = $user->data();

		$where = "user_id = :user_id";
		$params = "user_id={$user->id}";
		$values = $this->headers;

		if (!empty($values["date"]) && $date = filter_var($values["date"], FILTER_SANITIZE_STRING)) {
			if (!preg_match('/^(19|20)\d\d[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])$/', $values['date'])) {
				$this->call(
					422,
					"unprocessable_entity",
					"Data inválida. A Data deve ter o formato YYYY-MM-DD"
				)->back();
				return;
			}

			$where .= " AND DATE(created_at) = :date";
			$params .= "&date={$date}";
		}

		$drinks = (new Drink())->find($where, $params);

		if (!$drinks->count()) {
			$this->call(
				404,
				"not_found",
				"Nenhum registro encontrado"
			)->back(["results" => 0]);
			return;
		}

		$page = (!empty($values["page"]) ? $values["page"] : 1);
		$pager = new Pager(url("/users/"));
		$pager->pager($drinks->count(), CONF_PAGER_RESULTS, $page);

		$response["results"] = $drinks->count();
		$response["page"] = $pager->page();
		$response["pages"] = $pager->pages();

		unset($user->password, $user->token);
		$response["user"] = $user;

		foreach ($drinks->limit($pager->limit())->offset($pager->offset())->order("created_at DESC")->fetch(true) as $row) {
			$drink = $row->data();
			unset($drink->user_id);

			$response["drinks"][] = $drink;
		}

		$this->back($response);
		return;
	}
}
