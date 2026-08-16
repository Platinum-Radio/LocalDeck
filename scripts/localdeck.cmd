@echo off
setlocal
set "LOCALDECK_EXE=%~dp0LocalDeck.exe"
if not exist "%LOCALDECK_EXE%" set "LOCALDECK_EXE=%~dp0..\LocalDeck.exe"
if not exist "%LOCALDECK_EXE%" (
  echo LocalDeck.exe is niet naast de CLI-launcher gevonden.
  exit /b 2
)
"%LOCALDECK_EXE%" %*
exit /b %errorlevel%
