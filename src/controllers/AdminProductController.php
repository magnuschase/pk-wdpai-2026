<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repositories/GalleryItemsRepository.php';
require_once __DIR__ . '/../repositories/CategoriesRepository.php';

class AdminProductController extends AppController
{
    private GalleryItemsRepository $galleryItemsRepository;
    private CategoriesRepository $categoriesRepository;

    private const UPLOAD_DIR     = 'public/images/gallery/';
    private const ALLOWED_EXT    = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct()
    {
        $this->galleryItemsRepository = new GalleryItemsRepository();
        $this->categoriesRepository   = new CategoriesRepository();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $products   = $this->galleryItemsRepository->getAll();
        $categories = $this->categoriesRepository->getAll();

        $this->render('admin/products', compact('products', 'categories'));
    }

    // POST /admin/products/create
    public function create(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $name        = trim($_POST['name']        ?? '');
        $price       = (float) ($_POST['price']   ?? 0);
        $categoryId  = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;
        $material    = trim($_POST['material']     ?? '') ?: null;
        $description = trim($_POST['description']  ?? '') ?: null;

        if (!$name || $price <= 0) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/admin/products?error=missing_fields");
            exit();
        }

        $imageUrl = $this->handleUpload();

        $this->galleryItemsRepository->create($name, $price, $categoryId, $material, $imageUrl, $description);

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin/products");
        exit();
    }

    // POST /admin/products/{id}/update
    public function update(?int $id): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $product = $this->galleryItemsRepository->getById($id);
        if (!$product) {
            http_response_code(404);
            include 'public/views/404.html';
            exit();
        }

        $name        = trim($_POST['name']        ?? '');
        $price       = (float) ($_POST['price']   ?? 0);
        $categoryId  = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;
        $material    = trim($_POST['material']     ?? '') ?: null;
        $description = trim($_POST['description']  ?? '') ?: null;

        if (!$name || $price <= 0) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/admin/products?error=missing_fields");
            exit();
        }

        $imageUrl = $this->handleUpload() ?? $product->getImageUrl();

        $this->galleryItemsRepository->update($id, $name, $price, $categoryId, $material, $imageUrl, $description);

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin/products");
        exit();
    }

    // POST /admin/products/{id}/delete
    public function delete(?int $id): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $this->galleryItemsRepository->delete($id);

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin/products");
        exit();
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['image']['tmp_name'])) {
            return null;
        }

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return null;
        }

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        $filename = uniqid('product_') . '.' . $ext;
        $dest     = self::UPLOAD_DIR . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            return null;
        }

        return '/' . $dest;
    }
}
