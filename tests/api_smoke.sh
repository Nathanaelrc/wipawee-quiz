#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://localhost:8091}"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

COOKIE_JAR="$TMP_DIR/cookies.txt"
INDEX_HTML="$TMP_DIR/index.html"

expect_status() {
  local got="$1"
  local expected="$2"
  local label="$3"
  if [ "$got" != "$expected" ]; then
    echo "[FAIL] $label -> expected $expected, got $got"
    exit 1
  fi
  echo "[OK] $label -> $got"
}

status_get="$(curl -sS -o /dev/null -w "%{http_code}" "$BASE_URL/api.php")"
expect_status "$status_get" "405" "GET api.php"

curl -sS -c "$COOKIE_JAR" "$BASE_URL/" > "$INDEX_HTML"
csrf_token="$(sed -n 's/.*csrfToken: "\([^"]*\)".*/\1/p' "$INDEX_HTML" | head -n1)"

if [ -z "$csrf_token" ]; then
  echo "[FAIL] Could not extract CSRF token from index"
  exit 1
fi

echo "[OK] CSRF token extracted"

status_bad_origin="$(curl -sS -o /dev/null -w "%{http_code}" \
  -b "$COOKIE_JAR" \
  -H "Origin: https://evil.example" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: $csrf_token" \
  -X POST "$BASE_URL/api.php" \
  -d '{"score":8,"total":8}')"
expect_status "$status_bad_origin" "403" "POST wrong origin"

status_bad_csrf="$(curl -sS -o /dev/null -w "%{http_code}" \
  -b "$COOKIE_JAR" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: invalid" \
  -X POST "$BASE_URL/api.php" \
  -d '{"score":8,"total":8}')"
expect_status "$status_bad_csrf" "403" "POST invalid CSRF"

status_valid="$(curl -sS -o "$TMP_DIR/valid.json" -w "%{http_code}" \
  -b "$COOKIE_JAR" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: $csrf_token" \
  -X POST "$BASE_URL/api.php" \
  -d '{"score":8,"total":8}')"
expect_status "$status_valid" "200" "POST valid payload"

if ! grep -q '"success":true' "$TMP_DIR/valid.json"; then
  echo "[FAIL] Valid response did not include success=true"
  cat "$TMP_DIR/valid.json"
  exit 1
fi

echo "[OK] API happy-path response"

echo "Smoke tests completed successfully."
