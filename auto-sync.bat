@echo off
setlocal enabledelayedexpansion

echo ========================================================
echo   Auto-Sync Ecommerce Folders to GitHub
echo ========================================================
echo.

cd /d "%~dp0"

:: 1. Check & Commit admin_web changes
git status --porcelain admin_web > nul 2>&1
for /f %%i in ('git status --porcelain admin_web') do (
    echo [Admin Web] Changes detected. Committing...
    git add admin_web/
    git commit -m "Admin Web updates: %date% %time%"
    goto :check_customer
)
:check_customer

:: 2. Check & Commit customer_app changes
git status --porcelain customer_app > nul 2>&1
for /f %%i in ('git status --porcelain customer_app') do (
    echo [Customer App] Changes detected. Committing...
    git add customer_app/
    git commit -m "Customer App updates: %date% %time%"
    goto :check_delivery
)
:check_delivery

:: 3. Check & Commit delivery_boy_app changes
git status --porcelain delivery_boy_app > nul 2>&1
for /f %%i in ('git status --porcelain delivery_boy_app') do (
    echo [Delivery Boy App] Changes detected. Committing...
    git add delivery_boy_app/
    git commit -m "Delivery Boy App updates: %date% %time%"
    goto :check_root
)
:check_root

:: 4. Any root files (.gitignore, scripts, etc.)
git status --porcelain .gitignore > nul 2>&1
for /f %%i in ('git status --porcelain .gitignore') do (
    echo [Root Config] Changes detected. Committing...
    git add .gitignore
    git commit -m "Config updates: %date% %time%"
    goto :push_now
)

:push_now
echo.
echo Pushing all commits to GitHub...
git push origin main

echo.
echo ========================================================
echo   All folders synchronized successfully to GitHub!
echo ========================================================
pause
