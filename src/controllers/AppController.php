<?php


class AppController
{
	protected function isGet(): bool
	{
		return $_SERVER["REQUEST_METHOD"] === 'GET';
	}

	protected function isPost(): bool
	{
		return $_SERVER["REQUEST_METHOD"] === 'POST';
	}

	protected function render(string $template = null, array $variables = [])
	{
		$templatePath = 'public/views/' . $template . '.html';
		$templatePath404 = 'public/views/404.html';
		$output = "";

		if (file_exists($templatePath)) {
			extract($variables);
			// ["tab_name" => $title]

			// $tab_name = $title

			ob_start();
			include $templatePath;
			$output = ob_get_clean();
		} else {
			ob_start();
			include $templatePath404;
			$output = ob_get_clean();
		}
		echo $output;
	}

	protected function requireLogin(): void
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		if (empty($_SESSION['user_id'])) {
			$url = "http://$_SERVER[HTTP_HOST]";
			header("Location: {$url}/login");
			exit();
		}
	}

	protected function requireAdmin(): void
	{
		$this->requireLogin();

		if (empty($_SESSION['is_admin'])) {
			http_response_code(403);
			include 'public/views/403.html';
			exit();
		}
	}

	protected function ensureCsrfToken(): string
	{
		if (empty($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
		return $_SESSION['csrf_token'];
	}

	protected function verifyCsrfToken(): bool
	{
		$submitted = $_POST['csrf_token'] ?? '';
		return !empty($_SESSION['csrf_token'])
			&& hash_equals($_SESSION['csrf_token'], $submitted);
	}

	protected function json(mixed $data, int $status = 200): void
	{
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($data);
		exit();
	}
}
