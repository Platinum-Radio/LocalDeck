!include "LogicLib.nsh"

!macro customUnInstall
  DetailPrint "LocalDeck Windows-services veilig stoppen en loskoppelen..."
  nsExec::ExecToLog '"$SYSDIR\WindowsPowerShell\v1.0\powershell.exe" -NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File "$INSTDIR\resources\installer\manage-services.ps1" -RuntimeDir "$APPDATA\LocalDeck\runtime" -Action Uninstall -Service all -AppExe "$INSTDIR\LocalDeck.exe"'
  MessageBox MB_YESNO|MB_ICONEXCLAMATION|MB_DEFBUTTON2 "Wilt u ook alle LocalDeck-gegevens definitief verwijderen?$\r$\n$\r$\nDit verwijdert websites, databases, back-ups, lokale mail, SSL-certificaten en instellingen.$\r$\n$\r$\nKies Nee om alle gegevens veilig te bewaren." IDYES removeLocalDeckData IDNO keepLocalDeckData

  removeLocalDeckData:
    DetailPrint "LocalDeck-gegevens verwijderen na uitdrukkelijke toestemming..."
    RMDir /r "$APPDATA\LocalDeck"
    Goto localDeckDataChoiceDone

  keepLocalDeckData:
    DetailPrint "LocalDeck-websites, databases, back-ups en instellingen blijven bewaard in $APPDATA\LocalDeck."

  localDeckDataChoiceDone:
!macroend
