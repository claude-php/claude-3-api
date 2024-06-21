<?php

namespace Claude\Claude3Api\Models\Content;

class ImageContent implements ContentInterface
{
    private string $base64Data;
    private string $mediaType;

    public function __construct(string $base64Data, string $mediaType)
    {
        $this->base64Data = $base64Data;
        $this->mediaType = $mediaType;
    }

    public function toArray(): array
    {
        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $this->mediaType,
                'data' => $this->base64Data,
            ],
        ];
    }
}
