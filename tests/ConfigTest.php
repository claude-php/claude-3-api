<?php

namespace Claude\Claude3Api\Tests;

use PHPUnit\Framework\TestCase;
use Claude\Claude3Api\Config;

class ConfigTest extends TestCase
{
    public function testDefaultValues()
    {
        $config = new Config('test-api-key');

        $this->assertEquals('test-api-key', $config->getApiKey());
        $this->assertEquals(Config::DEFAULT_API_VERSION, $config->getApiVersion());
        $this->assertEquals(Config::DEFAULT_BASE_URL, $config->getBaseUrl());
        $this->assertEquals(Config::DEFAULT_MODEL, $config->getModel());
        $this->assertEquals(Config::DEFAULT_MAX_TOKENS, $config->getMaxTokens());
        $this->assertEquals(Config::DEFAULT_AUTH_TYPE, $config->getAuthType());
        $this->assertEquals(Config::DEFAULT_MESSAGE_PATH, $config->getMessagePath());
    }

    public function testSetMaxTokens()
    {
        $config = new Config('test-api-key');
        $initialMaxTokens = $config->getMaxTokens();

        $newMaxTokens = 50000;
        $config->setMaxTokens($newMaxTokens);

        $this->assertEquals($newMaxTokens, $config->getMaxTokens());
        $this->assertNotEquals($initialMaxTokens, $config->getMaxTokens());
    }

    public function testBetaFeatures()
    {
        $config = new Config('test-api-key');

        // Test enableBetaFeature
        $config->enableBetaFeature('output-128k-2025-02-19');
        $this->assertTrue($config->isBetaFeatureEnabled('output-128k-2025-02-19'));

        // Test disableBetaFeature
        $config->disableBetaFeature('output-128k-2025-02-19');
        $this->assertFalse($config->isBetaFeatureEnabled('output-128k-2025-02-19'));

        // Test setBetaFeatures
        $config->setBetaFeatures(['output-128k-2025-02-19' => true]);
        $this->assertTrue($config->isBetaFeatureEnabled('output-128k-2025-02-19'));

        // Test convenience methods
        $config = new Config('test-api-key');
        $config->enable128kOutput();
        $this->assertTrue($config->isBetaFeatureEnabled('output-128k-2025-02-19'));

        // Test maxTokens is not changed by enable128kOutput
        $this->assertEquals(Config::DEFAULT_MAX_TOKENS, $config->getMaxTokens());

        // Test enable128kOutputWithTokens
        $config = new Config('test-api-key');
        $customTokens = 100000;
        $config->enable128kOutputWithTokens($customTokens);
        $this->assertTrue($config->isBetaFeatureEnabled('output-128k-2025-02-19'));
        $this->assertEquals($customTokens, $config->getMaxTokens());

        // Test with default value
        $config = new Config('test-api-key');
        $config->enable128kOutputWithTokens();
        $this->assertTrue($config->isBetaFeatureEnabled('output-128k-2025-02-19'));
        $this->assertEquals(131072, $config->getMaxTokens());
    }

    public function testStrictTypeMaxTokens()
    {
        // This would cause a TypeError with strict_types if maxTokens was a string
        $config = new Config('test-api-key');
        $config->setMaxTokens(10000);
        $this->assertIsInt($config->getMaxTokens());

        // Test initial constructor with integer
        $config = new Config(
            'test-api-key',
            Config::DEFAULT_API_VERSION,
            Config::DEFAULT_BASE_URL,
            Config::DEFAULT_MODEL,
            50000
        );
        $this->assertEquals(50000, $config->getMaxTokens());
        $this->assertIsInt($config->getMaxTokens());
    }

    public function testUseClaude()
    {
        $config = new Config('test-api-key');
        $config->setModel('custom-model');
        $config->setMaxTokens(1000);

        // Reset to Claude defaults
        $config->useClaude();

        $this->assertEquals(Config::CLAUDE_MODEL, $config->getModel());
        $this->assertEquals(Config::CLAUDE_MAX_TOKENS, $config->getMaxTokens());
        $this->assertEquals(Config::CLAUDE_API_VERSION, $config->getApiVersion());
        $this->assertEquals(Config::CLAUDE_BASE_URL, $config->getBaseUrl());
        $this->assertEquals(Config::CLAUDE_AUTH_TYPE, $config->getAuthType());
        $this->assertEquals(Config::CLAUDE_MESSAGE_PATH, $config->getMessagePath());
    }
}
