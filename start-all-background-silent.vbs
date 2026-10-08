Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
ScriptDir = fso.GetParentFolderName(WScript.ScriptFullName)

WshShell.CurrentDirectory = ScriptDir
WshShell.Run "wscript """ & ScriptDir & "\start-reverb-silent.vbs""", 0, False
WshShell.Run "wscript """ & ScriptDir & "\start-scheduler-silent.vbs""", 0, False
WshShell.Run "wscript """ & ScriptDir & "\start-queue-silent.vbs""", 0, False
