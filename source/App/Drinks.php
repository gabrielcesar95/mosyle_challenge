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

		if($drink_counter->count()){
			$user->drink_counter = $drink_counter->fetch()->drink_counter;
		}

		$response["message"] = "Consumo de água cadastrado com sucesso.";
		$response["user"] = $user;
		$response["drink"] = $drink;

		$this->back($response);
	}
}
