<?php

namespace Claude\Claude3Api\Models\Content;

class TextContent implements ContentInterface
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function toArray(): array
    {
        return [
            'type' => 'text',
            'text' => $this->text,
        ];
    }
}
