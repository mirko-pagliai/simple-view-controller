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

namespace SimpleVC\Test\Tests\View;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use SimpleVC\View\View;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
    /**
     * @link \SimpleVC\View\View::__construct()
     *
     * @param array<string|null> $args
     * @param string $expectedTemplatePath
     * @param string|null $expectedLayout
     */
    #[Test]
    #[TestWith([[], TEMPLATES, 'layouts/default.php'])]
    #[TestWith([[TMP], TMP, 'layouts/default.php'])]
    #[TestWith([[TEMPLATES, null], TEMPLATES, null])]
    public function testConstruct(array $args, string $expectedTemplatePath, ?string $expectedLayout): void
    {
        $view = new class (...$args) extends View {
            public string $templatePath;

            public ?string $layout;
        };

        $this->assertSame($expectedTemplatePath, $view->templatePath);
        $this->assertSame($expectedLayout, $view->layout);
    }

    /**
     * @link \SimpleVC\View\View::__construct()
     */
    #[Test]
    public function testConstructWithNoExistingTemplatePath(): void
    {
        $this->expectExceptionMessage('Template path `/noExistingTemplatePath` does not exist');
        new View('/noExistingTemplatePath');
    }

    /**
     * @link \SimpleVC\View\View::setLayout()
     */
    #[Test]
    #[TestWith(['layouts/custom.php'])]
    #[TestWith([null])]
    public function testSetLayout(?string $layout): void
    {
        $view = new class extends View {
            public ?string $layout;
        };
        $result = $view->setLayout($layout);
        $this->assertSame($view, $result);
        $this->assertSame($layout, $view->layout);
    }

    /**
     * @link \SimpleVC\View\View::setRequest()
     */
    #[Test]
    public function testSetRequest(): void
    {
        $request = $this->createStub(Request::class);

        $view = new class extends View
        {
            public ?Request $request;
        };
        $result = $view->setRequest($request);
        $this->assertSame($view, $result);
        $this->assertSame($request, $view->request);
    }

    /**
     * @link \SimpleVC\View\View::set()
     */
    #[Test]
    public function testSet(): void
    {
        $expected = [
            'key' => 'value',
            'secondKey' => 'secondValue',
            'thirdKey' => 'thirdValue',
        ];

        $view = new class extends View {
            public array $data = [];
        };

        $view->set(['key' => 'value']);
        $result = $view->set(['secondKey' => 'secondValue', 'thirdKey' => 'thirdValue']);
        $this->assertSame($view, $result);
        $this->assertSame($expected, $view->data);
    }

    /**
     * @link \SimpleVC\View\View::set()
     */
    #[Test]
    public function testSetWithKeyAlreadyExists(): void
    {
        $view = new View();
        $view->set(['myKey' => 'value']);

        $this->expectExceptionMessage('Data key `myKey` already exists.');
        $view->set(['myKey' => 'newValue']);
    }

    /**
     * @link \SimpleVC\View\View::renderFile()
     */
    #[Test]
    public function testRenderFile(): void
    {
        $view = new class extends View {
            public function renderFile(string $file, array $data): string
            {
                return parent::renderFile($file, $data);
            }
        };

        $result = $view->renderFile('example.php', ['content' => 'test']);
        $this->assertSame('test', trim($result));
    }

    /**
     * @link \SimpleVC\View\View::renderFile()
     */
    #[Test]
    public function testRenderFileWithNoExistingFile(): void
    {
        $view = new class extends View {
            public function renderFile(string $file, array $data): string
            {
                return parent::renderFile($file, $data);
            }
        };

        $this->expectExceptionMessage('Template file `' . TEMPLATES . '/noExistingFile.php` not found.');
        $view->renderFile('noExistingFile.php', []);
    }

    /**
     * @link \SimpleVC\View\View::renderFile()
     */
    #[Test]
    public function testRenderFileOnError(): void
    {
        $view = $this->getMockBuilder(View::class)
            ->onlyMethods(['includeTemplateFile'])
            ->getMock();

        $view
            ->expects($this->once())
            ->method('includeTemplateFile')
            ->with(TEMPLATES . '/example.php', [])
            ->willReturn(false);

        $this->expectExceptionMessage('Template file `' . TEMPLATES . '/example.php` returned invalid output.');
        $view->render('example.php');
    }

    /**
     * @link \SimpleVC\View\View::render()
     */
    #[Test]
    public function testRender(): void
    {
        $view = new View();
        $view->set(['content' => 'test']);

        $expected = '<html lang="en"><body>test</body></html>';
        $result = $view->render('example.php');
        $this->assertSame($expected, trim($result));

        $view->setLayout(null);

        $result = $view->render('example.php');
        $this->assertSame('test', $result);
    }

    /**
     * @link \SimpleVC\View\View::render()
     */
    #[Test]
    public function testRenderAutoDetectTemplate(): void
    {
        $view = $this->getMockBuilder(View::class)
            ->setConstructorArgs([null, null])
            ->onlyMethods(['autoDetectTemplate'])
            ->getMock();

        $view
            ->expects($this->once())
            ->method('autoDetectTemplate')
            ->willReturn('example.php');

        $view->set(['content' => 'test']);

        $result = $view->render();
        $this->assertSame('test', $result);
    }

    /**
     * @link \SimpleVC\View\View::autoDetectTemplate()
     *
     * @param array{class-string, non-empty-string} $controller
     * @param string $expected
     */
    #[Test]
    #[TestWith([['MyApp\Controller\HomeController', 'index'], 'Home/index.php'])]
    #[TestWith([['MyApp\Controller\HomeController', 'showProfile'], 'Home/show_profile.php'])]
    #[TestWith([['MyApp\Controller\UserController', 'editSettings'], 'User/edit_settings.php'])]
    #[TestWith([['App\Controller\AdminController', 'dashboard'], 'Admin/dashboard.php'])]
    #[TestWith([['MyApp\SomeController', 'myAction'], 'Some/my_action.php'])]
    #[TestWith([['MyApp\MyTest', 'index'], 'MyTest/index.php'])]
    #[TestWith([['Controller', 'test'], '/test.php'])]
    #[TestWith([['FooBarController', 'bazQux'], 'FooBar/baz_qux.php'])]
    #[TestWith([['Very\Deep\Namespace\Structure\HomeController', 'index'], 'Home/index.php'])]
    public function testAutoDetectTemplateWithArrayController(array $controller, string $expected): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', $controller);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();
        $this->assertSame($expected, $result);
    }

    /**
     * @link \SimpleVC\View\View::autoDetectTemplate()
     */
    #[Test]
    #[TestWith(['MyApp\Controller\HomeController::index', 'Home/index.php'])]
    #[TestWith(['MyApp\Controller\HomeController::showProfile', 'Home/show_profile.php'])]
    #[TestWith(['MyApp\Controller\UserController::editSettings', 'User/edit_settings.php'])]
    #[TestWith(['App\Controller\AdminController::dashboard', 'Admin/dashboard.php'])]
    #[TestWith(['MyApp\SomeController::myAction', 'Some/my_action.php'])]
    #[TestWith(['Controller::test', '/test.php'])]
    #[TestWith(['FooBarController::bazQux', 'FooBar/baz_qux.php'])]
    public function testAutoDetectTemplateWithStringController(string $controller, string $expected): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', $controller);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();
        $this->assertSame($expected, $result);
    }

    /**
     * @link \SimpleVC\View\View::autoDetectTemplate()
     *
     * @param array{class-string, non-empty-string} $controller
     * @param string $expected
     */
    #[Test]
    #[TestWith([['MyApp\Controller\APIController', 'getUsers'], 'API/get_users.php'])]
    #[TestWith([['App\Controller\XMLController', 'parseXML'], 'XML/parse_xml.php'])]
    #[TestWith([['MyApp\HTMLParserController', 'convertHTML'], 'HTMLParser/convert_html.php'])]
    #[TestWith([['App\Controller\OAuth2Controller', 'validateToken'], 'OAuth2/validate_token.php'])]
    public function testAutoDetectTemplateWithAcronyms(array $controller, string $expected): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $request->attributes->set('_controller', $controller);
        $view->setRequest($request);

        $result = $view->autoDetectTemplate();
        $this->assertSame($expected, $result);
    }

    /**
     * @link \SimpleVC\View\View::autoDetectTemplate()
     */
    #[Test]
    public function testAutoDetectTemplateWithoutRequest(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $this->expectExceptionMessage('`$this->request` not set. Call `setRequest()` before `render()`.');
        $view->autoDetectTemplate();
    }

    /**
     * @link \SimpleVC\View\View::autoDetectTemplate()
     */
    #[Test]
    public function testAutoDetectTemplateWithoutControllerAttribute(): void
    {
        $view = new class extends View {
            public function autoDetectTemplate(): string
            {
                return parent::autoDetectTemplate();
            }
        };

        $request = Request::create('/test');
        $view->setRequest($request);

        $this->expectExceptionMessage('`_controller` attribute not found in `$this->request`.');
        $view->autoDetectTemplate();
    }

    #[Test]
    #[TestWith(['index', 'index'])]
    #[TestWith(['myAction', 'my_action'])]
    #[TestWith(['showProfile', 'show_profile'])]
    #[TestWith(['editUserSettings', 'edit_user_settings'])]
    #[TestWith(['create', 'create'])]
    #[TestWith(['HTMLParser', 'html_parser'])]
    #[TestWith(['parseHTML', 'parse_html'])]
    #[TestWith(['getHTMLContent', 'get_html_content'])]
    #[TestWith(['XMLToJSON', 'xml_to_json'])]
    #[TestWith(['APIController', 'api_controller'])]
    #[TestWith(['getURLFromAPI', 'get_url_from_api'])]
    #[TestWith(['HTTPSConnection', 'https_connection'])]
    #[TestWith(['base64Encode', 'base64_encode'])]
    #[TestWith(['utf8ToAscii', 'utf8_to_ascii'])]
    #[TestWith(['getUserProfileById', 'get_user_profile_by_id'])]
    #[TestWith(['exportDataToCSV', 'export_data_to_csv'])]
    #[TestWith(['validateOAuthToken', 'validate_o_auth_token'])]
    #[TestWith(['convertPDFToImage', 'convert_pdf_to_image'])]
    #[TestWith(['a', 'a'])]
    #[TestWith(['A', 'a'])]
    #[TestWith(['ABC', 'abc'])]
    #[TestWith(['ABCDef', 'abc_def'])]
    #[TestWith(['aB', 'a_b'])]
    #[TestWith(['aBC', 'a_bc'])]
    #[TestWith(['aBCDef', 'a_bc_def'])]
    #[TestWith(['my_action', 'my_action'])]
    public function testCamelToSnake(string $input, string $expected): void
    {
        $view = new class extends View {
            public function camelToSnake(string $input): string
            {
                return parent::camelToSnake($input);
            }
        };

        $result = $view->camelToSnake($input);
        $this->assertSame($expected, $result, "Failed: '{$input}' expected '{$expected}', got '{$result}'");
    }
}
