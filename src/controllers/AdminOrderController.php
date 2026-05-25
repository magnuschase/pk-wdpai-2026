<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/OrdersRepository.php';
require_once __DIR__ . '/../repositories/UsersRepository.php';
require_once __DIR__ . '/../repositories/ObjectTypesRepository.php';
require_once __DIR__ . '/../repositories/GlazesRepository.php';

class AdminOrderController extends AppController
{
	private OrdersRepository $ordersRepository;
	private UsersRepository $usersRepository;
	private ObjectTypesRepository $objectTypesRepository;
	private GlazesRepository $glazesRepository;

	public function __construct()
	{
		$this->ordersRepository = new OrdersRepository();
		$this->usersRepository = UsersRepository::getInstance();
		$this->objectTypesRepository = new ObjectTypesRepository();
		$this->glazesRepository = new GlazesRepository();
	}

	public function index(): void
	{
		$this->requireAdmin();

		$orders = $this->ordersRepository->getAll();

		// Enrich each order with customer name and object type label
		$users = [];
		$objectTypes = [];

		foreach ($orders as $order) {
			$uid = $order->getUserId();
			if (!isset($users[$uid])) {
				$users[$uid] = $this->usersRepository->getUserById($uid);
			}

			$otId = $order->getObjectTypeId();
			if ($otId && !isset($objectTypes[$otId])) {
				$objectTypes[$otId] = $this->objectTypesRepository->getById($otId);
			}
		}

		$this->render('admin/orders', compact('orders', 'users', 'objectTypes'));
	}

	public function show(?int $id): void
	{
		$this->requireAdmin();

		$order = $this->ordersRepository->getById($id);

		if (!$order) {
			http_response_code(404);
			include 'public/views/404.html';
			return;
		}

		$customer = $this->usersRepository->getUserById($order->getUserId());
		$objectType = $order->getObjectTypeId()
			? $this->objectTypesRepository->getById($order->getObjectTypeId())
			: null;
		$glaze = $order->getGlazeId()
			? $this->glazesRepository->getById($order->getGlazeId())
			: null;
		$notes = $this->ordersRepository->getNotesForOrder($id);
		$images = $this->ordersRepository->getImagesForOrder($id);

		$this->render('admin/order-detail', compact(
			'order',
			'customer',
			'objectType',
			'glaze',
			'notes',
			'images'
		));
	}

	// POST /admin/orders/{id}/status
	public function updateStatus(?int $id): void
	{
		$this->requireAdmin();

		if (!$this->isPost()) {
			$this->json(['error' => 'Method not allowed'], 405);
		}

		$status = $_POST['status']           ?? null;
		$priceAdjustment = $_POST['price_adjustment'] ?? null;

		$allowed = ['pending', 'approved', 'in_progress', 'completed', 'rejected'];
		if (!in_array($status, $allowed, true)) {
			$this->json(['error' => 'Invalid status'], 400);
		}

		$this->ordersRepository->updateStatus(
			$id,
			$status,
			$priceAdjustment !== null && $priceAdjustment !== '' ? (float) $priceAdjustment : null
		);

		$url = "http://$_SERVER[HTTP_HOST]";
		header("Location: {$url}/admin/orders/{$id}");
		exit();
	}

	// POST /admin/orders/{id}/note
	public function addNote(?int $id): void
	{
		$this->requireAdmin();

		if (!$this->isPost()) {
			$this->json(['error' => 'Method not allowed'], 405);
		}

		$noteType = $_POST['note_type'] ?? 'internal';
		$content = trim($_POST['content'] ?? '');
		$authorId = (int) $_SESSION['user_id'];

		if ($content === '') {
			$url = "http://$_SERVER[HTTP_HOST]";
			header("Location: {$url}/admin/orders/{$id}");
			exit();
		}

		$this->ordersRepository->addNote($id, $authorId, $noteType, $content);

		$url = "http://$_SERVER[HTTP_HOST]";
		header("Location: {$url}/admin/orders/{$id}");
		exit();
	}
}
