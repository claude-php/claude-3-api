<?php

namespace Claude\Claude3Api\Responses;

class MessageResponse
{
    private ?string $id;
    private ?string $object;
    private ?string $created;
    private ?string $type;
    private ?string $role;
    private array $content;
    private array $choices;
    private ?string $model;
    private ?string $stopReason;
    private ?string $stopSequence;
    private array $usage;
    private ?string $systemFingerprint;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->object = $data['object'] ?? null;
        $this->created = $data['created'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->content = $data['content'] ?? [];
        $this->choices = $data['choices'] ?? [];
        $this->model = $data['model'] ?? null;
        $this->systemFingerprint = $data['system_fingerprint'] ?? null;
        $this->stopReason = $data['stop_reason'] ?? null;
        $this->stopSequence = $data['stop_sequence'] ?? null;
        $this->usage = $data['usage'] ?? [];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function getCreated(): ?string
    {
        return $this->created;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getContent(): array
    {
        return $this->content ?? $this->choices ?? [];
    }

    public function getChoices(): array
    {
        return $this->choices ?? $this->content ?? [];
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getStopReason(): ?string
    {
        return $this->stopReason;
    }

    public function getStopSequence(): ?string
    {
        return $this->stopSequence;
    }

    public function getUsage(): array
    {
        return $this->usage;
    }

    public function getSystemFingerprint(): ?string
    {
        return $this->systemFingerprint;
    }
}
