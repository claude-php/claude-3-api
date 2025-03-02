<?php

namespace Claude\Claude3Api;

class Config
{
    const AUTH_TYPE_X_API_KEY = 'x-api-key';
    const AUTH_TYPE_BEARER = 'bearer';

    const CLAUDE_API_VERSION = '2023-06-01';
    const CLAUDE_BASE_URL = 'https://api.anthropic.com/v1';
    const CLAUDE_AUTH_TYPE = self::AUTH_TYPE_X_API_KEY;
    const CLAUDE_MODEL = 'claude-3-7-sonnet-latest';
    const CLAUDE_MAX_TOKENS = 8192; // Default max tokens for Claude 3.7
    const CLAUDE_MESSAGE_PATH = '/messages';

    // Beta features
    const CLAUDE_BETA_FEATURES = [
        'output-128k-2025-02-19' => false  // 128k token output for Claude 3.7 Sonnet
    ];

    const OPENAI_API_VERSION = '2023-06-01';
    const OPENAI_BASE_URL = 'https://api.openai.com/v1';
    const OPENAI_AUTH_TYPE = self::AUTH_TYPE_BEARER;
    const OPENAI_MODEL = 'gpt-4o';
    const OPENAI_MAX_TOKENS = 16384;
    const OPENAI_MESSAGE_PATH = '/chat/completions';

    const DEEPSEEK_API_VERSION = '2023-06-01';
    const DEEPSEEK_BASE_URL = 'https://api.deepseek.com';
    const DEEPSEEK_AUTH_TYPE = self::AUTH_TYPE_BEARER;
    const DEEPSEEK_MODEL = 'deepseek-chat';
    const DEEPSEEK_MAX_TOKENS = 8192;
    const DEEPSEEK_MESSAGE_PATH = '/chat/completions';

    const DEFAULT_API_VERSION = self::CLAUDE_API_VERSION;
    const DEFAULT_BASE_URL = self::CLAUDE_BASE_URL;
    const DEFAULT_AUTH_TYPE = self::CLAUDE_AUTH_TYPE;
    const DEFAULT_MODEL = self::CLAUDE_MODEL;
    const DEFAULT_MAX_TOKENS = self::CLAUDE_MAX_TOKENS;
    const DEFAULT_MESSAGE_PATH = self::CLAUDE_MESSAGE_PATH;
    const DEFAULT_BETA_FEATURES = self::CLAUDE_BETA_FEATURES;

    public function __construct(
        private string $apiKey,
        private string $apiVersion = self::DEFAULT_API_VERSION,
        private string $baseUrl = self::DEFAULT_BASE_URL,
        private string $model = self::DEFAULT_MODEL,
        private string $maxTokens = self::DEFAULT_MAX_TOKENS,
        private string $authType = self::DEFAULT_AUTH_TYPE,
        private string $messagePath = self::DEFAULT_MESSAGE_PATH,
        private array $betaFeatures = self::DEFAULT_BETA_FEATURES
    ) {}

    public function useClaude(): self
    {
        $this->apiVersion = self::CLAUDE_API_VERSION;
        $this->baseUrl = self::CLAUDE_BASE_URL;
        $this->model = self::CLAUDE_MODEL;
        $this->maxTokens = self::CLAUDE_MAX_TOKENS;
        $this->authType = self::CLAUDE_AUTH_TYPE;
        $this->messagePath = self::CLAUDE_MESSAGE_PATH;
        $this->betaFeatures = self::DEFAULT_BETA_FEATURES;
        return $this;
    }

    public function useOpenAI(): self
    {
        $this->apiVersion = self::OPENAI_API_VERSION; // api version is not used by openai
        $this->baseUrl = self::OPENAI_BASE_URL;
        $this->model = self::OPENAI_MODEL;
        $this->maxTokens = self::OPENAI_MAX_TOKENS;
        $this->authType = self::OPENAI_AUTH_TYPE;
        $this->messagePath = self::OPENAI_MESSAGE_PATH;
        $this->betaFeatures = [];
        return $this;
    }

    public function useDeepSeek(): self
    {
        $this->apiVersion = self::DEEPSEEK_API_VERSION;
        $this->baseUrl = self::DEEPSEEK_BASE_URL;
        $this->model = self::DEEPSEEK_MODEL;
        $this->maxTokens = self::DEEPSEEK_MAX_TOKENS;
        $this->authType = self::DEEPSEEK_AUTH_TYPE;
        $this->messagePath = self::DEEPSEEK_MESSAGE_PATH;
        $this->betaFeatures = [];
        return $this;
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

    public function getModel(): string
    {
        return $this->model;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function getAuthType(): string
    {
        return $this->authType;
    }

    public function getMessagePath(): string
    {
        return $this->messagePath;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function setAuthType(string $authType): self
    {
        $this->authType = $authType;
        return $this;
    }

    public function setMessagePath(string $messagePath): self
    {
        $this->messagePath = $messagePath;
        return $this;
    }

    public function getBetaFeatures(): array
    {
        return $this->betaFeatures;
    }

    public function enableBetaFeature(string $featureName): self
    {
        if (array_key_exists($featureName, $this->betaFeatures)) {
            $this->betaFeatures[$featureName] = true;
        }
        return $this;
    }

    public function disableBetaFeature(string $featureName): self
    {
        if (array_key_exists($featureName, $this->betaFeatures)) {
            $this->betaFeatures[$featureName] = false;
        }
        return $this;
    }

    public function setBetaFeatures(array $betaFeatures): self
    {
        $this->betaFeatures = array_merge($this->betaFeatures, $betaFeatures);
        return $this;
    }

    public function isBetaFeatureEnabled(string $featureName): bool
    {
        return isset($this->betaFeatures[$featureName]) && $this->betaFeatures[$featureName];
    }

    public function enable128kOutput(): self
    {
        return $this->enableBetaFeature('output-128k-2025-02-19');
    }

    public function enable128kOutputWithTokens(int $maxTokens = 131072): self
    {
        $this->enableBetaFeature('output-128k-2025-02-19');
        $this->setMaxTokens($maxTokens);
        return $this;
    }
}
