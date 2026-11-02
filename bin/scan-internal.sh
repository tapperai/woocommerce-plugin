#!/usr/bin/env bash
# Guard for a PUBLIC repo: no internal host, project id, private address or secret-looking value in any tracked file.
# Run by the release workflow (and by hand). Allowed tapper.ai hosts are listed below; anything else under tapper.ai fails.
set -euo pipefail
cd "$(dirname "$0")/.."

ALLOWED_HOSTS='^(tapper\.ai|www\.tapper\.ai|app\.tapper\.ai|monitor\.tapper\.ai|support\.tapper\.ai|docs\.tapper\.ai)$'
BANNED='itlinks-to|tapper-ai-dev|tapper-88735|trycloudflare|\.internal\b|\.svc\.cluster|loop-prod|forge-prod|roster@|@agents\.tapper\.ai|sk_live_|BEGIN [A-Z ]*PRIVATE KEY|AKIA[0-9A-Z]{16}|(^|[^0-9])(10|192\.168|172\.(1[6-9]|2[0-9]|3[01]))\.[0-9]+\.[0-9]+(\.[0-9]+)?([^0-9]|$)'

files=$(git ls-files | grep -v -E '^(LICENSE|bin/scan-internal\.sh)$' || true)
fail=0

hosts=$(echo "$files" | xargs grep -h -o -i -E '[a-z0-9][a-z0-9.-]*\.tapper\.ai' 2>/dev/null | tr 'A-Z' 'a-z' | sort -u || true)
for h in $hosts; do
  if ! echo "$h" | grep -q -E "$ALLOWED_HOSTS"; then echo "internal host in the repo: $h" >&2; fail=1; fi
done

if echo "$files" | xargs grep -n -E "$BANNED" 2>/dev/null; then
  echo "internal or secret-looking value found (lines above)" >&2; fail=1
fi

[ "$fail" = 0 ] && echo "scan-internal: clean ($(echo "$files" | wc -l | tr -d ' ') files)"
exit "$fail"
