<?php

namespace Dalehurley\Claude3Api\Models;

use Dalehurley\Claude3Api\Models\Content\ContentInterface;

class ToolResult implements ContentInterface
{
    private string $toolUseId;
    private $content;
    private bool $isError;

    public function __construct(string $toolUseId, $content, bool $isError = false)
    {
        $this->toolUseId = $toolUseId;
        $this->content = $content;
        $this->isError = $isError;
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'tool_result',
            'tool_use_id' => $this->toolUseId,
            'content' => $this->content
        ];

        if ($this->isError) {
            $result['is_error'] = true;
        }

        return $result;
    }
}
