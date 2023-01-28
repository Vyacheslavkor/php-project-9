<?php

namespace Urls;

use Carbon\Carbon;
use PDO;

class UrlChecksRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save($urlId, $params = []): bool|string
    {
        $time = Carbon::now();

        $default_params = [
            'status_code' => '',
            'h1'          => '',
            'title'       => '',
            'description' => '',
        ];

        $params = array_merge($default_params, $params);

        $sql = 'INSERT INTO url_checks(url_id, created_at, status_code, h1, title, description)'
            . ' VALUES(:urlId, :time, :statusCode, :h1, :title, :description)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':urlId', $urlId);
        $stmt->bindValue(':time', $time);
        $stmt->bindValue(':statusCode', $params['status_code']);
        $stmt->bindValue(':h1', $params['h1']);
        $stmt->bindValue(':title', $params['title']);
        $stmt->bindValue(':description', $params['description']);

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    public function getAllByUrlId(int $urlId): bool|array
    {
        $sql = 'SELECT * FROM url_checks WHERE url_id = :urlId ORDER BY id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':urlId', $urlId);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastCheck(int $urlId)
    {
        $sql = 'SELECT created_at AS last_check_at, status_code AS last_check_status_code FROM url_checks'
            . ' WHERE url_id = :urlId ORDER BY id DESC LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':urlId', $urlId);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? [] : $result;
    }
}
