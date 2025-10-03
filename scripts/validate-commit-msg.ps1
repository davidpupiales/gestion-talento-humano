param([string]$MsgFile)
if (-not $MsgFile) { Write-Error "Usage: validate-commit-msg.ps1 <commit-msg-file>"; exit 2 }
$msg = Get-Content -Raw -Path $MsgFile
$pattern = '^(feat|fix|docs|chore|refactor|perf)(\([a-z0-9\-_/]+\))?: .{1,72}'
if ($msg -notmatch $pattern) {
  Write-Error "Invalid commit message format. Expected '<type>(scope): short summary'"
  exit 1
}
Write-Host "Commit message format: OK"
exit 0
