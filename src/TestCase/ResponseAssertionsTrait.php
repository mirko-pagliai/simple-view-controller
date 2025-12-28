<?php
declare(strict_types=1);

namespace SimpleVC\TestCase;

use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a set of assertion methods to validate properties and behavior of an HTTP response in tests.
 *
 * This trait can be used in any test class that works with Symfony Response objects. The test class must have a
 *  `$response` property of type `Response|null`.
 *
 * @mixin \PHPUnit\Framework\TestCase
 */
trait ResponseAssertionsTrait
{
    /**
     * The HTTP response to assert against.
     *
     * Must be set before calling any assertion methods.
     *
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    protected ?Response $_response = null;

    /**
     * Asserts that a response has been set.
     *
     * This is a prerequisite check for all response assertion methods. If no response exists, the test will fail.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If the response is null
     */
    public function assertResponseExists(): void
    {
        $this->assertNotNull($this->_response, '`$this->response` property has not been set.');
    }

    /**
     * Asserts that the response status code matches the expected value.
     *
     * This method verifies that the HTTP status code of the response is equal to the specified expected code.
     * Optionally, a custom assertion message can be provided for better clarity in case of failure.
     *
     * @param int $expected The expected status code
     * @param string|null $message Optional custom message for the assertion
     * @return void
     */
    public function assertResponseStatusCode(int $expected, ?string $message = null): void
    {
        $this->assertResponseExists();

        $this->assertSame(
            $expected,
            $this->_response->getStatusCode(),
            $message ?? "Failed asserting that response status code is {$expected}.",
        );
    }

    /**
     * Asserts that the response is successful (2xx status code).
     *
     * @return void
     */
    public function assertResponseIsSuccessful(): void
    {
        $this->assertResponseExists();

        $this->assertTrue($this->_response->isSuccessful(), 'Failed asserting that response is successful.');
    }

    /**
     * Asserts that the response has a "404 Not Found" status code.
     *
     * @return void
     */
    public function assertResponseError(): void
    {
        $this->assertResponseStatusCode(404, 'Failed asserting that response is not found.');
    }

    /**
     * Asserts that the response has a "500 Internal Server Error" status code.
     *
     * @return void
     */
    public function assertResponseFailure(): void
    {
        $this->assertResponseStatusCode(500, 'Failed asserting that response is a server error.');
    }

    /**
     * Asserts that the response is a redirect and optionally verifies the redirect location.
     *
     * This method checks if the response indicates a redirection and validates the redirection URL when a specific URL
     *  is expected.
     *
     * @param string|null $expectedUrl The expected URL to which the response should redirect, or null to skip this check
     * @return void
     */
    public function assertResponseIsRedirect(?string $expectedUrl = null): void
    {
        $this->assertResponseExists();

        $this->assertTrue($this->_response->isRedirect(), 'Failed asserting that response is a redirect.');

        if ($expectedUrl !== null) {
            $this->assertResponseHeader('Location', $expectedUrl);
        }
    }

    /**
     * Asserts that the response content is empty.
     *
     * Useful for verifying that the response contains no content, ensuring it meets expected behavior for empty responses.
     *
     * @return void
     */
    public function assertResponseIsEmpty(): void
    {
        $this->assertResponseExists();

        $this->assertEmpty($this->_response->getContent(), 'Failed asserting that response is empty.');
    }

    /**
     * Asserts that the response content is not empty.
     *
     * Useful for ensuring that the response contains data and is not blank or null, which can be important for
     * verifying the presence of expected output in test cases.
     *
     * @param string|null $message Custom failure message for the assertion
     * @return void
     */
    public function assertResponseIsNotEmpty(?string $message = null): void
    {
        $this->assertResponseExists();

        $this->assertNotEmpty(
            $this->_response->getContent(),
            $message ?? 'Failed asserting that response is not empty.',
        );
    }

    /**
     * Asserts that the response content contains a specific string.
     *
     * Useful for checking if specific HTML elements, text, or data are present in the rendered output.
     *
     * @param string $needle The string to search for in the response content
     * @return void
     */
    public function assertResponseContains(string $needle): void
    {
        $this->assertResponseExists();

        $this->assertResponseIsNotEmpty(
            "Failed asserting that response contains \"{$needle}\", because the response is empty.",
        );

        $this->assertStringContainsString(
            $needle,
            $this->_response->getContent() ?: '',
            "Failed asserting that response contains \"{$needle}\".",
        );
    }

    /**
     * Asserts that the response content does not contain a specific string.
     *
     * Useful for verifying that sensitive data or unwanted content is not present in the rendered output.
     *
     * @param string $needle The string that should not appear in the response content
     * @return void
     */
    public function assertResponseNotContains(string $needle): void
    {
        $this->assertResponseExists();

        $this->assertResponseIsNotEmpty(
            "Failed asserting that response does not contain \"{$needle}\", because the response is empty.",
        );

        $this->assertStringNotContainsString(
            $needle,
            $this->_response->getContent() ?: '',
            "Failed asserting that response does not contain \"{$needle}\".",
        );
    }

    /**
     * Asserts that the response content matches a regular expression.
     *
     * Useful for complex pattern matching in the HTML output, such as validating specific HTML structures or data
     *  formats.
     *
     * @param string $pattern The regular expression pattern to match
     * @return void
     */
    public function assertResponseMatchesRegex(string $pattern): void
    {
        $this->assertResponseExists();

        $this->assertMatchesRegularExpression(
            $pattern,
            $this->_response->getContent() ?: '',
            "Failed asserting that response matches pattern \"{$pattern}\".",
        );
    }

    /**
     * Asserts that a specific HTTP header has an expected value.
     *
     * @param string $header The header name (e.g., `Content-Type`, `Cache-Control`)
     * @param string $expected The expected header value
     * @return void
     */
    public function assertResponseHeader(string $header, string $expected): void
    {
        $this->assertResponseExists();

        $this->assertSame(
            $expected,
            $this->_response->headers->get($header),
            'Failed asserting that response header "' . $header . '" has value "' . $expected . '".',
        );
    }
}
