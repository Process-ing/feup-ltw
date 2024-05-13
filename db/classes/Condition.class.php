<?php

declare(strict_types=1);

class Condition
{
    public string $condition;
    public function __construct(string $condition)
    {
        $this->condition = $condition;
    }

    public function upload(PDO $db)
    {
        $stmt = $db->prepare("INSERT INTO Condition (name) VALUES (:name)");
        $stmt->bindParam(":name", $this->condition);
        $stmt->execute();
    }

    public static function getCondition(PDO $db, string $condition): Condition
    {
        // At first glance, going to the database if we already now the condition
        // might seem a bit stupid, but in this way we can check
        // if the condition is in the database or not.
        $stmt = $db->prepare("SELECT name FROM Condition WHERE name = :condition");
        $stmt->bindParam(":condition", $condition);
        $stmt->execute();
        $conditionName = $stmt->fetch();
        if ($conditionName === false) {
            throw new Exception("Condition not found");
        }
        return new Condition($conditionName["name"]);
    }

    public static function getAll(PDO $db): array
    {
        $stmt = $db->prepare("SELECT name FROM Condition");
        $stmt->execute();
        $conditions = array_map(function ($condition) {
            return new Condition($condition["name"]);
        }, $stmt->fetchAll());
        return $conditions;
    }
}
