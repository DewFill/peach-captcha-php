<?php

namespace Peach\Controllers;

use PDO;
use Peach\Repositories\AttemptRepository;
use Peach\Repositories\MaskRepository;

class CaptchaController
{
    /**
     * Погрешность точки идеального пути, при котором пересечение с user defined точкой учитывается
     */
    public function __construct(private DatabaseController $database, private AttemptsController $attemptController, private MaskController $maskController)
    {
    }

    function generateCaptcha()
    {
        $maskId = $this->maskController->getRandomMaskId();
        return $this->attemptController->createAttempt($maskId);
    }

    function submitCaptcha(string $uuid, string $user_points)
    {
        $stmt = $this->database->getPDO()->prepare("UPDATE attempts SET points = :value1 WHERE uuid = :value2");
        $stmt->bindParam(':value1', $user_points);
        $stmt->bindParam(':value2', $uuid);

        return $stmt->execute();
    }


    function setCaptchaSolved(string $uuid, bool $solved)
    {
        $stmt = $this->database->getPDO()->prepare("UPDATE attempts SET solved = :value1 WHERE uuid = :value2");
        $stmt->bindParam(':value1', $solved, PDO::PARAM_INT);
        $stmt->bindParam(':value2', $uuid);

        return $stmt->execute();
    }


    public function isCaptchaAvailableToSolve(string $captcha_uuid): bool {
        $stmt = $this->database->getPDO()->prepare("SELECT * FROM attempts WHERE uuid = :uuid LIMIT 1");
        // Привязка параметра
        $stmt->bindParam(':uuid', $captcha_uuid);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $createdAt = strtotime($result["created_at"]);
        $currentTime = time();
//        var_dump($result === false);
//        var_dump((($currentTime - $createdAt) > 40));
//        var_dump($result["solved"] !== null);
        if (empty($result)) {
            return false;
        }

        if ($createdAt === false) {
            return false;
        }

        if ((($currentTime - $createdAt) > 40) or $result["solved"] !== null) {
            return false;
        }

        return true;
    }
}