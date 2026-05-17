<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/OrdersRepository.php';
require_once __DIR__ . '/../repositories/ObjectTypesRepository.php';
require_once __DIR__ . '/../repositories/GlazesRepository.php';

class OrderController extends AppController
{
    private OrdersRepository $ordersRepository;
    private ObjectTypesRepository $objectTypesRepository;
    private GlazesRepository $glazesRepository;

    public function __construct()
    {
        $this->ordersRepository      = new OrdersRepository();
        $this->objectTypesRepository = new ObjectTypesRepository();
        $this->glazesRepository      = new GlazesRepository();
    }

    public function wizard(): void
    {
        $this->requireLogin();

        if ($this->isPost()) {
            $this->handleSubmit();
            return;
        }

        $objectTypes = $this->objectTypesRepository->getAll();
        $glazes      = $this->glazesRepository->getAll();

        $this->render('order-wizard', compact('objectTypes', 'glazes'));
    }

    private function handleSubmit(): void
    {
        $userId = (int) $_SESSION['user_id'];

        $objectTypeId     = ($v = (int) ($_POST['object_type_id'] ?? 0)) > 0 ? $v : null;
        $glazeId          = ($v = (int) ($_POST['glaze_id']       ?? 0)) > 0 ? $v : null;
        $sizeType         = $_POST['size_type']         ?? null;
        $customDimensions = trim($_POST['custom_dimensions'] ?? '') ?: null;
        $specialRequests  = trim($_POST['special_requests']  ?? '') ?: null;
        $budgetMin        = ($v = $_POST['budget_min'] ?? '') !== '' ? (float) $v : null;
        $budgetMax        = ($v = $_POST['budget_max'] ?? '') !== '' ? (float) $v : null;

        $orderId = $this->ordersRepository->create(
            $userId,
            $objectTypeId,
            $glazeId,
            $sizeType,
            $customDimensions,
            $specialRequests,
            $budgetMin,
            $budgetMax
        );

        // Handle inspiration image uploads (step 5 of the wizard)
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = 'public/images/orders/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $ext         = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                $allowed     = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed, true)) {
                    continue;
                }

                $filename    = uniqid('order_' . $orderId . '_') . '.' . $ext;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($tmpName, $destination)) {
                    $this->ordersRepository->addImage($orderId, '/' . $destination);
                }
            }
        }

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/gallery");
        exit();
    }
}
