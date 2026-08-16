Option Explicit
Dim shell, runtimeDir, scriptPath, command
If WScript.Arguments.Count = 0 Then WScript.Quit 2
runtimeDir = WScript.Arguments(0)
scriptPath = runtimeDir & "\automation\run-backups.ps1"
Set shell = CreateObject("WScript.Shell")
command = "powershell.exe -NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File """ & scriptPath & """ -RuntimeDir """ & runtimeDir & """"
shell.Run command, 0, True
