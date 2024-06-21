<?php

namespace Claude\Claude3Api;

class Config
{
    public function __construct(
        private string $apiKey,
        private string $apiVersion = '2023-06-01',
        private string $baseUrl = 'https://api.anthropic.com/v1'
    ) {
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
