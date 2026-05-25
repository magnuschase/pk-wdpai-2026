#!/usr/bin/env bash
# Integration smoke tests — checks HTTP status codes against the running app.
# Usage: ./tests/integration-tests.sh [BASE_URL]
# Default base URL: http://localhost

set -euo pipefail

BASE="${1:-http://localhost}"
PASS=0
FAIL=0

check() {
    local label="$1"
    local expected="$2"
    local url="$3"
    shift 3
    local actual
    actual=$(curl -s -o /dev/null -w "%{http_code}" "$url" "$@")

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

# Public routes (curl does not follow redirects by default)
check "GET /login returns 200"              "200" "$BASE/login"
check "GET / shows login page"              "200" "$BASE/"

# Authenticated-only routes — must redirect to /login (302) when unauthenticated
check "GET /gallery redirects to login"     "302" "$BASE/gallery"
check "GET /order redirects to login"       "302" "$BASE/order"
check "GET /dashboard redirects to login"   "302" "$BASE/dashboard"

# Admin routes redirect to login when unauthenticated
check "GET /admin/users redirects"          "302" "$BASE/admin/users"
check "GET /admin/orders redirects"         "302" "$BASE/admin/orders"
check "GET /admin/products redirects"       "302" "$BASE/admin/products"

# 404 for unknown routes
check "GET /nonexistent returns 404"        "404" "$BASE/nonexistent-page-xyz"

# POST /login with bad credentials stays on login page (200, not redirect)
check "POST /login bad creds returns 200"   "200" "$BASE/login" \
    --request POST --data "email=nobody@example.com&password=wrongpass"

echo
echo "Results: $PASS passed, $FAIL failed"
[ "$FAIL" -eq 0 ]
