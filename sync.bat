@echo off
:: Metaserve - Git Auto-Sync & Deploy Tool
echo ==============================================
echo   Metaserve - Git Backup ^& Server Deploy Tool
echo ==============================================
echo.

:: Check if git is installed
where git >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Git is not installed or not in PATH. Please install Git first.
    pause
    exit /b
)

:: Check if repository directory is initialized
if not exist ".git" (
    echo [INFO] Git repository not initialized. Initializing now...
    git init
    git branch -M main
    echo.
)

:: Check remote origin configuration
git remote get-url origin >nul 2>nul
if %errorlevel% neq 0 (
    echo [INFO] GitHub Remote URL is not configured. Setting to metaserve repo...
    git remote add origin https://github.com/kiwixcompo/metaserve.git
    echo.
)

:: Backup and push changes
echo [INFO] Adding all changes to commit stage...
git add .

echo [INFO] Creating backup commit...
git commit -m "Auto backup & deploy: %date% %time%"

echo [INFO] Pushing changes to main branch on GitHub...
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo ==============================================
    echo [SUCCESS] Your work has been pushed to GitHub successfully!
    echo ==============================================
    echo.
    echo [INFO] Triggering Live Server Auto-Deployment on Metaserve.com.ng...
    curl -s "https://metaserve.com.ng/deploy.php?token=metaserve_deploy_2026"
    echo.
    echo [SUCCESS] Deployment trigger completed. Check the server to ensure changes reflect.
) else (
    echo.
    echo [WARNING] Push failed. Make sure you have internet access and authorization to the repo.
)
echo.
pause
