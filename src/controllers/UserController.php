<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/OrdersRepository.php';
require_once __DIR__ . '/../repositories/ObjectTypesRepository.php';
require_once __DIR__ . '/../repositories/GlazesRepository.php';
require_once __DIR__ . '/../repositories/GalleryItemsRepository.php';

class UserController extends AppController
{
    private OrdersRepository $ordersRepository;
    private ObjectTypesRepository $objectTypesRepository;
    private GlazesRepository $glazesRepository;
    private GalleryItemsRepository $galleryItemsRepository;

    public function __construct()
    {
        $this->ordersRepository      = new OrdersRepository();
        $this->objectTypesRepository = new ObjectTypesRepository();
        $this->glazesRepository      = new GlazesRepository();
        $this->galleryItemsRepository = new GalleryItemsRepository();
    }

    public function dashboard(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user_id'];

        $orders = $this->ordersRepository->getByUserId($userId);

        $objectTypes = [];
        $glazes      = [];
        $notes       = [];

        foreach ($orders as $order) {
            $otId = $order->getObjectTypeId();
            if ($otId && !isset($objectTypes[$otId])) {
                $objectTypes[$otId] = $this->objectTypesRepository->getById($otId);
            }

            $gId = $order->getGlazeId();
            if ($gId && !isset($glazes[$gId])) {
                $glazes[$gId] = $this->glazesRepository->getById($gId);
            }

            $notes[$order->getId()] = $this->ordersRepository->getNotesForOrder(
                $order->getId(),
                'customer'
            );
        }

        $favourites = $this->galleryItemsRepository->getFavouritesByUserId($userId);

        $this->render('user/dashboard', compact(
            'orders', 'objectTypes', 'glazes', 'notes', 'favourites'
        ));
    }
}
