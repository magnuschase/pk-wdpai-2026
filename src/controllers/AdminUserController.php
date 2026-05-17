<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/UsersRepository.php';
require_once __DIR__ . '/../repositories/UserProfilesRepository.php';

class AdminUserController extends AppController
{
    private UsersRepository $usersRepository;
    private UserProfilesRepository $userProfilesRepository;

    public function __construct()
    {
        $this->usersRepository        = new UsersRepository();
        $this->userProfilesRepository = new UserProfilesRepository();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $users      = $this->usersRepository->getUsers();
        $totalUsers = count($users);
        $activeUsers = count(array_filter($users, fn($u) => $u->isActive()));
        $adminUsers  = count(array_filter($users, fn($u) => $u->isAdmin()));

        $this->render('admin/users', compact('users', 'totalUsers', 'activeUsers', 'adminUsers'));
    }

    // POST /admin/users/{id}/update
    public function update(?int $id): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $isAdmin  = isset($_POST['is_admin'])  && $_POST['is_admin']  === '1';
        $isActive = isset($_POST['is_active']) && $_POST['is_active'] === '1';

        $this->usersRepository->updateUser($id, $isAdmin, $isActive);

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin/users");
        exit();
    }

    // POST /admin/users/create
    public function create(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $isAdmin  = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

        if (!$username || !$email || !$password) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/admin/users?error=missing_fields");
            exit();
        }

        if ($this->usersRepository->getUserByEmail($email)) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/admin/users?error=email_taken");
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->usersRepository->createUser($username, $email, $hashedPassword, $isAdmin);

        $newUser = $this->usersRepository->getUserByEmail($email);
        if ($newUser) {
            $this->userProfilesRepository->create($newUser->getId());
        }

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin/users");
        exit();
    }

    // POST /admin/users/{id}/delete
    public function delete(?int $id): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        // Prevent self-deletion
        if ($id === (int) $_SESSION['user_id']) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/admin/users?error=cannot_delete_self");
            exit();
        }

        $this->usersRepository->deleteUser($id);

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin/users");
        exit();
    }
}
