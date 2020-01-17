<?php

namespace Source\Models;

use Source\Core\Model;

class Drink extends Model
{
	/**
	 * Drink constructor.
	 */
	public function __construct()
	{
		parent::__construct("drinks", ["id"], ["user_id", "ml"]);
	}

	/**
	 * @param int $user_id
	 * @param int $ml
	 * @return Drink
	 */
	public function bootstrap(
		string $user_id,
		string $ml
	): Drink {
		$this->user_id = $user_id;
		$this->ml = $ml;
		return $this;
	}

	/**
	 * @param string $user_id
	 * @param string $columns
	 * @return null|Drink
	 */
	public function findByUserId(int $user_id, string $columns = "*"): ?Drink
	{
		$find = $this->find("user_id = :user_id", "user_id={$user_id}", $columns);
		return $find->fetch();
	}

	/**
	 * @return bool
	 */
	public function save(): bool
	{
		if (!$this->required()) {
			$this->message->warning("ID do usuário e mls consumidos são obrigatórios");
			return false;
		}

		if (!filter_var($this->user_id, FILTER_VALIDATE_INT)) {
			$this->message->warning("O ID de usuário informado não tem um formato válido");
			return false;
		}

		if (!filter_var($this->ml, FILTER_VALIDATE_INT)) {
			$this->message->warning("Os mls informados não tem um formato válido");
			return false;
		}

		/** Drink Update */
		if (!empty($this->id)) {
			$drinkId = $this->id;

			$this->update($this->safe(), "id = :id", "id={$drinkId}");
			if ($this->fail()) {
				$this->message->error("Erro ao atualizar, verifique os dados");
				return false;
			}
		}

		/** Drink Create */
		if (empty($this->id)) {
			$drinkId = $this->create($this->safe());
			if ($this->fail()) {
				$this->message->error("Erro ao cadastrar, verifique os dados");
				return false;
			}
		}

		$this->data = ($this->findById($drinkId))->data();
		return true;
	}
}
