<?php

namespace Source\App;

use Source\Core\Controller;
use Source\Models\Auth;

class Api extends Controller
{
	/** @var \Source\Models\User|null */
	protected $user;

	/** @var array|false */
	protected $headers;

	/** @var array|null */
	protected $response;

	/**
	 * Api constructor.
	 * @throws \Exception
	 */
	public function __construct()
	{
		parent::__construct("/");

		header('Content-Type: application/json; charset=UTF-8');
		$this->headers = getallheaders();
	}

	/**
	 * @param int $code
	 * @param string|null $type
	 * @param string|null $message
	 * @param string $rule
	 * @return Api
	 */
	protected function call(int $code, string $type = null, string $message = null, string $rule = "errors"): Api
	{
		http_response_code($code);

		if (!empty($type)) {
			$this->response = [
				$rule => [
					"type" => $type,
					"message" => (!empty($message) ? $message : null)
				]
			];
		}
		return $this;
	}

	/**
	 * @param array|null $response
	 * @return Api
	 */
	protected function back(array $response = null): Api
	{
		if (!empty($response)) {
			$this->response = (!empty($this->response) ? array_merge($this->response, $response) : $response);
		}

		echo json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		return $this;
	}

	/**
	 * @return bool
	 */
	protected function auth(): bool
	{
		if (empty($this->headers["token"])) {
			$this->call(
				400,
				"auth_empty",
				"Por favor informe seu token"
			)->back();
			return false;
		}

		$auth = new Auth();
		$user = $auth->attemptByToken($this->headers["token"]);

		if (!$user) {
			$this->call(
				401,
				"unauthorized",
				$auth->message()->getText()
			)->back();
			return false;
		}

		$this->user = $user;
		return true;
	}

	protected function authByEmail(array $data)
	{
		if (empty($data["email"]) || empty($data["password"])) {
			$this->call(
				400,
				"auth_empty",
				"Por favor informe seu e-mail e senha"
			)->back();
			return false;
		}

		$auth = new Auth();
		$user = $auth->attempt($data["email"], $data["password"]);

		if (!$user) {
			$this->call(
				401,
				"unauthorized",
				$auth->message()->getText()
			)->back();
			return false;
		}


		$this->user = $user;
		return true;
	}
}
