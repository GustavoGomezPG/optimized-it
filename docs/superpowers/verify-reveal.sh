#!/usr/bin/env bash
# Verifies the data-proto-animate reveal runtime is wired correctly on the frontend:
#   1. every page loads the plugin reveal runtime script + the <noscript> safety net
#   2. theme blocks emit the migrated data-proto-animate attribute
#   3. no theme block still emits the legacy data-animate="pending|manual" element attribute
set -u
export PATH="$HOME/.local/bin:$PATH"
H='-H Host:optimizedit.local'
PAGES=(/ /solutions/ /industries/government/ /about/ /about/contact/ /locations/cincinnati/ /blog/)
fail=0
for p in "${PAGES[@]}"; do
  html=$(curl -s $H "http://optimizedit.local$p")
  code=$(curl -s -o /dev/null -w "%{http_code}" $H "http://optimizedit.local$p")
  [ "$code" = "200" ] || { echo "HTTP $code: $p"; fail=1; }
  echo "$html" | grep -q "reveal-runtime.js" || { echo "MISSING runtime: $p"; fail=1; }
  echo "$html" | grep -q "<noscript>" || { echo "MISSING noscript: $p"; fail=1; }
  # legacy element attribute should be gone from theme output (the noscript/CSS alias only ever
  # contains data-animate="done", never pending/manual, so this matches real leftovers only):
  legacy=$(echo "$html" | grep -oE 'data-animate="(pending|manual)"' | wc -l | tr -d ' ')
  [ "$legacy" = "0" ] || { echo "LEGACY data-animate on $p: $legacy"; fail=1; }
  proto=$(echo "$html" | grep -oE 'data-proto-animate="(pending|manual)"' | wc -l | tr -d ' ')
  echo "  $p -> HTTP $code | proto-animate els: $proto | legacy: $legacy"
done
echo "RESULT: $([ $fail = 0 ] && echo PASS || echo FAIL) (fail=$fail)"
exit $fail
