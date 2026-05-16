<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/UsersRepository.php';

class DashboardController extends AppController
{

	public function dashboard()
	{
		$this->requireLogin();
		// TODO: pobieranie danych z bazy
		// wstawianie zmiennych na widok
		$title = "DASHBOARD";
		$usersRepository = new UsersRepository();
		$users = $usersRepository->getUsers();

		return $this->render("dashboard", ["title" => $title, "users" => $users]);
	}
}
