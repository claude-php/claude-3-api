# Claude 3 API PHP Package

A robust PHP package for interacting with Anthropic's Claude 3 API, supporting both text and vision capabilities.

## Features

- Easy-to-use interface for sending messages to Claude (default), OpenAI and DeepSeek
- Support for text-based conversations with a simple chat method
- Streaming support for real-time responses
- Vision capabilities - send images along with text prompts (Claude only)
- Tool usage support (Claude only)
- Comprehensive error handling
- Fully tested with PHPUnit

## Installation

You can install the package via composer:

```bash
composer require claude-php/claude-3-api
```

## Usage

### Basic Chat Usage

For simple text-based interactions, you can use the `chat` method:

### Basic Chat Usage

For simple text-based interactions, you can use the `chat` method in several ways:

1. Send a single string message:

```php
use Claude\Claude3Api\Client;
use Claude\Claude3Api\Config;

// Create a configuration object with your API key
$config = new Config('your-api-key-here');

// Create a client
$client = new Client($config);

// Send a single string message
$response = $client->chat("Hello, Claude");

echo "Claude's response: " . $response->getContent()[0]['text'];
```

2. Send a single message as an array:

```php
$response = $client->chat(['role' => 'user', "content" => "Hello, Claude"]);

echo "Claude's response: " . $response->getContent()[0]['text'];
```

3. Continue a conversation with multiple messages:

```php
$response = $client->chat([
    ['role' => 'user', "content" => "Hello, Claude"],
    ['role' => 'assistant', "content" => "Hello! It's nice to meet you. How can I assist you today?"],
    ['role' => 'user', "content" => "What is the population of Sydney?"],
]);

echo "Claude's response: " . $response->getContent()[0]['text'];
```

4. Specify a model or max tokens (optional):

```php
$response = $client->chat([
    'model' => 'claude-3-opus-20240229',
    'maxTokens' => 1024,
    'messages' => [
        ['role' => 'user', "content" => "Hello, Claude"],
    ]
]);

echo "Claude's response: " . $response->getContent()[0]['text'];
```

The `chat` method is flexible and can handle various input formats, making it easy to interact with Claude in different scenarios.

### Streaming

#### Stream with text message

```php
$client->streamMessage('Hello, how are you?', function ($chunk) {
    echo $chunk;
});
```

#### Stream with Array

```php
$client->streamMessage([
    ['role' => 'user', "content" => "Hello, Claude"],
    ['role' => 'assistant', "content" => "Hello! It's nice to meet you. How can I assist you today?"],
    ['role' => 'user', "content" => "What is the population of Sydney?"],
], function ($chunk) {
    echo $chunk;
});
```

### OpenAI

There is only limited support for OpenAI API for Chat Completion and Streaming.

```php
use Claude\Claude3Api\Config;
use Claude\Claude3Api\Client;

$apiKey = 'sk-#############################';  // OpenAI API Key

$config = new Config($apiKey);
$config->useOpenAI();
$client = new Client($config);

$response = $client->chat("Hello, OpenAI");

echo "OpenAI's response: " . $response->getChoices()[0]['message']['content'];
```

### DeepSeek

There is only limited support for DeepSeek API for Chat Completion and Streaming.

```php
use Claude\Claude3Api\Config;
use Claude\Claude3Api\Client;

$apiKey = 'sk-#############################';  // DeepSeek API Key

$config = new Config($apiKey);
$config->useDeepSeek();
$client = new Client($config);

$response = $client->chat("Hello, DeepSeek");

echo "DeepSeek's response: " . $response->getChoices()[0]['message']['content'];
```

### Other Compatible Providers

There is the flexibility to use other providers, but the API is not fully supported.

```php
use Claude\Claude3Api\Config;
use Claude\Claude3Api\Client;

$apiKey = 'sk-#############################';  // Other API Key
$baseUrl = 'https://api.deepseek.com';
$apiVersion = '2023-06-01';
$model = 'deepseek-chat';
$authType = Config::AUTH_TYPE_BEARER; // or Config::AUTH_TYPE_API_KEY
$messagePath = '/chat/completions';
$maxTokens = 8192;

$config = new Config($apiKey, $apiVersion, $baseUrl, $model, $maxTokens, $authType, $messagePath);
$client = new Client($config);

$response = $client->chat("Hello, Other Provider");

echo "Other Provider's response: " . $response->getChoices()[0]['message']['content'];
```

