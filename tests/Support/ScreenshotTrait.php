<?php

namespace App\Tests\Support;

use Facebook\WebDriver\Remote\RemoteWebDriver;

trait ScreenshotTrait
{
    protected string $screenshotDir = 'var/screenshots';
    protected string $testName;
    
    protected function configureScreenshotDir(?string $testName = null): void
    {
        $this->testName = $testName ?: (new \ReflectionClass($this))->getShortName();
        
        $directory = $this->getScreenshotPath();
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }
    
    protected function getScreenshotPath(): string
    {
        return $this->screenshotDir . '/' . $this->testName;
    }
    
    protected function takeScreenshot(
        RemoteWebDriver $driver, 
        string $name, 
        bool $saveHTML = false
    ): string {
        $filename = $this->getScreenshotPath() . '/' . $name . '.png';
        $driver->takeScreenshot($filename);
        
        if ($saveHTML) {
            $htmlFilename = $this->getScreenshotPath() . '/' . $name . '.html';
            file_put_contents($htmlFilename, $driver->getPageSource());
        }
        
        return $filename;
    }
    
    protected function logError(
        RemoteWebDriver $driver, 
        string $name, 
        \Exception $exception
    ): void {
        $this->takeScreenshot($driver, $name . '_error', true);
        
        $errorFilename = $this->getScreenshotPath() . '/' . $name . '_error.txt';
        file_put_contents($errorFilename, $exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
    }
}