<?php

declare(strict_types=1);

require_once __DIR__ . '/../../framework/Autoload.php';

class Product
{
    private ?int $id;
    private string $title;
    private float $price;
    private string $description;
    private int $publishDatetime;
    private User $seller;
    private ?Size $size;
    private ?Category $category;
    private ?Condition $condition;
    private ?Payment $payment;

    public function __construct(?int $id, string $title, float $price, string $description, int $publishDatetime, User $seller, ?Size $size, ?Category $category, ?Condition $condition, ?Payment $payment = null) {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
        $this->description = $description;
        $this->publishDatetime = $publishDatetime;
        $this->seller = $seller;
        $this->size = $size;
        $this->category = $category;
        $this->condition = $condition;
        $this->payment = $payment;
    }

    public function getId(): int {
        return (int)$this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getPrice(): float {
        return (float)$this->price;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPublishDatetime(): int {
        return (int)$this->publishDatetime;
    }

    public function getSeller(): User {
        return $this->seller;
    }

    public function getSize(): ?Size {
        return $this->size;
    }

    public function getCategory(): ?Category {
        return $this->category;
    }

    public function getCondition(): ?Condition {
        return $this->condition;
    }

    public function getPayment(): ?Payment {
        return $this->payment;
    }

    public function setTitle(string $title, PDO $db): void {
        $stmt = $db->prepare("UPDATE Product SET title = :title WHERE id = :id");
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->title = $title;
    }

    public function setPrice(float $price, PDO $db): void {
        $stmt = $db->prepare("UPDATE Product SET price = :price WHERE id = :id");
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->price = (float)$price;
    }

    public function setDescription(string $description, PDO $db): void {
        $stmt = $db->prepare("UPDATE Product SET description = :description WHERE id = :id");
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->description = $description;
    }

    public function setSize(?Size $size, PDO $db): void {
        $sizeName = $size?->getName();
        $stmt = $db->prepare("UPDATE Product SET size = :size WHERE id = :id");
        $stmt->bindParam(":size", $sizeName);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->size = $size;
    }

    public function setCategory(?Category $category, PDO $db): void {
        $categoryName = $category?->getName();
        $stmt = $db->prepare("UPDATE Product SET category = :category WHERE id = :id");
        $stmt->bindParam(":category", $categoryName);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->category = $category;
    }

    public function setCondition(?Condition $condition, PDO $db): void {
        $conditionName = $condition?->getName();
        $stmt = $db->prepare("UPDATE Product SET condition = :condition WHERE id = :id");
        $stmt->bindParam(":condition", $conditionName);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $this->condition = $condition;
    }

    public function upload(PDO $db) {
        $publishDatetime = date('m/d/Y H:i:s', $this->publishDatetime);
        $sellerId = $this->seller->getId();
        $size = $this->size?->getName();
        $category = $this->category?->getName();
        $condition = $this->condition?->getName();
        $paymentId = $this->payment?->getId();

        if ($this->id === null) {
            $stmt = $db->prepare("INSERT INTO Product (title, price, description, publishDatetime, seller, size, category, condition, payment)
                VALUES (:title, :price, :description, :publishDateTime, :seller, :size, :category, :condition, :payment)");
        } else {
            $stmt = $db->prepare("INSERT INTO Product (id, title, price, description, publishDatetime, seller, size, category, condition, payment)
                VALUES (:id, :title, :price, :description, :publishDateTime, :seller, :size, :category, :condition, :payment)");
            $stmt->bindParam(":id", $this->id);
        }

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":publishDateTime", $publishDatetime);
        $stmt->bindParam(":seller", $sellerId);
        $stmt->bindParam(":size", $size);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":condition", $condition);
        $stmt->bindParam(":payment", $paymentId);
        $stmt->execute();
        
        if ($this->id == null) {
            $stmt = $db->prepare("SELECT last_insert_rowid()");
            $stmt->execute();
            $id = $stmt->fetch();
            $this->id = (int)$id[0];
        }
    }

    public function getBrands(PDO $db): array
    {
        $stmt = $db->prepare("SELECT brand FROM ProductBrand WHERE product = :product ORDER BY brand ASC");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();
        $productBrands = $stmt->fetchAll();
        return array_map(function ($productBrand) use ($db) {
            return new Brand($productBrand["brand"]);
        }, $productBrands);
    }

    public function addBrand(PDO $db, Brand $brand): void
    {
        $brandName = $brand->getName();

        $stmt = $db->prepare("INSERT INTO ProductBrand (product, brand) VALUES (:product, :brand)");
        $stmt->bindParam(":product", $this->id);
        $stmt->bindParam(":brand", $brandName);
        $stmt->execute();
    }

    public function removeBrand(PDO $db, Brand $brand): void {
        $brandName = $brand->getName();

        $stmt = $db->prepare("DELETE FROM ProductBrand WHERE product = :product AND brand = :brand");
        $stmt->bindParam(":product", $this->id);
        $stmt->bindParam(":brand", $brandName);
        $stmt->execute();
    }

    public function removeBrands(PDO $db): void {
        $stmt = $db->prepare("DELETE FROM ProductBrand WHERE product = :product");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();
    }

    public function addImage(PDO $db, Image $image): void {
        $imageURL = $image->getUrl();
        $stmt = $db->prepare("INSERT INTO ProductImage (product, image) VALUES (:product, :image)");
        $stmt->bindParam(":product", $this->id);
        $stmt->bindParam(":image", $imageURL);
        $stmt->execute();
    }

    public function removeImage(PDO $db, $url): void {
        $stmt = $db->prepare("DELETE FROM ProductImage WHERE product = :product AND image = :image");
        $stmt->bindParam(":product", $this->id);
        $stmt->bindParam(":image", $url);
        $stmt->execute();
    }

    public function getAllImages(PDO $db): array {
        $stmt = $db->prepare("SELECT * FROM ProductImage WHERE product = :product");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();
        $images = $stmt->fetchAll();
        return array_map(function ($image) use ($db) {
            return new Image($image["image"]);
        }, $images);
    }


    public static function getNumberOfProducts(PDO $db) {
        $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM Product");
        $stmt->execute();
        return $stmt->fetch()['cnt'];
    }
    
    public static function getNumberOfActiveProducts(PDO $db) {
        $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM Product WHERE payment IS NULL");
        $stmt->execute();
        return $stmt->fetch()['cnt'];
    }

    public static function getNumberOfClosedProducts(PDO $db) {
        $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM Product WHERE NOT (payment IS NULL)");
        $stmt->execute();
        return $stmt->fetch()['cnt'];
    }

    private static function rowToProduct(array $row, PDO $db): Product {
        return new Product(
            $row["id"],
            $row["title"],
            $row["price"],
            $row["description"],
            strtotime((string)$row["publishDatetime"]),
            User::getUserByID($db, $row["seller"]),
            $row["size"] ? Size::getSize($db, $row["size"]) : null,
            $row["category"] ? Category::getCategory($db, $row["category"]) : null,
            $row["condition"] ? Condition::getCondition($db, $row["condition"]) : null,
            isset($row["payment"]) ? Payment::getPaymentById($db, $row["payment"]) : null,
        );
    }

    public static function getProductByID(PDO $db, int $id, bool $onlyValid = true): ?Product {
        $stmt = $db->prepare("SELECT * FROM Product WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $product = $stmt->fetch();
        if (!isset($product["id"]))
            return null;
        if ($onlyValid && isset($product["payment"]))
            return null;
        return Product::rowToProduct($product, $db);
    }

    public static function getProductsByCategory(PDO $db, Category $category, bool $onlyValid = true): array {
        $categoryName = $category->getName();

        $stmt = $db->prepare("SELECT * FROM Product WHERE category = :category");
        $stmt->bindParam(":category", $categoryName);
        $stmt->execute();
        $products = $stmt->fetchAll();
        if ($onlyValid) {
            $products = array_filter($products, function ($product) {
                return $product["payment"] == null;
            });
        }
        return array_map(function ($product) use ($db) {
            return Product::rowToProduct($product, $db);
        }, $products);
    }

    public static function getProductsByBrand(PDO $db, Brand $brand, bool $onlyValid = true) : array {
        $brandName = $brand->getName();

        $stmt = $db->prepare("SELECT * FROM Product WHERE id IN (SELECT product FROM ProductBrand WHERE brand = :brand)");
        $stmt->bindParam(":brand", $brandName);
        $stmt->execute();
        $products = $stmt->fetchAll();
        if ($onlyValid) {
            $products = array_filter($products, function ($product) {
                return $product["payment"] == null;
            });
        }
        return array_map(function ($product) use ($db) {
            return Product::rowToProduct($product, $db);
        }, $products);
    }

    public static function getNProducts(PDO $db, int $n, bool $onlyValid = true): array {
        $stmt = $db->prepare("SELECT * FROM Product WHERE id <= :n");
        $stmt->bindParam(":n", $n);
        $stmt->execute();
        $products = $stmt->fetchAll();
        if ($onlyValid) {
            $products = array_filter($products, function ($product) {
                return $product["payment"] == null;
            });
        }
        return array_map(function ($product) use ($db) {
            return Product::rowToProduct($product, $db);
        }, $products);
    }

    public static function getAllProducts(PDO $db, bool $onlyValid = true): array {
        $stmt = $db->prepare("SELECT * FROM Product");
        $stmt->execute();
        $products = $stmt->fetchAll();
        if ($onlyValid) {
            $products = array_filter($products, function ($product) {
                return $product["payment"] == null;
            });
        }
        return array_map(function ($product) use ($db) {
            return Product::rowToProduct($product, $db);
        }, $products);
    }

    public function associateToPayment(PDO $db, Payment $payment): void {
        $paymentId = $payment->getId();
        $productId = $this->id;

        $stmt = $db->prepare("UPDATE Product SET payment = :paymentId WHERE id = :productId");
        $stmt->bindParam(":paymentId", $paymentId);
        $stmt->bindParam(":productId", $productId);
        $stmt->execute();
        $this->payment = Payment::getPaymentById($db, (int)$paymentId);
    }

    public function delete(PDO $db): void {
        $stmt = $db->prepare("DELETE FROM ProductBrand WHERE product = :product");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM Wishes WHERE product = :product");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM Image WHERE url IN (SELECT image FROM ProductImage WHERE product = :product)");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM ProductImage WHERE product = :product");
        $stmt->bindParam(":product", $this->id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM Product WHERE id = :id");
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }
}