### Advanced Usage

For more complex scenarios, you can still use the `sendMessage` method with a `MessageRequest` object:

```php
use Claude\Claude3Api\Models\Message;
use Claude\Claude3Api\Models\Content\TextContent;
use Claude\Claude3Api\Requests\MessageRequest;

// Create a message request
$messageRequest = new MessageRequest();

// Add a user message
$userMessage = new Message('user', [
    new TextContent('What is the capital of France?')
]);
$messageRequest->addMessage($userMessage);

// Send the message and get the response
$response = $client->sendMessage($messageRequest);

// Process the response
echo "Claude's response: " . $response->getContent()[0]['text'];
```

### Vision Capabilities

```php
use Claude\Claude3Api\Models\Content\ImageContent;

// Send a message with both image and text
$response = $client->sendMessageWithImage('path/to/image.jpg', 'What is in this image?');

echo "Claude's description: " . $response->getContent()[0]['text'];
```

#### Stream with MessageRequest

```php
$client->streamMessage($messageRequest, function ($chunk) {
    if ($chunk instanceof MessageResponse) {
        // Handle complete message response
    } elseif (is_array($chunk) && isset($chunk['delta']['text'])) {
        echo $chunk['delta']['text'];
    }
});
```

### Using Tools

```php
use Claude\Claude3Api\Models\Tool;

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
                'description' => 'The unit of temperature'
            ]
        ],
        'required' => ['location']
    ]
);

$messageRequest->addTool($weatherTool);

// Create a message request and add the tool
$messageRequest = new MessageRequest();
$messageRequest->addTool($weatherTool);

// Add a user message that might trigger tool use
$userMessage = new Message('user', [
    new TextContent('What\'s the weather like in New York?')
]);
$messageRequest->addMessage($userMessage);

// Send the message
$response = $client->sendMessage($messageRequest);

// The response might include tool use
foreach ($response->getContent() as $content) {
    if ($content['type'] === 'text') {
        echo "Claude's response: " . $content['text'] . "\n";
    } elseif ($content['type'] === 'tool_use') {
        echo "Tool used: " . $content['name'] . "\n";
        echo "Tool input: " . json_encode($content['input']) . "\n";

        // Here you would typically execute the actual tool
        // and send the result back to Claude in a new message
    }
}
```

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Maintainer

This package is maintained by [Dale Hurley](https://dalehurley.com).

### Support Dale's Work

You can support Dale by checking out his other solutions:

- [Full.CX](https://full.cx) - Transform your product vision into actionable dev tasks with AI-powered clarity and precision
- [Custom Homework Maker](https://customhomeworkmaker.com) - Create engaging, personalized homework assignments in seconds with advanced AI technology
- [SpeedBrain](https://speedbrain.app) - Challenge yourself with this addictive, fast-paced knowledge game - completely free!
- [Spotfillr](https://spotfillr.com) - The smart solution for childcare centers to maximize occupancy and revenue through automated casual spot management
- [1-to-5 App](https://www.1to5app.com) - Help your children develop emotional intelligence with this engaging, research-backed emotional learning companion
- [RapidReportCard](https://rapidreportcard.com) - Streamline performance reviews and unlock your team's full potential with AI-powered insights
- [Risks.io](https://www.risks.io) - Stay ahead of project risks with our intelligent platform for seamless risk management and mitigation
- [TimeLodge](https://timelodge.com) - Simplify your business with powerful time tracking, professional invoicing, and seamless payment processing

## License

This Claude 3 API Package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

If you encounter any problems or have any questions, please open an issue in the GitHub repository.

## Disclaimer

This package is not officially associated with Anthropic. Make sure to comply with Anthropic's terms of service when using this package.
