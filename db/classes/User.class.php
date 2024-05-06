<?php

declare(strict_types=1);

require_once 'Image.class.php';
class User
{
    public string $username;
    public string $email;
    public string $name;
    public string $password;
    public int $registerDateTime;
    public Image $profilePicture;
    public string $type;

    public function __construct(string $username, string $email, string $name, string $password, int $registerDateTime, Image $profilePicture, string $type)
    {
        $this->username = $username;
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->registerDateTime = $registerDateTime;
        $this->profilePicture = $profilePicture;
        $this->type = $type;
    }

    public function validatePassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT, ['cost' >= 12]);
    }
    public function upload(PDO $db): void
    {
        $stmt = $db->prepare("INSERT INTO User (username, email, name, password, registerDatetime, profilePicture, type) VALUES (:username, :email, :name, :password, :registerDateTime, :profilePicture, :type)");
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":registerDateTime", $this->registerDateTime);
        $stmt->bindParam(":profilePicture", $this->profilePicture->url);
        $stmt->bindParam(":type", $this->type);
        $stmt->execute();
    }

    public static function getUserByName(PDO $db, string $username): User
    {
        $stmt = $db->prepare("SELECT * FROM User WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user === false) {
            throw new Exception("User not found");
        }
        return new User($user["username"], $user["email"], $user["name"], $user["password"], $user["registerDatetime"], new Image($user["profilePicture"]), $user["type"]);
    }

    public static function getUserByEmail(PDO $db, string $email): User
    {
        $stmt = $db->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user === false) {
            throw new Exception("User not found");
        }
        return new User($user["username"], $user["email"], $user["name"], $user["password"], $user["registerDatetime"], new Image($user["profilePicture"]), $user["type"]);
    }

    public function getProfilePicture(PDO $db): Image
    {
        $stmt = $db->prepare("SELECT profilePicture FROM User WHERE username = :username");
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
        $profilePicture = $stmt->fetch();
        if ($profilePicture === false) {
            throw new Exception("No image found");
        }
        return new Image($profilePicture["profilePicture"]);
    }

    public function setType(PDO $db, string $type): void
    {
        // if name not int ["seller", "buyer", "admin"] stop
        if (!in_array($type, ["seller", "buyer", "admin"])) {
            throw new Exception("Invalid type");
        }
        $this->type = $type;
        $stmt = $db->prepare("UPDATE User SET type = :type WHERE username = :username");
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
    }

    public function getUserPosts(PDO $db): array
    {
        // TODO if the posts don't work, its because of this.
        $stmt = $db->prepare("SELECT * FROM Post WHERE seller = :seller");
        $stmt->bindParam(":seller", $this->username);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        return array_map(function ($post) use ($db) {
            return new Post($post["id"], $post["title"], $post["price"], $post["description"], strtotime($post["publishDatetime"]), $this, Item::getItem($db, $post["item"]));
        }, $posts);
    }
}