<?php
declare(strict_types=1);

namespace TestApp\TestCase;

use PHPUnit\Framework\TestCase;
use SimpleVC\TestCase\ResponseAssertionsTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fake test case class that uses the ResponseAssertionsTrait.
 *
 * This class is only there to facilitate testing against `ResponseAssertionsTrait`, as it exposes its methods publicly.
 */
class TestCaseWithResponseAssertionsTrait extends TestCase
{
    use ResponseAssertionsTrait {
        assertResponseExists as public;
        assertResponseStatusCode as public;
        assertResponseIsSuccessful as public;
        assertResponseIsNotFound as public;
        assertResponseIsServerError as public;
        assertResponseIsRedirect as public;
        assertResponseIsEmpty as public;
        assertResponseIsNotEmpty as public;
        assertResponseContains as public;
        assertResponseNotContains as public;
        assertResponseMatchesRegex as public;
        assertResponseHeader as public;
    }

    public function setResponse(?Response $response): self
    {
        $this->response = $response;

        return $this;
    }
}
