<?php

namespace Peach\Repositories;

class AttemptRepository
{
    public function __construct(private ?string $id, private ?string $mask_id, private ?string $points, private ?string $uuid, private string $created_at, private null|bool $solved)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMaskId(): string
    {
        return $this->mask_id;
    }

    public function getPoints(): string
    {
        return $this->points;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function isSolved(): bool
    {
        return $this->solved;
    }

    public function setSolved(bool $solved): void
    {
        $this->solved = $solved;
    }

    public static function fromArray(array $array): self
    {
        return new self($array['id'], $array['mask_id'], $array['points'], $array['uuid'], $array['created_at'], $array['solved']);
    }
}