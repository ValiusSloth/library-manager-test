<?php

namespace App\Tests\E2E;

use App\Tests\Support\ScreenshotTrait;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\TestCase;

abstract class AbstractE2ETestCase extends TestCase
{
    use ScreenshotTrait;
    
    protected static ?RemoteWebDriver $driver = null;
    protected static bool $loggedIn = false;
    protected static string $baseUrl = 'http://nginx';
    
    protected static string $defaultEmail = 'admin@admin.com';
    protected static string $defaultPassword = 'admin';
    
    public static function setUpBeforeClass(): void
    {
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability('goog:chromeOptions', [
            'args' => [
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--window-size=1280,1500'
            ]
        ]);
        
        try {
            self::$driver = RemoteWebDriver::create(
                'http://selenium:4444/wd/hub',
                $capabilities,
                60000,
                60000
            );
            
            self::$driver->manage()->timeouts()->implicitlyWait(10);
            self::$driver->manage()->timeouts()->pageLoadTimeout(30);
        } catch (\Exception $e) {
            self::markTestSkipped('Could not connect to Selenium: ' . $e->getMessage());
        }
    }
    
    protected function setUp(): void
    {
        if (self::$driver === null) {
            $this->markTestSkipped('WebDriver is not available');
            return;
        }
        
        $this->configureScreenshotDir($this->getName());
        
        if (!self::$loggedIn) {
            $this->login();
            self::$loggedIn = true;
        }
    }
    
    protected function login(
        ?string $email = null, 
        ?string $password = null
    ): void {
        try {
            self::$driver->get(self::$baseUrl . '/login');
            $this->takeScreenshot(self::$driver, 'login_page');
            
            self::$driver->findElement(WebDriverBy::id('inputEmail'))
                ->sendKeys($email ?? self::$defaultEmail);
                
            self::$driver->findElement(WebDriverBy::id('inputPassword'))
                ->sendKeys($password ?? self::$defaultPassword);
            
            self::$driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
            
            sleep(2);
            
            $this->takeScreenshot(self::$driver, 'after_login');
            
        } catch (\Exception $e) {
            $this->logError(self::$driver, 'login', $e);
            $this->fail('Login failed: ' . $e->getMessage());
        }
    }
    
    protected function navigateTo(string $path): void
    {
        self::$driver->get(self::$baseUrl . $path);
    }
    
    protected function waitForElement(
        $selector, 
        $timeout = 10
    ) {
        return self::$driver->wait($timeout)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($selector)
        );
    }
    
    protected function findSearchInput(): ?object
    {
        $inputs = self::$driver->findElements(WebDriverBy::tagName('input'));
        $searchInput = null;
        
        foreach ($inputs as $input) {
            try {
                $type = $input->getAttribute('type');
                $placeholder = $input->getAttribute('placeholder');
                $id = $input->getAttribute('id');
                
                if ($type === 'text' && (
                    (strpos(strtolower($placeholder), 'search') !== false) || 
                    (strpos(strtolower($id), 'search') !== false) ||
                    (strpos(strtolower($input->getAttribute('class')), 'search') !== false)
                )) {
                    $searchInput = $input;
                    break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $searchInput;
    }
    
    public static function tearDownAfterClass(): void
    {
        if (self::$driver !== null) {
            self::$driver->quit();
        }
    }
}