<?php

namespace Urls;

use Carbon\Carbon;
use PDO;

class UrlsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getById(int $id)
    {
        $sql = 'SELECT * FROM urls WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByName($urlName)
    {
        $sql = 'SELECT * FROM urls WHERE name = :url';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':url', $urlName);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($urlName)
    {
        $time = Carbon::now();
        $sql = 'INSERT INTO urls(name, created_at) VALUES(:url, :time)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':url', $urlName);
        $stmt->bindValue(':time', $time);

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function getAll(): bool|array
    {
        $sql = 'SELECT * FROM urls ORDER BY id DESC';

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
