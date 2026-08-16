@echo off
setlocal
"%~dp0node\node.exe" "%~dp0node\node_modules\pnpm\bin\pnpm.mjs" %*
exit /b %ERRORLEVEL%
