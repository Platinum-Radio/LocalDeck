Option Explicit

Dim shell, files, folder, page, edge, address
Set shell = CreateObject("WScript.Shell")
Set files = CreateObject("Scripting.FileSystemObject")

folder = files.GetParentFolderName(WScript.ScriptFullName)
page = files.BuildPath(folder, "index.html")
edge = shell.ExpandEnvironmentStrings("%ProgramFiles(x86)%") & "\Microsoft\Edge\Application\msedge.exe"
address = "file:///" & Replace(page, "\", "/")

If files.FileExists(edge) Then
  shell.Run Chr(34) & edge & Chr(34) & " --app=" & Chr(34) & address & Chr(34) & " --start-maximized", 0, False
Else
  shell.Run Chr(34) & page & Chr(34), 1, False
End If
