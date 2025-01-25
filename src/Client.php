<?php

namespace Claude\Claude3Api;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Claude\Claude3Api\Exceptions\ApiException;
use Claude\Claude3Api\Models\Content\ImageContent;
use Claude\Claude3Api\Models\Content\TextContent;
use Claude\Claude3Api\Models\Message;
use Claude\Claude3Api\Requests\MessageRequest;
use Claude\Claude3Api\Responses\MessageResponse;

class Client
{
    private HttpClient $httpClient;

    public function __construct(private Config $config)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'anthropic-version' => $this->config->getApiVersion(),
        ];

        if ($this->config->getAuthType() === 'x-api-key') {
            $headers['x-api-key'] = $this->config->getApiKey();
        } elseif ($this->config->getAuthType() === 'bearer') {
            $headers['Authorization'] = 'Bearer ' . $this->config->getApiKey();
        }

        $this->httpClient = new HttpClient([
            'headers' => $headers,
        ]);
    }

    private function formatRequest(array|string $request): MessageRequest
    {
        $messageRequest = new MessageRequest($this->config);
        $messageRequest->setModel($this->config->getModel());

        if (is_string($request)) {
            $messageRequest->addMessage(new Message('user', [new TextContent($request)]));
        } elseif (isset($request['messages'])) {
            // Handle case with specified model and messages
            if (isset($request['model'])) {
                $messageRequest->setModel($request['model']);
            }
            if (isset($request['maxTokens'])) {
                $messageRequest->setMaxTokens($request['maxTokens']);
            }
            if (isset($request['temperature'])) {
                $messageRequest->setTemperature($request['temperature']);
            }
            if (isset($request['system'])) {
                $messageRequest->setSystem($request['system']);
            }
            foreach ($request['messages'] as $message) {
                $messageRequest->addMessage(new Message($message['role'], [new TextContent($message['content'])]));
            }
        } elseif (isset($request['role']) && isset($request['content'])) {
            $messageRequest->addMessage(new Message($request['role'], [new TextContent($request['content'])]));
        } else {
            foreach ($request as $message) {
                $messageRequest->addMessage(new Message($message['role'], [new TextContent($message['content'])]));
            }
        }
        return $messageRequest;
    }

    public function chat(array|string $request): MessageResponse
    {
        $messageRequest = $this->formatRequest($request);
        return $this->sendMessage($messageRequest);
    }

    public function sendMessage(MessageRequest|array $request): MessageResponse
    {
        try {
            $url = rtrim($this->config->getBaseUrl(), '/') . $this->config->getMessagePath();
            $response = $this->httpClient->post($url, [
                'json' => is_array($request) ? $request : $request->toArray(),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return new MessageResponse($data);
        } catch (RequestException $e) {
            throw new ApiException('Error sending message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function streamMessage($request, callable $callback)
    {
        if (!$request instanceof MessageRequest) {
            $request = $this->formatRequest($request);
        }

        $request->setStream(true);

        try {
            $url = rtrim($this->config->getBaseUrl(), '/') . $this->config->getMessagePath();
            $response = $this->httpClient->post($url, [
                'json' => $request->toArray(),
                'stream' => true,
            ]);

            $body = $response->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                $events = $this->parseSSE($buffer);

                foreach ($events as $event) {
                    $data = json_decode($event['data'], true);
                    switch ($event['event']) {
                        case 'message_start':
                            if (isset($data['message'])) {
                                $callback(new MessageResponse($data['message']));
                            }
                            break;
                        case 'message_stop':
                            if (isset($data['message'])) {
                                $callback(new MessageResponse($data['message']));
                            } else {
                                $callback($data); // Pass the raw data if 'message' is not present
                            }
                            return;
                        case 'message_other':
                            if (isset($data)) {
                                $callback($data);
                            }
                            break;
                        case 'content_block_start':
                        case 'content_block_delta':
                        case 'message_delta':
                            $callback($data);
                            break;
                        default:
                            $callback($data);
                            break;
                    }
                }

                $buffer = $this->trimBuffer($buffer);
            }
        } catch (RequestException $e) {
            throw new ApiException('Error streaming message: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function sendMessageWithImage(string $imagePath, string $prompt): MessageResponse
    {
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);
        $mimeType = mime_content_type($imagePath);

        $messageRequest = new MessageRequest();
        $message = new Message('user');
        $message->addContent(new ImageContent($base64Image, $mimeType));
        $message->addContent(new TextContent($prompt));
        $messageRequest->addMessage($message);

        return $this->sendMessage($messageRequest);
    }

    private function parseSSE($buffer)
    {
        $events = [];
        $lines = explode("\n\n", $buffer);

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $event = [
                'event' => '',
                'data' => '',
            ];

            foreach (explode("\n", $line) as $part) {
                if (strpos($part, 'event:') === 0) {
                    $event['event'] = trim(substr($part, 6));
                } elseif (strpos($part, 'data:') === 0) {
                    $event['data'] = trim(substr($part, 5));
                }
            }

            if (!empty($event['event']) && !empty($event['data'])) {
                $events[] = $event;
            } elseif (strpos($part, 'data:') === 0) {
                $event['data'] = trim(substr($part, 5));
                if (empty($event['event']) && $event['data'] !== '[DONE]') {
                    $event['event'] = 'message_other';
                } elseif (empty($event['event']) && $event['data'] === '[DONE]') {
                    $event['event'] = 'message_stop';
                }
                $events[] = $event;
            }
        }

        return $events;
    }

    private function trimBuffer($buffer)
    {
        $lastNewLine = strrpos($buffer, "\n\n");
        if ($lastNewLine !== false) {
            return substr($buffer, $lastNewLine + 2);
        }
        return $buffer;
    }
}
