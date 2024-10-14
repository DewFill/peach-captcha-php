<?php

namespace Peach\Controllers;

use Exception;
use JsonException;
use PDO;
use Peach\Repositories\AttemptRepository;

class AttemptsController
{
    public function __construct(private DatabaseController $database)
    {
    }

    function createAttempt(string $mask_id): AttemptRepository
    {
        $generateCaptchaUserUuid = $this->generateCaptchaUserUuid();

        $stmt = $this->database->getPDO()->prepare("INSERT INTO attempts (mask_id, uuid) VALUES (:value1, :value2)");
        $stmt->bindParam(':value1', $mask_id);
        $stmt->bindParam(':value2', $generateCaptchaUserUuid);

        $isExecuted = $stmt->execute();
        if ($isExecuted === false) throw new Exception("Failed to create attempt");
        $attempt_id = $this->database->getPDO()->lastInsertId();
        return new AttemptRepository($attempt_id, $mask_id, "[]", $generateCaptchaUserUuid, time(), null);
    }

    private function generateCaptchaUserUuid()
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)), // 32 bits for "time_low"
            bin2hex(random_bytes(2)), // 16 bits for "time_mid"
            bin2hex(random_bytes(2)), // 16 bits for "time_high_and_version"
            bin2hex(random_bytes(2)), // 16 bits for "clock_seq_and_reserved"
            bin2hex(random_bytes(6))  // 48 bits for "node"
        );
    }

    /**
     * @throws JsonException
     */
    function submitAttempt(string $paths): false|string
    {
        if (json_validate($paths) === false) throw new JsonException("Validation failed");
        $stmt = $this->database->getPDO()->prepare("INSERT INTO attempts (points) VALUES (:value1)");
        $stmt->bindParam(':value1', $paths);

        $isExecuted = $stmt->execute();
        if ($isExecuted === false) return false;

        return $this->database->getPDO()->lastInsertId();
    }

    function getAttempt(string $id): AttemptRepository|false
    {
        $stmt = $this->database->getPDO()->prepare("SELECT * FROM attempts WHERE id = :id");
        // Привязка параметра
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Получение результата
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) return AttemptRepository::fromArray($result);
        else return false;
    }

    function getAttemptByUuid(string $uuid): AttemptRepository|false
    {
        $stmt = $this->database->getPDO()->prepare("SELECT * FROM attempts WHERE uuid = :uuid");
        // Привязка параметра
        $stmt->bindParam(':uuid', $uuid);

        $stmt->execute();

        // Получение результата
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) return AttemptRepository::fromArray($result);

        else return false;
    }
}