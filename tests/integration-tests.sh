#!/usr/bin/env bash
# Integration smoke tests — checks HTTP status codes against the running app.
# Usage: ./tests/integration-tests.sh [BASE_URL]
# Default base URL: http://localhost

set -euo pipefail

BASE="${1:-http://localhost}"
PASS=0
FAIL=0
COOKIEJAR=$(mktemp)
trap 'rm -f "$COOKIEJAR"' EXIT

check() {
    local label="$1"
    local expected="$2"
    local actual="$3"

    if [ "$actual" = "$expected" ]; then
        echo "  PASS  $label (HTTP $actual)"
        PASS=$((PASS + 1))
    else
        echo "  FAIL  $label — expected HTTP $expected, got $actual"
        FAIL=$((FAIL + 1))
    fi
}

echo "Running integration tests against $BASE"
echo

# Public routes (no redirect following)
check "GET /login returns 200" "200" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/login")"

check "GET / shows login page" "200" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/")"

# Authenticated-only routes redirect to login when unauthenticated
check "GET /gallery redirects to login" "302" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/gallery")"

check "GET /order redirects to login" "302" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/order")"

check "GET /dashboard redirects to login" "302" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/dashboard")"

# Admin routes redirect to login when unauthenticated
check "GET /admin/users redirects" "302" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/admin/users")"

check "GET /admin/orders redirects" "302" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/admin/orders")"

check "GET /admin/products redirects" "302" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/admin/products")"

# 404 for unknown routes
check "GET /nonexistent returns 404" "404" \
    "$(curl -s -o /dev/null -w "%{http_code}" "$BASE/nonexistent-page-xyz")"

# POST /login with bad credentials — must use a real session + CSRF token.
# Step 1: GET login page to establish session and capture the CSRF token.
LOGIN_HTML=$(curl -s -c "$COOKIEJAR" "$BASE/login")
CSRF=$(echo "$LOGIN_HTML" | grep 'name="csrf_token"' | sed 's/.*value="\([^"]*\)".*/\1/')

# Step 2: POST with the session cookie and CSRF token.
check "POST /login bad creds returns 200" "200" \
    "$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIEJAR" \
        --request POST \
        --data "email=nobody%40example.com&password=wrongpass&csrf_token=${CSRF}" \
        "$BASE/login")"

echo
echo "Results: $PASS passed, $FAIL failed"
[ "$FAIL" -eq 0 ]
