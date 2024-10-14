<?php

namespace Peach\Handlers;

use Peach\Controllers\ImageController;

class ImageHandler
{
    public function __construct(private ImageController $imageController)
    {
    }

    function handleViewImage(): callable
    {
        return function () {
            if (!isset($_GET["image_id"]) or !is_numeric($_GET["image_id"])) {
                throw new \Exception("Invalid image id");
            }
            $imageRepository = $this->imageController->getImage($_GET["image_id"]);
            header("Content-Type: {$imageRepository->getMineType()}");
            echo $imageRepository->getImage();
        };
    }
}