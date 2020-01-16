<?php

namespace Source\App;

use Source\Models\User;

class Users extends Api
{
	/**
	 * Users constructor.
	 * @throws \Exception
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param array $data
	 */
	public function create(array $data): void
	{
		$data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

		$this->user = new User();

		if (!isset($data['name']) || !$data['name']) {
			$this->call(
				422,
				"unprocessable_entity",
				'O nome de usuário é obrigatório'
			)->back();
			return;
		}

		if (!is_email($data['email'])) {
			$this->call(
				422,
				"unprocessable_entity",
				'O e-mail informado é inválido'
			)->back();
			return;
		}

		if (!is_passwd($data['password'])) {
			$this->call(
				422,
				"unprocessable_entity",
				"A senha informada é inválida. Senhas devem ter entre " . CONF_PASSWD_MIN_LEN . " e " . CONF_PASSWD_MAX_LEN . " caracteres"
			)->back();
			return;
		}

		$this->user->name = (!empty($data["name"]) ? $data["name"] : $this->user->name);
		$this->user->email = (!empty($data["email"]) ? $data["email"] : $this->user->email);
		$this->user->password = (!empty($data["password"]) ? passwd($data["password"]) : $this->user->password);
		$this->user->token = uniqid();

		if (!$this->user->save()) {
			$this->call(
				400,
				"invalid_data",
				$this->user->message()->getText()
			)->back();
			return;
		}

		$user = $this->user->data();
		unset($user->password, $user->token, $user->created_at, $user->updated_at);

		$response["message"] = 'Usuário cadastrado com sucesso.';
		$response["user"] = $user;

		$this->back($response);
	}
}
