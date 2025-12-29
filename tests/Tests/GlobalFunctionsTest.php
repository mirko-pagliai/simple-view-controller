<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function SimpleVC\env;

class GlobalFunctionsTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        putenv('TEST_ENV');
        putenv('TEST_BOOL_TRUE');
        putenv('TEST_BOOL_FALSE');
        putenv('TEST_NULL');
    }

    /**
     * @link \SimpleVC\env()
     */
    #[Test]
    public function testEnvReturnsDefaultWhenVariableIsMissing(): void
    {
        $value = env('TEST_ENV', 'default');
        $this->assertSame('default', $value);
    }

    /**
     * @link \SimpleVC\env()
     */
    #[Test]
    public function testEnvReturnsStringValue(): void
    {
        putenv('TEST_ENV=value');

        $value = env('TEST_ENV');
        $this->assertSame('value', $value);
    }

    /**
     * @link \SimpleVC\env()
     */
    #[Test]
    public function testEnvParsesTrueBoolean(): void
    {
        putenv('TEST_BOOL_TRUE=true');

        $value = env('TEST_BOOL_TRUE');
        $this->assertTrue($value);
    }

    /**
     * @link \SimpleVC\env()
     */
    #[Test]
    public function testEnvParsesFalseBoolean(): void
    {
        putenv('TEST_BOOL_FALSE=false');

        $value = env('TEST_BOOL_FALSE');
        $this->assertFalse($value);
    }

    /**
     * @link \SimpleVC\env()
     */
    #[Test]
    public function testEnvParsesNullValue(): void
    {
        putenv('TEST_NULL=null');

        $value = env('TEST_NULL', 'fallback');
        $this->assertNull($value);
    }
}
