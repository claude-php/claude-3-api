<?php

namespace Claude\Claude3Api\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Claude\Claude3Api\Client;
use Claude\Claude3Api\Config;
use Claude\Claude3Api\Models\Content\ImageContent;
use Claude\Claude3Api\Models\Message;
use Claude\Claude3Api\Models\Content\TextContent;
use Claude\Claude3Api\Models\Tool;
use Claude\Claude3Api\Requests\MessageRequest;
use Claude\Claude3Api\Responses\MessageResponse;

class ClientTest extends TestCase
{
    private $mockHandler;
    private $handlerStack;
    private $httpClient;
    private $config;
    private $client;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->handlerStack = HandlerStack::create($this->mockHandler);
        $this->httpClient = new HttpClient(['handler' => $this->handlerStack]);
        $this->config = new Config('test-api-key');
        $this->client = new Client($this->config);

        // Use reflection to set the mock HTTP client
        $reflection = new \ReflectionClass($this->client);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->client, $this->httpClient);
    }

    public function testSendMessage()
    {
        $responseBody = json_encode([
            'id' => 'msg_123',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Hello! How can I help you today?']
            ],
            'model' => 'claude-3-sonnet-20240229',
            'stop_reason' => 'end_turn',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 20
            ]
        ]);

        $this->mockHandler->append(new Response(200, [], $responseBody));

        $messageRequest = new MessageRequest();
        $messageRequest->addMessage(new Message('user', [new TextContent('Hello')]));

        $response = $this->client->sendMessage($messageRequest);

        $this->assertInstanceOf(MessageResponse::class, $response);
        $this->assertEquals('msg_123', $response->getId());
        $this->assertEquals('message', $response->getType());
        $this->assertEquals('assistant', $response->getRole());
        $this->assertEquals([['type' => 'text', 'text' => 'Hello! How can I help you today?']], $response->getContent());
        $this->assertEquals('claude-3-sonnet-20240229', $response->getModel());
        $this->assertEquals('end_turn', $response->getStopReason());
        $this->assertNull($response->getStopSequence());
        $this->assertEquals(['input_tokens' => 10, 'output_tokens' => 20], $response->getUsage());
    }

    public function testStreamMessage()
    {
        $streamedEvents = [
            "event: message_start\ndata: {\"type\":\"message_start\",\"message\":{\"id\":\"msg_123\",\"type\":\"message\",\"role\":\"assistant\",\"content\":[],\"model\":\"claude-3-sonnet-20240229\",\"stop_reason\":null,\"stop_sequence\":null,\"usage\":{\"input_tokens\":10}}}\n\n",
            "event: content_block_start\ndata: {\"type\":\"content_block_start\",\"index\":0,\"content_block\":{\"type\":\"text\",\"text\":\"\"}}\n\n",
            "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"Hello\"}}\n\n",
            "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"! How can I help you today?\"}}\n\n",
            "event: message_delta\ndata: {\"type\":\"message_delta\",\"delta\":{\"stop_reason\":\"end_turn\",\"usage\":{\"output_tokens\":20}}}\n\n",
            "event: message_stop\ndata: {\"type\":\"message_stop\"}\n\n"
        ];

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, implode('', $streamedEvents));
        rewind($stream);

        $this->mockHandler->append(new Response(200, [], new Stream($stream)));

        $messageRequest = new MessageRequest();
        $messageRequest->addMessage(new Message('user', [new TextContent('Hello')]));

        $receivedEvents = [];
        $this->client->streamMessage($messageRequest, function ($chunk) use (&$receivedEvents) {
            $receivedEvents[] = $chunk;
        });

        $this->assertCount(6, $receivedEvents);
        $this->assertInstanceOf(MessageResponse::class, $receivedEvents[0]);
        $this->assertEquals('msg_123', $receivedEvents[0]->getId());
        $this->assertIsArray($receivedEvents[1]);
        $this->assertEquals('content_block_start', $receivedEvents[1]['type']);
        $this->assertIsArray($receivedEvents[2]);
        $this->assertEquals('Hello', $receivedEvents[2]['delta']['text']);
        $this->assertIsArray($receivedEvents[3]);
        $this->assertEquals('! How can I help you today?', $receivedEvents[3]['delta']['text']);
        $this->assertIsArray($receivedEvents[4]);
        $this->assertEquals('end_turn', $receivedEvents[4]['delta']['stop_reason']);
        $this->assertIsArray($receivedEvents[5]);
        $this->assertEquals('message_stop', $receivedEvents[5]['type']);
    }

    public function testMessageRequestWithTool()
    {
        $messageRequest = new MessageRequest();
        $messageRequest->addMessage(new Message('user', [new TextContent('What\'s the weather in San Francisco?')]));

        $weatherTool = new Tool(
            'get_weather',
            'Get the current weather in a given location',
            [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The city and state, e.g. San Francisco, CA'
                    ],
                    'unit' => [
                        'type' => 'string',
                        'enum' => ['celsius', 'fahrenheit'],
                        'description' => 'The unit of temperature, either "celsius" or "fahrenheit"'
                    ]
                ],
                'required' => ['location']
            ]
        );
        $messageRequest->addTool($weatherTool);

        $requestArray = $messageRequest->toArray();

        $this->assertArrayHasKey('tools', $requestArray);
        $this->assertCount(1, $requestArray['tools']);
        $this->assertEquals('get_weather', $requestArray['tools'][0]['name']);
    }

    public function testSendMessageWithImage()
    {
        $imageUrl = "https://upload.wikimedia.org/wikipedia/commons/a/a7/Camponotus_flavomarginatus_ant.jpg";
        $imageData = file_get_contents($imageUrl);
        $base64Image = base64_encode($imageData);

        $responseBody = json_encode([
            'id' => 'msg_123',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'The image shows a close-up photograph of an ant. It appears to be a Camponotus flavomarginatus, also known as a carpenter ant. The ant is shown in great detail, with its characteristic large mandibles, antennae, and segmented body clearly visible. The ant has a reddish-brown coloration and seems to be on a light-colored surface, possibly a leaf or piece of wood.']
            ],
            'model' => 'claude-3-sonnet-20240620',
            'stop_reason' => 'end_turn',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 80
            ]
        ]);

        $this->mockHandler->append(new Response(200, [], $responseBody));

        $messageRequest = new MessageRequest();
        $message = new Message('user');
        $message->addContent(new ImageContent($base64Image, 'image/jpeg'));
        $message->addContent(new TextContent('What is in the above image?'));
        $messageRequest->addMessage($message);

        $response = $this->client->sendMessage($messageRequest);

        $this->assertInstanceOf(MessageResponse::class, $response);
        $this->assertEquals('msg_123', $response->getId());
        $this->assertEquals('message', $response->getType());
        $this->assertEquals('assistant', $response->getRole());
        $this->assertStringContainsString('The image shows a close-up photograph of an ant', $response->getContent()[0]['text']);
        $this->assertEquals('claude-3-sonnet-20240620', $response->getModel());
        $this->assertEquals('end_turn', $response->getStopReason());
        $this->assertNull($response->getStopSequence());
        $this->assertEquals(['input_tokens' => 100, 'output_tokens' => 80], $response->getUsage());
    }
}
