<?php

require_once 'AppController.php';

class SecurityController extends AppController
{

	public function login()
	{
		if (!$this->isPost()) {
			return $this->render('login');
		}

		$email = $_POST["email"] ?? '';
		$password = $_POST["password"] ?? '';

		if (empty($email) || empty($password)) {
			return $this->render('login', ['messages' => 'Fill all fields']);
		}

		//TODO get from database user with given email, userRepository can be created once (singleton), and assigned in constructor
		$userRepository = new UsersRepository();
		$user = $userRepository->getUserByEmail($email);

		if (!$user) {
			return $this->render('login', ['messages' => 'User not found']);
		}

		if (!password_verify($password, $user['password'])) {
			return $this->render('login', ['messages' => 'Wrong password']);
		}

		// TODO możemy przechowywać sesje użytkowika lub token
		// setcookie("username", $user['email'], time() + 3600, '/');
		$url = "http://$_SERVER[HTTP_HOST]";
		header("Location: {$url}/dashboard");
	}

	public function register()
	{
		$userRepository = new UsersRepository();

		if ($this->isPost()) {
			$email = trim($_POST['email'] ?? '');
			$password = $_POST['password'] ?? '';
			$password2 = $_POST['password2'] ?? '';
			$username = $_POST['username'] ?? '';

			if (empty($email) || empty($password) || empty($username)) {
				return $this->render('register', ['messages' => 'Fill all fields']);
			}

			if ($password !== $password2) {
				return $this->render('register', ['messages' => 'Passwords do not match']);
			}

			$user = $userRepository->getUserByEmail($email);
			if ($user) {
				return $this->render("register", ["messages" => "User exists"]);
			}

			$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
			$userRepository->createUser($username, $email, $hashedPassword);

			$url = "http://$_SERVER[HTTP_HOST]";
			header("Location: {$url}/login");
			return;
		}

		return $this->render("register");
	}
}
