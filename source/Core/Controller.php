<?php

namespace Source\Core;

use Source\Support\Message;

class Controller
{
	/** @var Message */
	protected $message;

	public function __construct()
	{
		$this->message = new Message();
	}
}
