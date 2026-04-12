<?php

require_once 'AppController.php';

class SecurityController extends AppController {

	public function login() {
		// TODO: check if user exists

		if ($this->isPost()) {
			// return $this->render("dashboard");

			$url = "http://$_SERVER[HTTP_HOST]";
			header("Location: {$url}/dashboard");
			exit();
		}

		return $this->render("login");
	}

	public function register() {
		if ($this->isPost()) {
			// TODO: register action
			var_dump($_POST);
		}

		return $this->render("register");
	}
}