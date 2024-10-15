<?php

namespace Peach\Handlers;

use Exception;
use Peach\Controllers\AttemptsController;
use Peach\Repositories\AttemptRepository;
use Peach\Repositories\RequestRepository;
use Peach\Visualizers\PreviewVisualizer;

readonly class AttemptsHandler
{
    public function __construct(private AttemptsController $attemptsController, private RequestRepository $requestRepository)
    {
    }

    function handleSubmitAttempt(): callable
    {
        $attemptsController = $this->attemptsController;
        return function () use ($attemptsController) {
            $result = $attemptsController->submitAttempt($this->requestRepository->getBody());
            if ($result === false) {
                echo json_encode([
                    "status" => "failed",
                    "message" => "Attempt failed"
                ]);
            } else {
                echo json_encode([
                    "status" => "success",
                    "message" => "Attempt written successfully",
                    "preview_page_id" => $result
                ]);
            }
        };
    }

    function handleViewPreview(): callable
    {
        return function () {
            if (!isset($_GET["attempt_id"]) or !is_numeric($_GET["attempt_id"])) throw new Exception("Invalid attempt id number");

            $attempt_repository = $this->attemptsController->getAttempt($_GET["attempt_id"]);

            if ($attempt_repository === false) throw new Exception("Attempt not found");

            return (new PreviewVisualizer($attempt_repository))->visualize();
        };
    }

    function handleApiGetAttempt(): callable
    {
        return function () {
            if (!isset($_GET["attempt_id"]) or !is_numeric($_GET["attempt_id"])) throw new Exception("Invalid attempt id number");
            $attempt_repository = $this->attemptsController->getAttempt($_GET["attempt_id"]);

            if ($attempt_repository === false) throw new Exception("Attempt not found");

            header("Content-Type: application/json");
            echo json_encode([
                "status" => "success",
                "points" => $attempt_repository->getPoints(),
            ]);
        };
    }
}