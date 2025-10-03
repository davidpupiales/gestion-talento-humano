#!/usr/bin/env bash
if [ -z "$1" ]; then
  echo "Usage: $0 <commit-msg-file>"
  exit 2
fi

MSG_FILE="$1"
MSG=$(cat "$MSG_FILE")

if ! echo "$MSG" | grep -Eq "^(feat|fix|docs|chore|refactor|perf)(\([a-z0-9\-_/]+\))?: .{1,72}"; then
  echo "Invalid commit message format. Expected '<type>(scope): short summary'"
  exit 1
fi

echo "Commit message format: OK"
exit 0
