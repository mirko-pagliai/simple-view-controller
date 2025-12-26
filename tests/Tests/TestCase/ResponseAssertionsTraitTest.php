<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\TestCase;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SimpleVC\TestCase\ResponseAssertionsTrait;
use Symfony\Component\HttpFoundation\Response;
use TestApp\TestCase\TestCaseWithResponseAssertionsTrait;

#[CoversTrait(ResponseAssertionsTrait::class)]
class ResponseAssertionsTraitTest extends TestCase
{
    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseExists()
     */
    #[Test]
    public function testAssertResponseExists(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response())
            ->assertResponseExists();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('`$this->response` property has not been set.');
        $testCase
            ->setResponse(null)
            ->assertResponseExists();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseStatusCode()
     */
    #[Test]
    public function testAssertResponseStatusCode(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 200))
            ->assertResponseStatusCode(200);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response status code is 404.');
        $testCase->assertResponseStatusCode(404);
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsSuccessful()
     */
    #[Test]
    public function testAssertResponseIsSuccessful(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 200))
            ->assertResponseIsSuccessful();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is successful.');
        $testCase
            ->setResponse(new Response('', 404))
            ->assertResponseIsSuccessful();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsNotFound()
     */
    #[Test]
    public function testAssertResponseIsNotFound(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 404))
            ->assertResponseIsNotFound();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is not found.');
        $testCase
            ->setResponse(new Response('', 200))
            ->assertResponseIsNotFound();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsServerError()
     */
    #[Test]
    public function testAssertResponseIsServerError(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 500))
            ->assertResponseIsServerError();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is a server error.');
        $testCase
            ->setResponse(new Response('', 200))
            ->assertResponseIsServerError();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsRedirect()
     */
    #[Test]
    public function testAssertResponseIsRedirect(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 302))
            ->assertResponseIsRedirect();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response is a redirect.');
        $testCase
            ->setResponse(new Response('', 200))
            ->assertResponseIsRedirect();
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseIsRedirect()
     */
    #[Test]
    public function testAssertResponseIsRedirectWithExpectedUrl(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 302, ['Location' => '/my/url']))
            ->assertResponseIsRedirect('/my/url');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response header "Location" has value "/my/url".');
        $testCase
            ->setResponse(new Response('', 302, ['Location' => '/this/is/not/my/url']))
            ->assertResponseIsRedirect('/my/url');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseContains()
     */
    #[Test]
    public function testAssertResponseContains(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('my content'))
            ->assertResponseContains('my content');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response contains "not this".');
        $testCase->assertResponseContains('not this');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseNotContains()
     */
    #[Test]
    public function testAssertResponseNotContains(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('my content'))
            ->assertResponseNotContains('not this');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response does not contain "my content".');
        $testCase->assertResponseNotContains('my content');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseMatchesRegex()
     */
    #[Test]
    public function testAssertResponseMatchesRegex(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('my content'))
            ->assertResponseMatchesRegex('/my.*/');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response matches pattern "/\d+/".');
        $testCase->assertResponseMatchesRegex('/\d+/');
    }

    /**
     * @link \SimpleVC\TestCase\ResponseAssertionsTrait::assertResponseHeader()
     */
    #[Test]
    public function testAssertResponseHeader(): void
    {
        $testCase = new TestCaseWithResponseAssertionsTrait('myTest');

        $testCase
            ->setResponse(new Response('', 200, ['Content-Type'=> 'text/html']))
            ->assertResponseHeader('Content-Type', 'text/html');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response header "Content-Type" has value "application/json".');
        $testCase->assertResponseHeader('Content-Type', 'application/json');
    }
}
