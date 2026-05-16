<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/UsersRepository.php';

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

		if (!password_verify($password, $user->getPassword())) {
			return $this->render('login', ['messages' => 'Wrong password']);
		}

		// Create user session
		session_regenerate_id(true); // new session id for security purposes

		$_SESSION['user_id'] = $user->getId();
		$_SESSION['user_email'] = $user->getEmail();
		$_SESSION['user_name'] = $user->getUsername();
		$_SESSION['is_logged_in'] = true;
		$_SESSION['is_admin'] = $user->isAdmin();

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

	public function logout()
	{
		// Check if session is not already started
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// Clear all session data
		$_SESSION = [];

		// Optional: remove client-side session cookie
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				'',
				time() - 42000,
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}

		// Destroy the session
		session_destroy();

		// Redirect to the login page
		$url = "http://$_SERVER[HTTP_HOST]";
		header("Location: {$url}/login");
	}
}
