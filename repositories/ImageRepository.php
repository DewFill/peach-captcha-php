<?php

namespace Peach\Repositories;

class ImageRepository
{
    public function __construct(private string $id, private string $image, private string $mine_type, private string $width, private string $height)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getMineType(): string
    {
        return $this->mine_type;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public static function fromArray(array $data): ImageRepository
    {
        return new self($data['id'], $data['image'], $data['mime_type'], $data['width'], $data['height']);
    }
}