<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/UsersRepository.php';
require_once __DIR__ . '/../repositories/UserProfilesRepository.php';

class SecurityController extends AppController
{
	private const MAX_LOGIN_ATTEMPTS = 5;
	private const LOCKOUT_SECONDS = 900; // 15 minutes

	private UsersRepository $userRepository;
	private UserProfilesRepository $userProfilesRepository;

	public function __construct()
	{
		$this->userRepository = UsersRepository::getInstance();
		$this->userProfilesRepository = new UserProfilesRepository();
	}

	public function login(): void
	{
		if (!$this->isPost()) {
			if (!empty($_SESSION['is_logged_in'])) {
				$redirect = !empty($_SESSION['is_admin']) ? '/admin/users' : '/gallery';
				$this->redirect($redirect);
			}
			$this->ensureCsrfToken();
			$this->render('login');
			return;
		}

		if (!$this->verifyCsrfToken()) {
			http_response_code(400);
			$this->ensureCsrfToken();
			$this->render('login', ['messages' => 'Invalid request. Please try again.']);
			return;
		}

		// Rate-limit check
		$_SESSION['login_attempts'] ??= 0;
		$_SESSION['login_lockout_until'] ??= 0;

		if (time() < $_SESSION['login_lockout_until']) {
			$wait = (int) ceil(($_SESSION['login_lockout_until'] - time()) / 60);
			$this->ensureCsrfToken();
			$this->render('login', ['messages' => "Too many failed attempts. Try again in {$wait} minute(s)."]);
			return;
		}

		$email = trim($_POST['email']    ?? '');
		$password = $_POST['password'] ?? '';

		if (empty($email) || empty($password)) {
			$this->ensureCsrfToken();
			$this->render('login', ['messages' => 'Please fill in all fields.']);
			return;
		}

		if (strlen($email) > 255 || strlen($password) > 128) {
			$this->ensureCsrfToken();
			$this->render('login', ['messages' => 'Invalid email or password.']);
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->ensureCsrfToken();
			$this->render('login', ['messages' => 'Invalid email or password.']);
			return;
		}

		$user = $this->userRepository->getUserByEmail($email);

		if (!$user || !password_verify($password, $user->getPassword())) {
			$_SESSION['login_attempts']++;
			if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
				$_SESSION['login_lockout_until'] = time() + self::LOCKOUT_SECONDS;
				$_SESSION['login_attempts'] = 0;
			}
			error_log(sprintf(
				'[AUTH_FAIL] %s | email: %s | IP: %s | attempt: %d',
				date('Y-m-d H:i:s'),
				$email,
				$_SERVER['REMOTE_ADDR'] ?? 'unknown',
				$_SESSION['login_attempts']
			));
			$this->ensureCsrfToken();
			$this->render('login', ['messages' => 'Invalid email or password.']);
			return;
		}

		// Successful login
		$_SESSION['login_attempts'] = 0;
		$_SESSION['login_lockout_until'] = 0;
		session_regenerate_id(true);

		$_SESSION['user_id'] = $user->getId();
		$_SESSION['user_email'] = $user->getEmail();
		$_SESSION['user_name'] = $user->getUsername();
		$_SESSION['is_logged_in'] = true;
		$_SESSION['is_admin'] = $user->isAdmin();

		$this->redirect($user->isAdmin() ? '/admin/users' : '/gallery');
	}

	public function register(): void
	{
		if (!$this->isPost()) {
			$this->ensureCsrfToken();
			$this->render('register');
			return;
		}

		if (!$this->verifyCsrfToken()) {
			http_response_code(400);
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'Invalid request. Please try again.']);
			return;
		}

		$email = trim($_POST['email']     ?? '');
		$password  = $_POST['password']  ?? '';
		$password2 = $_POST['password2'] ?? '';
		$username  = trim($_POST['username']  ?? '');

		if (empty($email) || empty($password) || empty($username)) {
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'Please fill in all fields.']);
			return;
		}

		if (strlen($email) > 255 || strlen($username) > 100 || strlen($password) > 128) {
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'Input exceeds maximum allowed length.']);
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'Please enter a valid email address.']);
			return;
		}

		if (strlen($password) < 8) {
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'Password must be at least 8 characters.']);
			return;
		}

		if ($password !== $password2) {
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'Passwords do not match.']);
			return;
		}

		if ($this->userRepository->getUserByEmail($email)) {
			$this->ensureCsrfToken();
			$this->render('register', ['messages' => 'An account with this email already exists.']);
			return;
		}

		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		$this->userRepository->createUser($username, $email, $hashedPassword);

		$newUser = $this->userRepository->getUserByEmail($email);
		if ($newUser) {
			$this->userProfilesRepository->create($newUser->getId());
		}

		$this->redirect('/login');
	}

	public function logout(): void
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$_SESSION = [];

		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				'',
				time() - 42000,
				$params['path'],
				$params['domain'],
				$params['secure'],
				$params['httponly']
			);
		}

		session_destroy();

		$this->redirect('/login');
	}

	private function redirect(string $path): void
	{
		$url = "http://$_SERVER[HTTP_HOST]";
		header("Location: {$url}{$path}");
		exit();
	}
}
