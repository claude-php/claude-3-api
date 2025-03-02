<?php

namespace Claude\Claude3Api\Tests;

use PHPUnit\Framework\TestCase;
use Claude\Claude3Api\Models\Content\CacheControl;
use Claude\Claude3Api\Models\Content\TextContent;
use Claude\Claude3Api\Models\Message;
use Claude\Claude3Api\Requests\MessageRequest;

class CacheControlTest extends TestCase
{
    public function testCacheControlCreation()
    {
        $cacheControl = new CacheControl();
        $this->assertEquals('ephemeral', $cacheControl->getType());

        $customCacheControl = new CacheControl('ephemeral');
        $this->assertEquals('ephemeral', $customCacheControl->getType());

        $staticCacheControl = CacheControl::ephemeral();
        $this->assertEquals('ephemeral', $staticCacheControl->getType());
    }

    public function testCacheControlToArray()
    {
        $cacheControl = new CacheControl();
        $array = $cacheControl->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('type', $array);
        $this->assertEquals('ephemeral', $array['type']);
    }

    public function testTextContentWithCacheControl()
    {
        $text = "Test content";
        $cacheControl = new CacheControl();

        $textContent = new TextContent($text, $cacheControl);
        $array = $textContent->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('text', $array);
        $this->assertArrayHasKey('cache_control', $array);
        $this->assertEquals('text', $array['type']);
        $this->assertEquals($text, $array['text']);
        $this->assertEquals(['type' => 'ephemeral'], $array['cache_control']);

        // Test convenience method
        $convenientTextContent = TextContent::withEphemeralCache($text);
        $convenientArray = $convenientTextContent->toArray();

        $this->assertArrayHasKey('cache_control', $convenientArray);
        $this->assertEquals(['type' => 'ephemeral'], $convenientArray['cache_control']);
    }

    public function testSystemMessageWithCacheControl()
    {
        $regularText = "Regular system instruction";
        $cacheableText = "Large cacheable text";

        $systemMessage = new Message('system', [
            new TextContent($regularText),
            TextContent::withEphemeralCache($cacheableText)
        ]);

        $messageRequest = new MessageRequest();
        $messageRequest->addSystemMessage($systemMessage);

        // Add a user message to satisfy the requirement for at least one message
        $userMessage = new Message('user', [new TextContent('Test question')]);
        $messageRequest->addMessage($userMessage);

        $array = $messageRequest->toArray();

        $this->assertArrayHasKey('system', $array);
        $this->assertIsArray($array['system']);
        $this->assertCount(2, $array['system']);

        // First content item (regular text)
        $this->assertEquals('text', $array['system'][0]['type']);
        $this->assertEquals($regularText, $array['system'][0]['text']);
        $this->assertArrayNotHasKey('cache_control', $array['system'][0]);

        // Second content item (cacheable text)
        $this->assertEquals('text', $array['system'][1]['type']);
        $this->assertEquals($cacheableText, $array['system'][1]['text']);
        $this->assertArrayHasKey('cache_control', $array['system'][1]);
        $this->assertEquals(['type' => 'ephemeral'], $array['system'][1]['cache_control']);
    }
}
