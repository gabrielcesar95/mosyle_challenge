<?php

namespace Source\Models;

use Source\Core\Model;
use Source\Core\Session;

class Auth extends Model
{
	/**
	 * Auth constructor.
	 */
	public function __construct()
	{
		parent::__construct("users", ["id"], ["email", "password"]);
	}

	/**
	 * @return null|User
	 */
	public static function user(): ?User
	{
		$session = new Session();
		if (!$session->has("authUser")) {
			return null;
		}

		return (new User())->findById($session->authUser);
	}

	/**
	 * log-out
	 */
	public static function logout(): void
	{
		$session = new Session();
		$session->unset("authUser");
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @return User|null
	 */
	public function attempt(string $email, string $password): ?User
	{
		if (!is_email($email)) {
			$this->message->warning("O e-mail informado não é válido");
			return null;
		}

		if (!is_passwd($password)) {
			$this->message->warning("A senha informada não é válida");
			return null;
		}

		$user = (new User())->findByEmail($email);

		if (!$user) {
			$this->message->error("O e-mail informado não está cadastrado");
			return null;
		}

		if (!passwd_verify($password, $user->password)) {
			$this->message->error("A senha informada não confere");
			return null;
		}

		if (passwd_rehash($user->password)) {
			$user->password = $password;
			$user->save();
		}

		return $user;
	}

	public function attemptByToken(string $token) :?User
	{
		$user = (new User())->findByToken($token);

		if (!$user) {
			$this->message->error("Nenhum usuário encontrado com o token informado");
			return null;
		}

		return $user;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param bool $save
	 * @return bool
	 */
	public function login(string $email, string $password, bool $save = false): bool
	{
		$user = $this->attempt($email, $password);
		if (!$user) {
			return false;
		}

		if ($save) {
			setcookie("authEmail", $email, time() + 604800, "/");
		} else {
			setcookie("authEmail", null, time() - 3600, "/");
		}

		//LOGIN
		(new Session())->set("authUser", $user->id);
		return true;
	}

	/**
	 * @param string $email
	 * @return bool
	 */
	public function forget(string $email): bool
	{
		$user = (new User())->findByEmail($email);

		if (!$user) {
			$this->message->warning("O e-mail informado não está cadastrado.");
			return false;
		}

		$user->forget = md5(uniqid(rand(), true));
		$user->save();

		return true;
	}

	/**
	 * @param string $email
	 * @param string $code
	 * @param string $password
	 * @param string $passwordRe
	 * @return bool
	 */
	public function reset(string $email, string $code, string $password, string $passwordRe): bool
	{
		$user = (new User())->findByEmail($email);

		if (!$user) {
			$this->message->warning("A conta para recuperação não foi encontrada.");
			return false;
		}

		if ($user->forget != $code) {
			$this->message->error("Desculpe, mas o código de verificação não é válido.");
			return false;
		}

		if (!is_passwd($password)) {
			$min = CONF_PASSWD_MIN_LEN;
			$max = CONF_PASSWD_MAX_LEN;
			$this->message->info("Sua senha deve ter entre {$min} e {$max} caracteres.");
			return false;
		}

		if ($password != $passwordRe) {
			$this->message->warning("Você informou duas senhas diferentes.");
			return false;
		}

		$user->password = $password;
		$user->forget = null;
		$user->save();
		return true;
	}
}
