@echo off
echo ===================================
echo Stripe PHP SDK Installation
echo ===================================
echo.

REM Check if composer is installed
where composer >nul 2>nul
if %errorlevel% equ 0 (
    echo Composer found! Installing Stripe PHP SDK...
    echo.
    composer require stripe/stripe-php
    echo.
    echo Installation complete!
    echo.
) else (
    echo Composer not found. Trying XAMPP's PHP Composer...
    echo.
    if exist "C:\xampp\php\php.exe" (
        cd /d "%~dp0"
        C:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        C:\xampp\php\php.exe composer-setup.php
        C:\xampp\php\php.exe -r "unlink('composer-setup.php');"
        C:\xampp\php\php.exe composer.phar require stripe/stripe-php
        echo.
        echo Installation complete!
    ) else (
        echo.
        echo ERROR: Composer not found and XAMPP PHP not detected.
        echo.
        echo Please install Composer first:
        echo 1. Download from: https://getcomposer.org/Composer-Setup.exe
        echo 2. Run the installer
        echo 3. Restart this script
        echo.
        echo OR manually download Stripe PHP from:
        echo https://github.com/stripe/stripe-php/releases
    )
)

echo.
echo Press any key to exit...
pause >nul