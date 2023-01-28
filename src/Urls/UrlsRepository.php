<?php

namespace Urls;

use Carbon\Carbon;
use PDO;

class UrlsRepository
{
    /**
     * @var \PDO
     */
    private PDO $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function getById(int $id)
    {
        $sql = 'SELECT * FROM urls WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $urlName
     *
     * @return mixed
     */
    public function getByName(string $urlName)
    {
        $sql = 'SELECT * FROM urls WHERE name = :url';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':url', $urlName);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $urlName
     *
     * @return false|string
     */
    public function add(string $urlName)
    {
        $time = Carbon::now();
        $sql = 'INSERT INTO urls(name, created_at) VALUES(:url, :time)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':url', $urlName);
        $stmt->bindValue(':time', $time);

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $sql = 'SELECT * FROM urls ORDER BY id DESC';
        $result = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $result
            ?: [];
    }
}
