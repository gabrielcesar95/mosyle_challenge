<?php

namespace Source\App;

use Source\Models\Drink;
use Source\Models\User;
use Source\Support\Pager;

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

	public function index(): void
	{
		$auth = $this->auth();
		if (!$auth) {
			exit;
		}

		$where = "";
		$params = "";
		$values = $this->headers;

		if (!empty($values["search"]) && $search = filter_var($values["search"], FILTER_SANITIZE_STRING)) {
			$where .= "MATCH(name, email) AGAINST(:search IN BOOLEAN MODE)";
			$params .= "&search=+{$search}*";
		}

		$users = (new User())->find($where, $params, 'id, name, email, created_at, updated_at');

		if (!$users->count()) {
			$this->call(
				404,
				"not_found",
				"Nenhum usuário encontrado"
			)->back(["results" => 0]);
			return;
		}

		if(!isset($values['page']) || !filter_var($values['page'], FILTER_VALIDATE_INT)){
			$this->call(
				422,
				"unprocessable_entity",
				"O valor do header page deve ser um número inteiro"
			)->back();
			return;
		}

		$page = (!empty($values["page"]) ? $values["page"] : 1);
		$pager = new Pager(url("/users/"));
		$pager->pager($users->count(), CONF_PAGER_RESULTS, $page);

		$response["results"] = $users->count();
		$response["page"] = $pager->page();
		$response["pages"] = $pager->pages();

		foreach ($users->limit($pager->limit())->offset($pager->offset())->order("created_at ASC")->fetch(true) as $row) {
			$user = $row->data();
			$user->drink_counter = $this->getDrinkCounter($row);

			$response["users"][] = $user;
		}

		$this->back($response);
		return;
	}

	/**
	 * @param array $data
	 */
	public function login(array $data): void
	{
		$auth = $this->authByEmail($data);
		if (!$auth) {
			exit;
		}

		$user = $this->user->data();
		$user->drink_counter = $this->getDrinkCounter($this->user);
		unset($user->password, $user->created_at, $user->updated_at);

		$response["user"] = $user;

		$this->back($response);
		return;
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

		if ($validate = $this->validateLoginData($data["email"], $data["password"])) {
			$this->call(
				$validate['code'],
				$validate['type'],
				$validate['message']
			)->back();
			return;
		}

		$this->user->name = $data["name"];
		$this->user->email = $data["email"];
		$this->user->password = $data["password"];
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

	/**
	 * @param array $data
	 */
	public function show(array $data): void
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
		$user->drink_counter = $this->getDrinkCounter($this->user);
		unset($user->password, $user->token, $user->created_at, $user->updated_at);

		$response["user"] = $user;

		$this->back($response);
	}

	/**
	 * @param array $data
	 */
	public function update(array $data): void
	{
		$auth = $this->auth();
		if (!$auth) {
			exit;
		}

		$data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

		if ($this->user->id != $data['id']) {
			$this->call(
				403,
				"forbidden",
				'Não é permitido alterar outros usuários'
			)->back();
			return;
		}

		if ($validate = $this->validateLoginData($data["email"], $data["password"])) {
			$this->call(
				$validate['code'],
				$validate['type'],
				$validate['message']
			)->back();
			return;
		}

		$this->user->name = (!empty($data["name"]) ? $data["name"] : $this->user->name);
		$this->user->email = (!empty($data["email"]) ? $data["email"] : $this->user->email);
		$this->user->password = (!empty($data["password"]) ? passwd($data["password"]) : $this->user->password);

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

		$response["message"] = 'Usuário alterado com sucesso.';
		$response["user"] = $user;

		$this->back($response);

	}

	/**
	 * @param array $data
	 */
	public function delete(array $data): void
	{
		$auth = $this->auth();
		if (!$auth) {
			exit;
		}

		$data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

		if ($this->user->id != $data['id']) {
			$this->call(
				403,
				"forbidden",
				'Não é permitido deletar outros usuários'
			)->back();
			return;
		}

		if (!$this->user->destroy()) {
			$this->call(
				400,
				"invalid_data",
				$this->user->message()->getText()
			)->back();
			return;
		}

		$response["message"] = 'Usuário deletado com sucesso.';

		$this->back($response);
	}

	/**
	 * @param $email
	 * @param $password
	 * @return array|null
	 */
	private function validateLoginData($email, $password): ?array
	{
		if (!is_email($email)) {
			return [
				'code' => 422,
				'type' => 'unprocessable_entity',
				'message' => 'O e-mail informado é inválido'
			];
		}

		if (!is_passwd($password)) {
			return [
				'code' => 422,
				'type' => 'unprocessable_entity',
				'message' => "A senha informada é inválida. Senhas devem ter entre " . CONF_PASSWD_MIN_LEN . " e " . CONF_PASSWD_MAX_LEN . " caracteres"
			];
		}

		return null;
	}

	/**
	 * @param User $user
	 * @return int
	 */
	public function getDrinkCounter(User $user): int
	{
		$userData = $user->data();

		if ($userData) {
			$drink_counter = (new Drink())->find('user_id = :user_id AND DATE(created_at) = DATE_FORMAT(NOW(), "%Y-%m-%d")', "user_id={$user->id}", 'SUM(ml) AS drink_counter');

			if ($drink_counter->count()) {
				return (int)$drink_counter->fetch()->drink_counter;
			}

			return 0;
		}

		return 0;
	}
}
