<?php

namespace Claude\Claude3Api\Models;

class Tool
{
    private string $name;
    private string $description;
    private array $inputSchema;

    public function __construct(string $name, string $description, array $inputSchema)
    {
        $this->name = $name;
        $this->description = $description;
        $this->inputSchema = $inputSchema;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'input_schema' => $this->inputSchema,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getInputSchema(): array
    {
        return $this->inputSchema;
    }
}
