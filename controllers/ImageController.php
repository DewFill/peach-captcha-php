<?php

namespace Peach\Controllers;

use PDO;
use Peach\Repositories\ImageRepository;
use Peach\Repositories\MaskRepository;

class ImageController
{
    public function __construct(private DatabaseController $database)
    {
    }

    public function getImage(string $id)
    {
        $stmt = $this->database->getPDO()->prepare("SELECT * FROM images WHERE id = :id");
        // Привязка параметра
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        // Получение результата
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) return ImageRepository::fromArray($result);
        else return false;
    }


    public function uploadImage(string $filename)
    {
        $mime_type = mime_content_type($filename);

        if ($mime_type !== 'image/jpeg' and $mime_type !== 'image/png') {
            throw new \Exception("File type is not allowed");
        }
        $imageInfo = getimagesize($filename);
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        $imageData = file_get_contents($filename);

        $stmt = $this->database->getPDO()->prepare("INSERT INTO images (image, mime_type, width, height) VALUES (:value1, :value2, :value3, :value4)");
        $stmt->bindParam(':value1', $imageData);
        $stmt->bindParam(':value2', $mime_type);
        $stmt->bindParam(':value3', $width);
        $stmt->bindParam(':value4', $height);

        $isExecuted = $stmt->execute();
        if ($isExecuted === false) return false;

        return $this->database->getPDO()->lastInsertId();
    }
}