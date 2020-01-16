<?php

namespace Source\Models;

use Source\Core\Model;

class User extends Model
{
	/**
	 * User constructor.
	 */
	public function __construct()
	{
		parent::__construct("users", ["id"], ["name", "email", "password"]);
	}

	/**
	 * @param string $name
	 * @param string $email
	 * @param string $password
	 * @return User
	 */
	public function bootstrap(
		string $name,
		string $email,
		string $password
	): User {
		$this->name = $name;
		$this->email = $email;
		$this->password = $password;
		return $this;
	}

	/**
	 * @param string $email
	 * @param string $columns
	 * @return null|User
	 */
	public function findByEmail(string $email, string $columns = "*"): ?User
	{
		$find = $this->find("email = :email", "email={$email}", $columns);
		return $find->fetch();
	}
	/**
	 * @param string $token
	 * @param string $columns
	 * @return null|User
	 */
	public function findByToken(string $token, string $columns = "*"): ?User
	{
		$find = $this->find("token = :token", "token={$token}", $columns);
		return $find->fetch();
	}

	/**
	 * @return bool
	 */
	public function save(): bool
	{
		if (!$this->required()) {
			$this->message->warning("Nome, email e senha são obrigatórios");
			return false;
		}

		if (!is_email($this->email)) {
			$this->message->warning("O e-mail informado não tem um formato válido");
			return false;
		}

		if (!is_passwd($this->password)) {
			$min = CONF_PASSWD_MIN_LEN;
			$max = CONF_PASSWD_MAX_LEN;
			$this->message->warning("A senha deve ter entre {$min} e {$max} caracteres");
			return false;
		} else {
			$this->password = passwd($this->password);
		}

		/** User Update */
		if (!empty($this->id)) {
			$userId = $this->id;

			if ($this->find("email = :e AND id != :i", "e={$this->email}&i={$userId}", "id")->fetch()) {
				$this->message->warning("O e-mail informado já está cadastrado");
				return false;
			}

			$this->update($this->safe(), "id = :id", "id={$userId}");
			if ($this->fail()) {
				$this->message->error("Erro ao atualizar, verifique os dados");
				return false;
			}
		}

		/** User Create */
		if (empty($this->id)) {
			if ($this->findByEmail($this->email, "id")) {
				$this->message->warning("O e-mail informado já está cadastrado");
				return false;
			}

			$userId = $this->create($this->safe());
			if ($this->fail()) {
				$this->message->error("Erro ao cadastrar, verifique os dados");
				return false;
			}
		}

		$this->data = ($this->findById($userId))->data();
		return true;
	}
}
