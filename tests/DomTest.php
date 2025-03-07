<?php

namespace HeadlessChromium\Test;

use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;

/**
 * @covers \HeadlessChromium\Dom\Dom
 */
class DomTest extends BaseTestCase
{
    /**
     * @var Browser\ProcessAwareBrowser
     */
    public static $browser;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $factory = new BrowserFactory();
        self::$browser = $factory->createBrowser();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$browser->close();
    }

    private function openSitePage($file)
    {
        $page = self::$browser->createPage();
        $page->navigate(self::sitePath($file))->waitForNavigation();

        return $page;
    }

    public function testSearchByCssSelector(): void
    {
        $page = $this->openSitePage('domForm.html');
        $element = $page->dom()->querySelector('button');

        $this->assertNotNull($element);
    }

    public function testSearchByCssSelectorAll(): void
    {
        $page = $this->openSitePage('domForm.html');

        $elements = $page->dom()->querySelectorAll('div');

        $this->assertCount(2, $elements);
    }

    public function testSearchByXpath(): void
    {
        $page = $this->openSitePage('domForm.html');

        $elements = $page->dom()->search('//*/div');

        $this->assertCount(2, $elements);
    }

    public function testClick(): void
    {
        $page = $this->openSitePage('domForm.html');

        $element = $page->dom()->querySelector('#myinput');

        $value = $page
            ->evaluate('document.activeElement === document.querySelector("#myinput");')
            ->getReturnValue();

        $this->assertFalse($value);

        // press the Tab key
        $element->click();

        // test the the focus switched to #myinput
        $value = $page
            ->evaluate('document.activeElement === document.querySelector("#myinput");')
            ->getReturnValue();

        $this->assertTrue($value);
    }

    public function testType(): void
    {
        $page = $this->openSitePage('domForm.html');

        $element = $page->dom()->querySelector('#myinput');

        $element->click();
        $element->sendKeys('bar');

        $value = $page
            ->evaluate('document.querySelector("#myinput").value;')
            ->getReturnValue();

        // checks if the input contains the typed text
        $this->assertEquals('bar', $value);
    }

    public function testGetText(): void
    {
        $page = $this->openSitePage('domForm.html');

        $element = $page->dom()->querySelector('#div1');

        $value = $element->getText();

        $this->assertEquals('bar', $value);
    }

    public function testGetAttribute(): void
    {
        $page = $this->openSitePage('domForm.html');

        $element = $page->dom()->querySelector('#div1');

        $value = $element->getAttribute('type');

        $this->assertEquals('foo', $value);
    }

    public function testSetAttribute(): void
    {
        $page = $this->openSitePage('domForm.html');

        $element = $page->dom()->querySelector('#div1');

        $element->setAttributeValue('type', 'hello');

        $value = $element->getAttribute('type');

        $this->assertEquals('hello', $value);
    }

    public function testUploadFile(): void
    {
        $page = $this->openSitePage('domForm.html');
        $file = self::sitePath('domForm.html');

        $element = $page->dom()->querySelector('#myfile');
        $element->sendFile($file);

        $value = $page
            ->evaluate('document.querySelector("#myfile").value;')
            ->getReturnValue();

        // check if the file was selected
        $this->assertStringEndsWith(\basename($file), $value);
    }

    public function testUploadFiles(): void
    {
        $page = $this->openSitePage('domForm.html');
        $files = [
            self::sitePath('domForm.html'),
            self::sitePath('form.html'),
        ];

        $element = $page->dom()->querySelector('#myfiles');
        $element->sendFiles($files);

        $value1 = $page->evaluate('document.querySelector("#myfiles").files[0].name;')->getReturnValue();
        $value2 = $page->evaluate('document.querySelector("#myfiles").files[1].name;')->getReturnValue();

        // check if the files were selected
        $this->assertStringEndsWith(\basename($files[0]), $value1);
        $this->assertStringEndsWith(\basename($files[1]), $value2);
    }
}
