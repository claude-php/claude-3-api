<?php

namespace Claude\Claude3Api\Models\Content;

class ToolUseContent implements ContentInterface
{
    private string $id;
    private string $name;
    private array $input;

    public function __construct(string $id, string $name, array $input)
    {
        $this->id = $id;
        $this->name = $name;
        $this->input = $input;
    }

    public function toArray(): array
    {
        return [
            'type' => 'tool_use',
            'id' => $this->id,
            'name' => $this->name,
            'input' => (object)$this->input,
        ];
    }
}
