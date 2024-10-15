<?php

namespace Peach\Controllers;

use Exception;
use JsonException;
use PDO;
use Peach\Repositories\MaskRepository;

class MaskController
{
    public function __construct(private DatabaseController $database)
    {
    }

    /**
     * @throws JsonException
     */
    function editMask(MaskRepository $maskRepository): bool
    {
        $id = $maskRepository->getId();
        $points = $maskRepository->getPoints();
        $minPercentageMatch1 = $maskRepository->getMinPercentageMatch1();
        $maxPercentageNotMatch2 = $maskRepository->getMaxPercentageNotMatch2();
        $point_tolerance = $maskRepository->getPointTolerance();

        if (json_validate($points) === false) throw new JsonException("Validation failed");
        $stmt = $this->database->getPDO()->prepare("UPDATE masks SET points = :value1, min_percentage_match_1 = :value2, max_percentage_not_match_2 = :value3, point_tolerance = :value4  WHERE id = :value5");
        $stmt->bindParam(':value1', $points);
        $stmt->bindParam(':value2', $minPercentageMatch1);
        $stmt->bindParam(':value3', $maxPercentageNotMatch2);
        $stmt->bindParam(':value4', $point_tolerance);
        $stmt->bindParam(':value5', $id);

        return $stmt->execute();
    }

    function createMask($image_id, $points, $min_percentage_match_1, $max_percentage_not_match_2, $point_tolerance): bool|MaskRepository
    {

        if (!is_null($points) and json_validate($points) === false) throw new JsonException("Validation points failed");
        $stmt = $this->database->getPDO()->prepare("INSERT INTO masks (image_id, points, min_percentage_match_1, max_percentage_not_match_2, point_tolerance) VALUES (:value1, :value2, :value3, :value4, :value5)");
        $stmt->bindParam(':value1', $image_id);
        $stmt->bindParam(':value2', $points);
        $stmt->bindParam(':value3', $min_percentage_match_1);
        $stmt->bindParam(':value4', $max_percentage_not_match_2);
        $stmt->bindParam(':value5', $point_tolerance);

        $isExecuted = $stmt->execute();
        if ($isExecuted === false) return false;

        return new MaskRepository($this->database->getPDO()->lastInsertId(), $image_id, $points, $min_percentage_match_1, $max_percentage_not_match_2, $point_tolerance);
    }

    function getMask(string $id)
    {
        $stmt = $this->database->getPDO()->prepare("SELECT * FROM masks WHERE id = :id");
        // Привязка параметра
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Получение результата
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) return MaskRepository::fromArray($result);
        else return false;
    }

    function getRandomMaskId(): string
    {
        $stmt = $this->database->getPDO()->prepare("SELECT id FROM masks ORDER BY RAND() LIMIT 1;");

        $isExecuted = $stmt->execute();
        if ($isExecuted === false) return throw new Exception("Database error");

        // Получение результата
        return $stmt->fetch(PDO::FETCH_ASSOC)["id"];
    }
}