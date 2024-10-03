<?php

namespace Claude\Claude3Api\Models;

use Claude\Claude3Api\Models\Content\ContentInterface;

class ToolResult implements ContentInterface
{
    private string $toolUseId;
    private $content;
    public function __construct(string $toolUseId, $content)
    {
        $this->toolUseId = $toolUseId;
        $this->content = $content;
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'tool_result',
            'tool_use_id' => $this->toolUseId,
            'content' => $this->content
        ];

        return $result;
    }
}
