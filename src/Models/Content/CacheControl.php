<?php

namespace Claude\Claude3Api\Models\Content;

class CacheControl
{
    private string $type;

    /**
     * Creates a new CacheControl instance
     * 
     * @param string $type The type of cache control (currently only "ephemeral" is supported)
     */
    public function __construct(string $type = 'ephemeral')
    {
        $this->type = $type;
    }

    /**
     * Convert to array for API request
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type
        ];
    }

    /**
     * Get the cache control type
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Create an ephemeral cache control (5-minute minimum lifetime)
     * 
     * @return self
     */
    public static function ephemeral(): self
    {
        return new self('ephemeral');
    }
}
