<?php

namespace Peach\Handlers;

use Exception;
use Peach\Controllers\ImageController;
use Peach\Controllers\MaskController;
use Peach\Repositories\MaskRepository;
use Peach\Repositories\RequestRepository;
use Peach\Visualizers\MaskVisualizer;

class MaskHandler
{
    private const DEFAULT_PERCENTAGE_MATCH_1 = .8;
    private const DEFAULT_PERCENTAGE_NOT_MATCH_2 = .2;
    private const POINT_TOLERANCE = 10;
    public function __construct(private MaskVisualizer $visualizer, private MaskController $maskController, private RequestRepository $requestRepository, private ImageController $imageController)
    {
    }

    function handleViewMask(): callable
    {
        return function () {
            return $this->visualizer->visualize();
        };
    }

    function handleApiCreateMask():callable
    {
        return function () {
            if (!isset($_FILES["image"]) or !isset($_FILES["image"]["tmp_name"]) or !($_FILES["image"]["error"] === 0)) {
                throw new Exception("File upload error");
            }
            $image_id = $this->imageController->uploadImage($_FILES["image"]["tmp_name"]);
            if ($image_id === false) {
                throw new Exception("File upload error");
            }

            $maskRepository = $this->maskController->createMask($image_id, "[]", self::DEFAULT_PERCENTAGE_MATCH_1, self::DEFAULT_PERCENTAGE_NOT_MATCH_2, self::POINT_TOLERANCE);

            if ($maskRepository === false) {
                throw new Exception("Cannot create mask");
            }

            echo json_encode([
                "status" => "success",
                "mask_id" => $maskRepository->getId()
            ]);
        };
    }

    function handleApiGetMask(): callable
    {
        return function () {
            if (!isset($_GET["mask_id"]) or !is_numeric($_GET["mask_id"])) throw new Exception("Invalid mask id number");
            $maskRepository = $this->maskController->getMask($_GET["mask_id"]);

            if ($maskRepository === false) throw new Exception("Mask not found");

            header("Content-Type: application/json");
            echo json_encode([
                "status" => "success",
                "image_id" => $maskRepository->getImageId(),
                "points" => $maskRepository->getPoints(),
                "min_percentage_match_1" => $maskRepository->getMinPercentageMatch1(),
                "max_percentage_not_match_2" => $maskRepository->getMaxPercentageNotMatch2(),
                "point_tolerance" => $maskRepository->getPointTolerance()
            ]);
        };
    }

    function handleApiEditMask(): callable
    {
        return function () {
            if (!json_validate($this->requestRepository->getBody())) throw new Exception("Invalid request body");
            $data = json_decode($this->requestRepository->getBody(), true);
            if (!isset($data["mask_id"]) or !is_numeric($data["mask_id"])) throw new Exception("Invalid mask id number");
            if (!isset($data["points"]) or !is_string($data["points"])) throw new Exception("Invalid points");
//var_dump($data);
//die();
            $maskRepository = new MaskRepository($data["mask_id"], null, $data["points"], $data["min_percentage_match_1"], $data["max_percentage_not_match_2"], $data["point_tolerance"]);
            $result = $this->maskController->editMask($maskRepository);

            if ($result === false) throw new Exception("Failed to edit mask");

            header("Content-Type: application/json");
            echo json_encode([
                "status" => "success",
            ]);
        };
    }
}