<?php
declare(strict_types=1);

/**
 * simple-view-controller: a lightweight PHP framework focused exclusively on the Controller and View layers
 * Copyright (c) Mirko Pagliai (https://github.com/mirko-pagliai)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Mirko Pagliai (https://github.com/mirko-pagliai)
 * @link          https://github.com/mirko-pagliai/simple-view-controller CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

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
