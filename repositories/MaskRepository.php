<?php

namespace Peach\Repositories;

class MaskRepository
{
    public function __construct(private ?string $id,
                                private ?string $image_id,
                                private ?string $points,
                                private float $min_percentage_match_1,
                                private float $max_percentage_not_match_2,
                                private int $point_tolerance,
                                private ?int $view_count = null,
                                private ImageRepository|null $imageRepository = null)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getImageId(): string
    {
        return $this->image_id;
    }

    public function getPoints(): string|null
    {
        return $this->points;
    }

    public static function fromArray(array $array): self
    {
        return new self($array['id'], $array['image_id'], $array['points'], $array['min_percentage_match_1'], $array['max_percentage_not_match_2'], $array['point_tolerance'], $array['view_count']);
    }

    public function getMinPercentageMatch1(): float
    {
        return $this->min_percentage_match_1;
    }

    public function getMaxPercentageNotMatch2(): float
    {
        return $this->max_percentage_not_match_2;
    }

    public function getPointTolerance(): int
    {
        return $this->point_tolerance;
    }

    function withImageRepository(ImageRepository $imageRepository): self
    {
        $this->imageRepository = $imageRepository;
        return $this;
    }

    public function getImageRepository(): ImageRepository
    {
        return $this->imageRepository;
    }

    public function getViewCount(): int
    {
        return $this->view_count;
    }


}