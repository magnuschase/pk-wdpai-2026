<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/GalleryItemsRepository.php';
require_once __DIR__ . '/../repositories/CategoriesRepository.php';

class GalleryController extends AppController
{
    private GalleryItemsRepository $galleryItemsRepository;
    private CategoriesRepository $categoriesRepository;

    public function __construct()
    {
        $this->galleryItemsRepository = new GalleryItemsRepository();
        $this->categoriesRepository   = new CategoriesRepository();
    }

    public function index(): void
    {
        $this->requireLogin();

        $categorySlug = $_GET['category'] ?? null;
        $categories   = $this->categoriesRepository->getAll();

        $activeCategory = null;
        $categoryId     = null;

        if ($categorySlug) {
            $activeCategory = $this->categoriesRepository->getBySlug($categorySlug);
            $categoryId     = $activeCategory?->getId();
        }

        $items        = $this->galleryItemsRepository->getAll($categoryId);
        $favouriteIds = array_map(
            fn($item) => $item->getId(),
            $this->galleryItemsRepository->getFavouritesByUserId($_SESSION['user_id'])
        );

        $this->render('gallery', compact('categories', 'items', 'favouriteIds', 'activeCategory'));
    }

    // POST /gallery/favourite - Fetch API endpoint, returns JSON
    public function toggleFavourite(): void
    {
        $this->requireLogin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $data   = json_decode(file_get_contents('php://input'), true);
        $itemId = (int) ($data['item_id'] ?? 0);

        if (!$itemId) {
            $this->json(['error' => 'Missing item_id'], 400);
        }

        $userId = (int) $_SESSION['user_id'];

        if ($this->galleryItemsRepository->isFavouritedBy($userId, $itemId)) {
            $this->galleryItemsRepository->removeFavourite($userId, $itemId);
            $this->json(['favourited' => false]);
        } else {
            $this->galleryItemsRepository->addFavourite($userId, $itemId);
            $this->json(['favourited' => true]);
        }
    }
}
