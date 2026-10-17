param([string]$Php = 'C:\xampp\php\php.exe')
$ErrorActionPreference = 'Stop'
$backendDirectory = (Resolve-Path (Join-Path $PSScriptRoot '..\backend')).Path
if (-not (Test-Path -LiteralPath $Php)) { throw 'PHP executable not found.' }
$action = New-ScheduledTaskAction -Execute $Php -Argument 'artisan schedule:run' -WorkingDirectory $backendDirectory
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 5)
$identity = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
Register-ScheduledTask -TaskName 'DT07-Homestay-Scheduler' -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description 'Run the DT07 Laravel scheduler every minute while the user is logged in.' -Force | Select-Object TaskName,State
Start-ScheduledTask -TaskName 'DT07-Homestay-Scheduler'
