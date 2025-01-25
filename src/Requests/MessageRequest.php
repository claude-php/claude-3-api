<?php

namespace Claude\Claude3Api\Requests;

use Claude\Claude3Api\Config;
use Claude\Claude3Api\Models\Message;
use Claude\Claude3Api\Models\Tool;
use Claude\Claude3Api\Exceptions\InvalidArgumentException;

class MessageRequest
{
    private string $model = Config::DEFAULT_MODEL;
    private int $maxTokens = Config::DEFAULT_MAX_TOKENS;
    private array $messages = [];
    private array $tools = [];
    private ?array $toolChoice = null;
    private ?string $system = null;
    private ?float $temperature = null;
    private ?array $stopSequences = null;
    private ?bool $stream = null;
    private ?array $metadata = null;
    private ?int $topK = null;
    private ?float $topP = null;

    public function __construct(Config $config = null)
    {
        if ($config) {
            $this->model = $config->getModel() ?? Config::DEFAULT_MODEL;
            $this->maxTokens = $config->getMaxTokens() ?? Config::DEFAULT_MAX_TOKENS;
        }
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

    public function addMessage(Message $message): self
    {
        $this->messages[] = $message->toArray();
        return $this;
    }

    public function addTool(Tool|array $tool): self
    {
        $this->tools[] = $tool->toArray();
        return $this;
    }

    public function setToolChoice(?array $toolChoice): self
    {
        $this->toolChoice = $toolChoice;
        return $this;
    }

    public function setSystem(?string $system): self
    {
        $this->system = $system;
        return $this;
    }

    public function setTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setStopSequences(?array $stopSequences): self
    {
        $this->stopSequences = $stopSequences;
        return $this;
    }

    public function setStream(?bool $stream): self
    {
        $this->stream = $stream;
        return $this;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function setTopK(?int $topK): self
    {
        $this->topK = $topK;
        return $this;
    }

    public function setTopP(?float $topP): self
    {
        $this->topP = $topP;
        return $this;
    }

    public function toArray(): array
    {
        if (empty($this->messages)) {
            throw new InvalidArgumentException('At least one message is required');
        }

        $data = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->messages,
        ];

        if (!empty($this->tools)) {
            $data['tools'] = $this->tools;
        }

        if ($this->toolChoice !== null) {
            $data['tool_choice'] = $this->toolChoice;
        }

        if ($this->system !== null) {
            $data['system'] = $this->system;
        }

        if ($this->temperature !== null) {
            $data['temperature'] = $this->temperature;
        }

        if ($this->stopSequences !== null) {
            $data['stop_sequences'] = $this->stopSequences;
        }

        if ($this->stream !== null) {
            $data['stream'] = $this->stream;
        }

        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        if ($this->topK !== null) {
            $data['top_k'] = $this->topK;
        }

        if ($this->topP !== null) {
            $data['top_p'] = $this->topP;
        }

        return $data;
    }
}
