param([Parameter(Mandatory=$true)][string]$BackupFile)
python (Join-Path $PSScriptRoot 'db-maintenance.py') restore-test --backup-file $BackupFile
exit $LASTEXITCODE
