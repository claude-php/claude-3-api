<?php

namespace Claude\Claude3Api\Models\Content;

class TextContent implements ContentInterface
{
    private string $text;
    private ?CacheControl $cacheControl;

    /**
     * Creates a new TextContent instance
     * 
     * @param string $text The text content
     * @param CacheControl|null $cacheControl Optional cache control settings
     */
    public function __construct(string $text, ?CacheControl $cacheControl = null)
    {
        $this->text = $text;
        $this->cacheControl = $cacheControl;
    }

    /**
     * Convert to array for API request
     * 
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type' => 'text',
            'text' => $this->text,
        ];

        if ($this->cacheControl !== null) {
            $result['cache_control'] = $this->cacheControl->toArray();
        }

        return $result;
    }

    /**
     * Get the text content
     * 
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get cache control settings if set
     * 
     * @return CacheControl|null
     */
    public function getCacheControl(): ?CacheControl
    {
        return $this->cacheControl;
    }

    /**
     * Set cache control settings
     * 
     * @param CacheControl|null $cacheControl
     * @return self
     */
    public function setCacheControl(?CacheControl $cacheControl): self
    {
        $this->cacheControl = $cacheControl;
        return $this;
    }

    /**
     * Create text content with ephemeral caching
     * 
     * @param string $text The text content
     * @return self
     */
    public static function withEphemeralCache(string $text): self
    {
        return new self($text, CacheControl::ephemeral());
    }
}
