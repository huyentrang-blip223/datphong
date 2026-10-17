param([string]$OutputDir = (Join-Path $PSScriptRoot '..\backups'))
python (Join-Path $PSScriptRoot 'db-maintenance.py') backup --output-dir $OutputDir
exit $LASTEXITCODE
