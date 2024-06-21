<?php

namespace Claude\Claude3Api\Models;

use Claude\Claude3Api\Models\Content\ContentInterface;

class Message
{
    private string $role;
    private array $content;

    public function __construct(string $role, array $content = [])
    {
        $this->role = $role;
        $this->content = $content;
    }

    public function addContent(ContentInterface $content): self
    {
        $this->content[] = $content;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => array_map(function (ContentInterface $content) {
                return $content->toArray();
            }, $this->content),
        ];
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getContent(): array
    {
        return $this->content;
    }
}
