<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\TestCase;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SimpleVC\TestCase\ResponseAssertionsTrait;
use Symfony\Component\HttpFoundation\Response;

#[CoversTrait(ResponseAssertionsTrait::class)]
class ResponseAssertionsTraitTest extends TestCase
{
    use ResponseAssertionsTrait;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->_response = new Response(
            'This is the content of the Response',
            200,
            ['Content-Type' => 'text/html'],
        );
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseExists()
     */
    #[Test]
    public function testAssertResponseExists(): void
    {
        $this->assertResponseExists();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('`$this->response` property has not been set.');
        $this->_response = null;
        $this->assertResponseExists();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseStatusCode()
     */
    #[Test]
    public function testAssertResponseStatusCode(): void
    {
        $this->assertResponseStatusCode(200);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is 404.');
        $this->assertResponseStatusCode(404);
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsSuccessful()
     */
    #[Test]
    public function testAssertResponseIsSuccessful(): void
    {
        $this->assertResponseIsSuccessful();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is successful.');
        $this->_response = new Response('', 404);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseError()
     */
    #[Test]
    public function testAssertResponseError(): void
    {
        $this->_response = new Response('', 404);
        $this->assertResponseError();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is not found.');
        $this->_response = new Response('', 200);
        $this->assertResponseError();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseFailure()
     */
    #[Test]
    public function testAssertResponseFailure(): void
    {
        $this->_response = new Response('', 500);
        $this->assertResponseFailure();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is a server error.');
        $this->_response = new Response('', 200);
        $this->assertResponseFailure();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsRedirect()
     */
    #[Test]
    public function testAssertResponseIsRedirect(): void
    {
        $this->_response = new Response('', 302);
        $this->assertResponseIsRedirect();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is a redirect.');
        $this->_response = new Response('', 200);
        $this->assertResponseIsRedirect();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsRedirect()
     */
    #[Test]
    public function testAssertResponseIsRedirectWithExpectedUrl(): void
    {
        $this->_response = new Response('', 302, ['Location' => '/my/url']);
        $this->assertResponseIsRedirect('/my/url');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response header "Location" has value "/my/url".');
        $this->_response = new Response('', 302, ['Location' => '/this/is/not/my/url']);
        $this->assertResponseIsRedirect('/my/url');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsEmpty()
     */
    #[Test]
    public function testAssertResponseIsEmpty(): void
    {
        $this->_response = new Response();
        $this->assertResponseIsEmpty();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is empty.');
        $this->_response = new Response('this response is not empty');
        $this->assertResponseIsEmpty();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsNotEmpty()
     */
    #[Test]
    public function testAssertResponseIsNotEmpty(): void
    {
        $this->assertResponseIsNotEmpty();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is not empty.');
        $this->_response = new Response();
        $this->assertResponseIsNotEmpty();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseContains()
     */
    #[Test]
    public function testAssertResponseContains(): void
    {
        $this->assertResponseContains('content of');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response contains "not this".');
        $this->assertResponseContains('not this');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseContains()
     */
    #[Test]
    public function testAssertResponseContainsWithEmptyResponse(): void
    {
        $this->_response = new Response();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response contains "content of", because the response is empty.');
        $this->assertResponseContains('content of');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseNotContains()
     */
    #[Test]
    public function testAssertResponseNotContains(): void
    {
        $this->assertResponseNotContains('not this');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response does not contain "content of".');
        $this->assertResponseNotContains('content of');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseNotContains()
     */
    #[Test]
    public function testAssertResponseNotContainsWithEmptyResponse(): void
    {
        $this->_response = new Response();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response does not contain "content of", because the response is empty.');
        $this->assertResponseNotContains('content of');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseMatchesRegex()
     */
    #[Test]
    public function testAssertResponseMatchesRegex(): void
    {
        $this->assertResponseMatchesRegex('/^This(\s)?is/');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response matches pattern "/\d+/".');
        $this->assertResponseMatchesRegex('/\d+/');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseHeader()
     */
    #[Test]
    public function testAssertResponseHeader(): void
    {
        $this->assertResponseHeader('Content-Type', 'text/html');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response header "Content-Type" has value "application/json".');
        $this->assertResponseHeader('Content-Type', 'application/json');
    }
}
