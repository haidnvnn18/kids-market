#!/bin/bash
# ==============================================================================
# KidsMarket — Automated API Test Script
# ==============================================================================
# Sử dụng: bash tests/api_test.sh [BASE_URL]
#
# Ví dụ:
#   bash tests/api_test.sh                                   # mặc định dùng demo server
#   bash tests/api_test.sh http://localhost:8000              # local
#   bash tests/api_test.sh https://demo.nongsanphutho.com.vn # production demo
#
# Script sẽ:
#   1. Chạy tất cả test cases tự động (curl)
#   2. Kiểm tra HTTP status code + JSON response body
#   3. In kết quả PASS/FAIL cho từng case
#   4. Tạo report tổng hợp cuối cùng
#   5. Ghi log chi tiết vào file tests/api_test_report.log
# ==============================================================================

set -euo pipefail

# ─── Configuration ────────────────────────────────────────────────────────────
BASE_URL="${1:-https://demo.nongsanphutho.com.vn}"
API_URL="${BASE_URL}/api"

# Test credentials (from database/seeders/UserSeeder.php)
BUYER_USERNAME="nguyenvana"
BUYER_PASSWORD="password123"
SELLER_USERNAME="manual@gmail.com"
SELLER_PASSWORD="123@123aB"

# Timestamps for unique test data
TIMESTAMP=$(date +%s)
TEST_BUYER_USERNAME="testbuyer_${TIMESTAMP}"
TEST_SELLER_USERNAME="testseller_${TIMESTAMP}"
TEST_SELLER_EMAIL="testseller_${TIMESTAMP}@test.com"

# ─── Counters & Colors ───────────────────────────────────────────────────────
PASS=0
FAIL=0
SKIP=0
TOTAL=0
CONCERNS=()

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

LOG_FILE="tests/api_test_report.log"
mkdir -p tests
: > "$LOG_FILE"

# ─── Helper Functions ─────────────────────────────────────────────────────────

log() {
    echo "$@" | tee -a "$LOG_FILE"
}

log_raw() {
    echo "$@" >> "$LOG_FILE"
}

# assert_test <test_id> <test_name> <http_status> <expected_status> <response_body> <json_check_field> <json_check_value>
# json_check_field: jq expression (e.g., ".success", ".data.id")
# json_check_value: expected value (e.g., "true", "not_null", "gt:0", "contains:xxx")
assert_test() {
    local test_id="$1"
    local test_name="$2"
    local http_status="$3"
    local expected_status="$4"
    local response_body="$5"
    local json_field="${6:-}"
    local json_expected="${7:-}"

    TOTAL=$((TOTAL + 1))
    local result="PASS"
    local detail=""
    local concern=""

    # Check HTTP status
    if [[ "$http_status" != "$expected_status" ]]; then
        result="FAIL"
        detail="HTTP ${http_status} (expected ${expected_status})"
    fi

    # Check JSON field if specified
    if [[ -n "$json_field" && "$result" == "PASS" ]]; then
        local actual_value
        actual_value=$(echo "$response_body" | jq -r "$json_field" 2>/dev/null || echo "PARSE_ERROR")

        if [[ "$actual_value" == "PARSE_ERROR" ]]; then
            result="FAIL"
            detail="JSON parse error"
        elif [[ "$json_expected" == "not_null" ]]; then
            if [[ "$actual_value" == "null" || -z "$actual_value" ]]; then
                result="FAIL"
                detail="${json_field} is null/empty"
            fi
        elif [[ "$json_expected" == "not_empty_array" ]]; then
            local arr_len
            arr_len=$(echo "$response_body" | jq "$json_field | length" 2>/dev/null || echo "0")
            if [[ "$arr_len" -eq 0 ]]; then
                result="FAIL"
                detail="${json_field} is empty array"
            fi
        elif [[ "$json_expected" == gt:* ]]; then
            local threshold="${json_expected#gt:}"
            if ! [[ "$actual_value" =~ ^[0-9]+$ ]] || [[ "$actual_value" -le "$threshold" ]]; then
                result="FAIL"
                detail="${json_field} = ${actual_value} (expected > ${threshold})"
            fi
        elif [[ "$json_expected" == "gte:0" ]]; then
            if ! [[ "$actual_value" =~ ^[0-9]+$ ]]; then
                result="FAIL"
                detail="${json_field} = ${actual_value} (expected >= 0)"
            fi
        elif [[ "$json_expected" == contains:* ]]; then
            local substr="${json_expected#contains:}"
            if [[ "$actual_value" != *"$substr"* ]]; then
                result="FAIL"
                detail="${json_field} does not contain '${substr}'"
            fi
        elif [[ "$json_expected" == "is_bool" ]]; then
            if [[ "$actual_value" != "true" && "$actual_value" != "false" ]]; then
                result="FAIL"
                detail="${json_field} = ${actual_value} (expected boolean)"
            fi
        else
            if [[ "$actual_value" != "$json_expected" ]]; then
                result="FAIL"
                detail="${json_field} = '${actual_value}' (expected '${json_expected}')"
            fi
        fi
    fi

    # Print result
    if [[ "$result" == "PASS" ]]; then
        PASS=$((PASS + 1))
        log -e "  ${GREEN}✓ PASS${NC} ${test_id}: ${test_name}"
    else
        FAIL=$((FAIL + 1))
        log -e "  ${RED}✗ FAIL${NC} ${test_id}: ${test_name} — ${detail}"
        CONCERNS+=("${test_id}: ${test_name} — ${detail}")
    fi

    # Log full response to file
    log_raw "--- ${test_id}: ${test_name} ---"
    log_raw "HTTP Status: ${http_status}"
    log_raw "Response: ${response_body}"
    log_raw ""
}

# Perform a curl request and return status|body
do_request() {
    local method="$1"
    local url="$2"
    local data="${3:-}"
    local auth_header="${4:-}"
    local content_type="${5:-application/json}"

    local curl_args=(-s -w "\n%{http_code}" -X "$method" "$url" -H "Content-Type: ${content_type}")

    if [[ -n "$auth_header" ]]; then
        curl_args+=(-H "Authorization: Bearer ${auth_header}")
    fi

    if [[ -n "$data" && "$method" != "GET" && "$method" != "DELETE" ]]; then
        curl_args+=(-d "$data")
    fi

    local response
    response=$(curl "${curl_args[@]}" 2>/dev/null || echo -e "\n000")

    local body http_code
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    echo "${http_code}|${body}"
}

# ─── Print header ─────────────────────────────────────────────────────────────
log ""
log -e "${BOLD}${CYAN}╔══════════════════════════════════════════════════════════════╗${NC}"
log -e "${BOLD}${CYAN}║        KidsMarket — Automated API Test Suite                ║${NC}"
log -e "${BOLD}${CYAN}╠══════════════════════════════════════════════════════════════╣${NC}"
log -e "${BOLD}${CYAN}║${NC} Target: ${BASE_URL}"
log -e "${BOLD}${CYAN}║${NC} Time:   $(date '+%Y-%m-%d %H:%M:%S')"
log -e "${BOLD}${CYAN}╚══════════════════════════════════════════════════════════════╝${NC}"
log ""

# ==============================================================================
# SECTION 1: BUYER AUTHENTICATION
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 1. BUYER AUTHENTICATION ━━━${NC}"

# TC-AUTH-01: Register with invalid data (short password)
result=$(do_request "POST" "${API_URL}/register" \
    '{"type":"basic","fullname":"Test","username":"ab","password":"12","phone":"000","dob":"2000-01-01"}')
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-01" "Register with invalid data → validation error" "$http_code" "422" "$body" ".success" "false"

# TC-AUTH-02: Register with valid data
result=$(do_request "POST" "${API_URL}/register" \
    "{\"type\":\"basic\",\"fullname\":\"Test User ${TIMESTAMP}\",\"username\":\"${TEST_BUYER_USERNAME}\",\"password\":\"KidsMarketPass@2026\",\"phone\":\"090${TIMESTAMP:0:7}\",\"dob\":\"1995-01-15\"}")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-02" "Register valid buyer account" "$http_code" "201" "$body" ".success" "true"

# TC-AUTH-03: Register duplicate username
result=$(do_request "POST" "${API_URL}/register" \
    "{\"type\":\"basic\",\"fullname\":\"Dup User\",\"username\":\"${TEST_BUYER_USERNAME}\",\"password\":\"KidsMarketPass@2026\",\"phone\":\"0901111111\",\"dob\":\"1995-01-15\"}")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-03" "Register duplicate username → error" "$http_code" "422" "$body" ".success" "false"

# TC-AUTH-04: Login with wrong password
result=$(do_request "POST" "${API_URL}/login" \
    '{"type":"basic","username":"buyer1","password":"wrong_password"}')
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-04" "Login wrong password → 401" "$http_code" "401" "$body" ".success" "false"

# TC-AUTH-05: Login with valid credentials
result=$(do_request "POST" "${API_URL}/login" \
    "{\"type\":\"basic\",\"username\":\"${BUYER_USERNAME}\",\"password\":\"${BUYER_PASSWORD}\"}")
http_code="${result%%|*}"
body="${result#*|}"
BUYER_TOKEN=$(echo "$body" | jq -r '.accessToken // empty')
assert_test "TC-AUTH-05" "Login valid buyer → get token" "$http_code" "200" "$body" ".success" "true"

if [[ -z "$BUYER_TOKEN" ]]; then
    log -e "  ${RED}⚠ CRITICAL: No buyer token obtained. Subsequent buyer tests will fail.${NC}"
fi

# TC-AUTH-06: Check username exists
result=$(do_request "GET" "${API_URL}/check-username?username=${BUYER_USERNAME}")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-06" "Check existing username → exists=true" "$http_code" "200" "$body" ".exists" "true"

# TC-AUTH-07: Check username available
result=$(do_request "GET" "${API_URL}/check-username?username=nonexistent_user_xyz_999")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-07" "Check available username → exists=false" "$http_code" "200" "$body" ".exists" "false"

# TC-AUTH-08: Access protected endpoint without token
result=$(do_request "GET" "${API_URL}/cart")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-AUTH-08" "Access protected API without token → 401" "$http_code" "401" "$body"

log ""

# ==============================================================================
# SECTION 2: PRODUCTS (PUBLIC)
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 2. PRODUCTS (PUBLIC) ━━━${NC}"

# TC-PROD-01: Get product list
result=$(do_request "GET" "${API_URL}/products?per_page=5")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-PROD-01" "Get product list" "$http_code" "200" "$body" ".success" "true"

# Extract first product ID for later use
FIRST_PRODUCT_ID=$(echo "$body" | jq -r '.data.data[0].id // empty')
FIRST_VARIANT_ID=$(echo "$body" | jq -r '.data.data[0].variants[0].id // empty')
FIRST_SUPPLIER_ID=$(echo "$body" | jq -r '.data.data[0].supplier_id // empty')

# TC-PROD-02: Get product categories
result=$(do_request "GET" "${API_URL}/products/categories")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-PROD-02" "Get product categories" "$http_code" "200" "$body" ".success" "true"
FIRST_CATEGORY_ID=$(echo "$body" | jq -r '.data[0].id // empty')

# TC-PROD-03: Search products autocomplete
result=$(do_request "GET" "${API_URL}/products/search?q=a")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-PROD-03" "Search autocomplete" "$http_code" "200" "$body" ".success" "true"

# TC-PROD-04: Filter by category
if [[ -n "$FIRST_CATEGORY_ID" ]]; then
    result=$(do_request "GET" "${API_URL}/products?category_id=${FIRST_CATEGORY_ID}&per_page=5")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-PROD-04" "Filter products by category_id=${FIRST_CATEGORY_ID}" "$http_code" "200" "$body" ".success" "true"
else
    TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
    log -e "  ${YELLOW}○ SKIP${NC} TC-PROD-04: No category found to test filter"
fi

# TC-PROD-05: Get product detail
if [[ -n "$FIRST_PRODUCT_ID" ]]; then
    result=$(do_request "GET" "${API_URL}/products/${FIRST_PRODUCT_ID}" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-PROD-05" "Get product detail (id=${FIRST_PRODUCT_ID})" "$http_code" "200" "$body" ".data.id" "$FIRST_PRODUCT_ID"
else
    TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
    log -e "  ${YELLOW}○ SKIP${NC} TC-PROD-05: No product found"
fi

# TC-PROD-06: Get non-existent product → 404
result=$(do_request "GET" "${API_URL}/products/999999")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-PROD-06" "Get non-existent product → 404" "$http_code" "404" "$body" ".success" "false"

# TC-PROD-07: Track product view (auth required)
if [[ -n "$FIRST_PRODUCT_ID" && -n "$BUYER_TOKEN" ]]; then
    result=$(do_request "POST" "${API_URL}/products/${FIRST_PRODUCT_ID}/track-view" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-PROD-07" "Track product view" "$http_code" "200" "$body" ".success" "true"
else
    TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
    log -e "  ${YELLOW}○ SKIP${NC} TC-PROD-07: Missing product or token"
fi

# TC-PROD-08: Toggle like (auth required)
if [[ -n "$FIRST_PRODUCT_ID" && -n "$BUYER_TOKEN" ]]; then
    result=$(do_request "POST" "${API_URL}/products/${FIRST_PRODUCT_ID}/like" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-PROD-08" "Toggle like product" "$http_code" "200" "$body" ".data.is_liked" "is_bool"
else
    TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
    log -e "  ${YELLOW}○ SKIP${NC} TC-PROD-08: Missing product or token"
fi

# TC-PROD-09: Home recommendations (optional auth)
result=$(do_request "GET" "${API_URL}/products/recommendations/home?per_page=5" "" "$BUYER_TOKEN")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-PROD-09" "Home recommendations (with auth)" "$http_code" "200" "$body" ".success" "true"

# TC-PROD-10: Home recommendations (no auth)
result=$(do_request "GET" "${API_URL}/products/recommendations/home?per_page=5")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-PROD-10" "Home recommendations (no auth)" "$http_code" "200" "$body" ".success" "true"

log ""

# ==============================================================================
# SECTION 3: SHOPS (PUBLIC)
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 3. SHOPS (PUBLIC) ━━━${NC}"

if [[ -n "$FIRST_SUPPLIER_ID" ]]; then
    # TC-SHOP-01: Get shop info
    result=$(do_request "GET" "${API_URL}/shops/${FIRST_SUPPLIER_ID}")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SHOP-01" "Get shop info (id=${FIRST_SUPPLIER_ID})" "$http_code" "200" "$body" ".success" "true"

    # TC-SHOP-02: Get shop products
    result=$(do_request "GET" "${API_URL}/shops/${FIRST_SUPPLIER_ID}/products")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SHOP-02" "Get shop products" "$http_code" "200" "$body" ".success" "true"
else
    TOTAL=$((TOTAL + 2)); SKIP=$((SKIP + 2))
    log -e "  ${YELLOW}○ SKIP${NC} TC-SHOP-01/02: No supplier found"
fi

log ""

# ==============================================================================
# SECTION 4: PROVINCES & DISTRICTS
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 4. PROVINCES & DISTRICTS ━━━${NC}"

# TC-GEO-01: Get provinces
result=$(do_request "GET" "${API_URL}/provinces")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-GEO-01" "Get provinces list" "$http_code" "200" "$body" ".success" "true"
FIRST_PROVINCE_ID=$(echo "$body" | jq -r '.data[0].id // empty')

# TC-GEO-02: Get province detail
if [[ -n "$FIRST_PROVINCE_ID" ]]; then
    result=$(do_request "GET" "${API_URL}/provinces/${FIRST_PROVINCE_ID}")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-GEO-02" "Get province detail (id=${FIRST_PROVINCE_ID})" "$http_code" "200" "$body" ".success" "true"

    # TC-GEO-03: Get districts by province
    result=$(do_request "GET" "${API_URL}/provinces/${FIRST_PROVINCE_ID}/districts")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-GEO-03" "Get districts by province" "$http_code" "200" "$body" ".success" "true"
    FIRST_DISTRICT_ID=$(echo "$body" | jq -r '.data[0].id // empty')
else
    TOTAL=$((TOTAL + 2)); SKIP=$((SKIP + 2))
    log -e "  ${YELLOW}○ SKIP${NC} TC-GEO-02/03: No province found"
fi

log ""

# ==============================================================================
# SECTION 5: SHIPPING ADDRESSES (AUTH REQUIRED)
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 5. SHIPPING ADDRESSES ━━━${NC}"

if [[ -n "$BUYER_TOKEN" ]]; then
    # TC-ADDR-01: Get addresses list
    result=$(do_request "GET" "${API_URL}/shipping-addresses" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-ADDR-01" "Get shipping addresses list" "$http_code" "200" "$body" ".success" "true"

    # TC-ADDR-02: Create new shipping address
    ADDR_PROVINCE="${FIRST_PROVINCE_ID:-1}"
    ADDR_DISTRICT="${FIRST_DISTRICT_ID:-1}"
    result=$(do_request "POST" "${API_URL}/shipping-addresses" \
        "{\"full_name\":\"Test User\",\"phone\":\"0901234567\",\"province_id\":${ADDR_PROVINCE},\"district_id\":${ADDR_DISTRICT},\"address_detail\":\"123 Test Street, Ward 1\",\"is_default\":false}" \
        "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-ADDR-02" "Create shipping address" "$http_code" "201" "$body" ".success" "true"
    NEW_ADDRESS_ID=$(echo "$body" | jq -r '.data.id // empty')

    # TC-ADDR-03: Update shipping address
    if [[ -n "$NEW_ADDRESS_ID" ]]; then
        result=$(do_request "PUT" "${API_URL}/shipping-addresses/${NEW_ADDRESS_ID}" \
            "{\"full_name\":\"Updated Name\",\"phone\":\"0909876543\",\"province_id\":${ADDR_PROVINCE},\"district_id\":${ADDR_DISTRICT},\"address_detail\":\"456 Updated St\",\"is_default\":false}" \
            "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ADDR-03" "Update shipping address" "$http_code" "200" "$body" ".success" "true"

        # TC-ADDR-04: Set as default
        result=$(do_request "PATCH" "${API_URL}/shipping-addresses/${NEW_ADDRESS_ID}/set-default" "" "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ADDR-04" "Set default address" "$http_code" "200" "$body" ".success" "true"

        # TC-ADDR-05: Delete address
        result=$(do_request "DELETE" "${API_URL}/shipping-addresses/${NEW_ADDRESS_ID}" "" "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ADDR-05" "Delete shipping address" "$http_code" "200" "$body" ".success" "true"
    else
        TOTAL=$((TOTAL + 3)); SKIP=$((SKIP + 3))
        log -e "  ${YELLOW}○ SKIP${NC} TC-ADDR-03/04/05: Failed to create address"
    fi
else
    TOTAL=$((TOTAL + 5)); SKIP=$((SKIP + 5))
    log -e "  ${YELLOW}○ SKIP${NC} TC-ADDR-*: No buyer token"
fi

log ""

# ==============================================================================
# SECTION 6: CART (AUTH REQUIRED)
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 6. CART ━━━${NC}"

if [[ -n "$BUYER_TOKEN" && -n "$FIRST_VARIANT_ID" ]]; then
    # TC-CART-01: Get cart
    result=$(do_request "GET" "${API_URL}/cart" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-CART-01" "Get cart" "$http_code" "200" "$body" ".success" "true"

    # TC-CART-02: Update cart (add items)
    result=$(do_request "POST" "${API_URL}/cart" \
        "{\"items\":[{\"product_variant_id\":${FIRST_VARIANT_ID},\"quantity\":2}]}" \
        "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-CART-02" "Update cart (add variant ${FIRST_VARIANT_ID})" "$http_code" "200" "$body" ".success" "true"

    # TC-CART-03: Verify cart has items
    result=$(do_request "GET" "${API_URL}/cart" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    CART_ITEM_COUNT=$(echo "$body" | jq '.data.items | length' 2>/dev/null || echo "0")
    assert_test "TC-CART-03" "Cart has items after update" "$http_code" "200" "$body" ".data.items | length" "gt:0"

    # Save cart item ID for order creation
    CART_ITEM_ID=$(echo "$body" | jq -r '.data.items[0].id // empty')
else
    TOTAL=$((TOTAL + 3)); SKIP=$((SKIP + 3))
    log -e "  ${YELLOW}○ SKIP${NC} TC-CART-*: No buyer token or variant"
fi

log ""

# ==============================================================================
# SECTION 7: ORDERS (AUTH REQUIRED)
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 7. ORDERS ━━━${NC}"

if [[ -n "$BUYER_TOKEN" ]]; then
    # First ensure we have a shipping address for orders
    result=$(do_request "GET" "${API_URL}/shipping-addresses" "" "$BUYER_TOKEN")
    SHIPPING_ADDR_ID=$(echo "${result#*|}" | jq -r '.data[0].id // empty')

    # If no address, create one
    if [[ -z "$SHIPPING_ADDR_ID" ]]; then
        ADDR_P="${FIRST_PROVINCE_ID:-1}"
        ADDR_D="${FIRST_DISTRICT_ID:-1}"
        result=$(do_request "POST" "${API_URL}/shipping-addresses" \
            "{\"full_name\":\"Order Test User\",\"phone\":\"0901234567\",\"province_id\":${ADDR_P},\"district_id\":${ADDR_D},\"address_detail\":\"Order Test Address\",\"is_default\":true}" \
            "$BUYER_TOKEN")
        SHIPPING_ADDR_ID=$(echo "${result#*|}" | jq -r '.data.id // empty')
    fi

    # TC-ORDER-01: Create order
    if [[ -n "$SHIPPING_ADDR_ID" && -n "$FIRST_VARIANT_ID" ]]; then
        ORDER_PAYLOAD="{\"shipping_address_id\":${SHIPPING_ADDR_ID},\"items\":[{\"product_variant_id\":${FIRST_VARIANT_ID},\"quantity\":1,\"is_wholesale\":false"
        if [[ -n "$CART_ITEM_ID" ]]; then
            ORDER_PAYLOAD="${ORDER_PAYLOAD},\"cart_item_id\":${CART_ITEM_ID}"
        fi
        ORDER_PAYLOAD="${ORDER_PAYLOAD}}]}"

        result=$(do_request "POST" "${API_URL}/orders" "$ORDER_PAYLOAD" "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ORDER-01" "Create order" "$http_code" "201" "$body" ".success" "true"
        NEW_ORDER_ID=$(echo "$body" | jq -r '.data.id // empty')
    else
        TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
        log -e "  ${YELLOW}○ SKIP${NC} TC-ORDER-01: Missing shipping address or variant"
    fi

    # TC-ORDER-02: Get orders list
    result=$(do_request "GET" "${API_URL}/orders" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-ORDER-02" "Get orders list" "$http_code" "200" "$body" ".success" "true"

    # TC-ORDER-03: Filter orders by status
    result=$(do_request "GET" "${API_URL}/orders?status=pending" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-ORDER-03" "Filter orders by status=pending" "$http_code" "200" "$body" ".success" "true"

    # TC-ORDER-04: Get order detail
    if [[ -n "${NEW_ORDER_ID:-}" ]]; then
        result=$(do_request "GET" "${API_URL}/orders/${NEW_ORDER_ID}" "" "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ORDER-04" "Get order detail (id=${NEW_ORDER_ID})" "$http_code" "200" "$body" ".data.status" "pending"
    else
        TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
        log -e "  ${YELLOW}○ SKIP${NC} TC-ORDER-04: No order created"
    fi

    # TC-ORDER-05: Cancel order
    if [[ -n "${NEW_ORDER_ID:-}" ]]; then
        result=$(do_request "PATCH" "${API_URL}/orders/${NEW_ORDER_ID}/cancel" "" "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ORDER-05" "Cancel order" "$http_code" "200" "$body" ".data.status" "cancelled"
    else
        TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
        log -e "  ${YELLOW}○ SKIP${NC} TC-ORDER-05: No order to cancel"
    fi

    # TC-ORDER-06: Cancel already cancelled order → error
    if [[ -n "${NEW_ORDER_ID:-}" ]]; then
        result=$(do_request "PATCH" "${API_URL}/orders/${NEW_ORDER_ID}/cancel" "" "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-ORDER-06" "Cancel already cancelled order → 422" "$http_code" "422" "$body" ".success" "false"
    else
        TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
        log -e "  ${YELLOW}○ SKIP${NC} TC-ORDER-06: No order to test"
    fi

    # TC-ORDER-07: Get non-existent order → 404
    result=$(do_request "GET" "${API_URL}/orders/999999" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-ORDER-07" "Get non-existent order → 404" "$http_code" "404" "$body" ".success" "false"
else
    TOTAL=$((TOTAL + 7)); SKIP=$((SKIP + 7))
    log -e "  ${YELLOW}○ SKIP${NC} TC-ORDER-*: No buyer token"
fi

log ""

# ==============================================================================
# SECTION 8: REVIEWS
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 8. REVIEWS ━━━${NC}"

if [[ -n "$FIRST_PRODUCT_ID" ]]; then
    # TC-REVIEW-01: Get reviews for product (public)
    result=$(do_request "GET" "${API_URL}/products/${FIRST_PRODUCT_ID}/reviews")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-REVIEW-01" "Get product reviews (public)" "$http_code" "200" "$body" ".success" "true"

    # TC-REVIEW-02: Submit review (auth required)
    if [[ -n "$BUYER_TOKEN" ]]; then
        result=$(do_request "POST" "${API_URL}/products/${FIRST_PRODUCT_ID}/reviews" \
            "{\"rating\":5,\"comment\":\"Automated test review ${TIMESTAMP}\"}" \
            "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        # Could be 201 (new) or 409 (already reviewed)
        if [[ "$http_code" == "201" ]]; then
            assert_test "TC-REVIEW-02" "Submit review" "$http_code" "201" "$body" ".success" "true"
        elif [[ "$http_code" == "409" ]]; then
            assert_test "TC-REVIEW-02" "Submit review (already reviewed - expected)" "$http_code" "409" "$body" ".success" "false"
            log -e "    ${YELLOW}ℹ Note: User already reviewed this product — this is expected behavior${NC}"
        else
            assert_test "TC-REVIEW-02" "Submit review" "$http_code" "201" "$body" ".success" "true"
        fi
    fi

    # TC-REVIEW-03: Submit review without auth → 401
    result=$(do_request "POST" "${API_URL}/products/${FIRST_PRODUCT_ID}/reviews" \
        '{"rating":5,"comment":"No auth test"}')
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-REVIEW-03" "Submit review without auth → 401" "$http_code" "401" "$body"

    # TC-REVIEW-04: Submit review with invalid rating → 422
    if [[ -n "$BUYER_TOKEN" ]]; then
        result=$(do_request "POST" "${API_URL}/products/${FIRST_PRODUCT_ID}/reviews" \
            '{"rating":10,"comment":"Invalid rating"}' \
            "$BUYER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-REVIEW-04" "Submit review with invalid rating → 422" "$http_code" "422" "$body" ".success" "false"
    fi
else
    TOTAL=$((TOTAL + 4)); SKIP=$((SKIP + 4))
    log -e "  ${YELLOW}○ SKIP${NC} TC-REVIEW-*: No product found"
fi

log ""

# ==============================================================================
# SECTION 9: SELLER AUTHENTICATION
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 9. SELLER AUTHENTICATION ━━━${NC}"

# TC-SELLER-AUTH-01: Seller register
result=$(do_request "POST" "${API_URL}/seller/auth/register" \
    "{\"name\":\"Test Seller ${TIMESTAMP}\",\"username\":\"${TEST_SELLER_USERNAME}\",\"email\":\"${TEST_SELLER_EMAIL}\",\"phone\":\"088${TIMESTAMP:0:7}\",\"password\":\"Password123!\",\"password_confirmation\":\"Password123!\",\"brand_name\":\"Test Brand ${TIMESTAMP}\",\"country\":\"VN\",\"description\":\"Test shop description\"}")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-SELLER-AUTH-01" "Register seller account" "$http_code" "201" "$body" ".success" "true"

# TC-SELLER-AUTH-02: Seller login
result=$(do_request "POST" "${API_URL}/seller/auth/login" \
    "{\"username_or_email\":\"${SELLER_USERNAME}\",\"password\":\"${SELLER_PASSWORD}\"}")
http_code="${result%%|*}"
body="${result#*|}"
SELLER_TOKEN=$(echo "$body" | jq -r '.accessToken // empty')
assert_test "TC-SELLER-AUTH-02" "Login seller" "$http_code" "200" "$body" ".success" "true"

if [[ -z "$SELLER_TOKEN" ]]; then
    log -e "  ${RED}⚠ CRITICAL: No seller token. Subsequent seller tests will fail.${NC}"
fi

# TC-SELLER-AUTH-03: Login with wrong credentials
result=$(do_request "POST" "${API_URL}/seller/auth/login" \
    '{"username_or_email":"noseller@test.com","password":"wrong"}')
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-SELLER-AUTH-03" "Seller login wrong creds → 401" "$http_code" "401" "$body" ".success" "false"

# TC-SELLER-AUTH-04: Buyer token accessing seller endpoints → 403
if [[ -n "$BUYER_TOKEN" ]]; then
    result=$(do_request "GET" "${API_URL}/seller/dashboard" "" "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    # Should be 403 (buyer role, not seller)
    assert_test "TC-SELLER-AUTH-04" "Buyer token on seller endpoint → 403" "$http_code" "403" "$body"
fi

log ""

# ==============================================================================
# SECTION 10: SELLER API (AUTH REQUIRED)
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 10. SELLER API ━━━${NC}"

if [[ -n "$SELLER_TOKEN" ]]; then
    # TC-SELLER-01: Get dashboard stats
    result=$(do_request "GET" "${API_URL}/seller/dashboard" "" "$SELLER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SELLER-01" "Seller dashboard stats" "$http_code" "200" "$body" ".success" "true"

    # TC-SELLER-02: Get shop info
    result=$(do_request "GET" "${API_URL}/seller/shop" "" "$SELLER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SELLER-02" "Get seller shop info" "$http_code" "200" "$body" ".success" "true"

    # TC-SELLER-03: Get seller products
    result=$(do_request "GET" "${API_URL}/seller/products" "" "$SELLER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SELLER-03" "Get seller products list" "$http_code" "200" "$body" ".success" "true"
    SELLER_PRODUCT_ID=$(echo "$body" | jq -r '.data[0].id // empty')

    # TC-SELLER-04: Get seller product detail
    if [[ -n "$SELLER_PRODUCT_ID" ]]; then
        result=$(do_request "GET" "${API_URL}/seller/products/${SELLER_PRODUCT_ID}" "" "$SELLER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-SELLER-04" "Get seller product detail" "$http_code" "200" "$body" ".success" "true"
    else
        TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
        log -e "  ${YELLOW}○ SKIP${NC} TC-SELLER-04: No seller product"
    fi

    # TC-SELLER-05: Get seller orders
    result=$(do_request "GET" "${API_URL}/seller/orders" "" "$SELLER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SELLER-05" "Get seller orders list" "$http_code" "200" "$body" ".success" "true"
    SELLER_ORDER_ID=$(echo "$body" | jq -r '.data[0].id // empty')

    # TC-SELLER-06: Get seller order detail
    if [[ -n "$SELLER_ORDER_ID" ]]; then
        result=$(do_request "GET" "${API_URL}/seller/orders/${SELLER_ORDER_ID}" "" "$SELLER_TOKEN")
        http_code="${result%%|*}"
        body="${result#*|}"
        assert_test "TC-SELLER-06" "Get seller order detail" "$http_code" "200" "$body" ".success" "true"
    else
        TOTAL=$((TOTAL + 1)); SKIP=$((SKIP + 1))
        log -e "  ${YELLOW}○ SKIP${NC} TC-SELLER-06: No seller order"
    fi

    # TC-SELLER-07: Get seller reviews
    result=$(do_request "GET" "${API_URL}/seller/reviews" "" "$SELLER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SELLER-07" "Get seller reviews list" "$http_code" "200" "$body" ".success" "true"

    # TC-SELLER-08: Seller logout
    result=$(do_request "POST" "${API_URL}/seller/auth/logout" "" "$SELLER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    assert_test "TC-SELLER-08" "Seller logout" "$http_code" "200" "$body" ".success" "true"
else
    TOTAL=$((TOTAL + 8)); SKIP=$((SKIP + 8))
    log -e "  ${YELLOW}○ SKIP${NC} TC-SELLER-*: No seller token"
fi

log ""

# ==============================================================================
# SECTION 11: EDGE CASES & SECURITY
# ==============================================================================
log -e "${BOLD}${BLUE}━━━ 11. EDGE CASES & SECURITY ━━━${NC}"

# TC-EDGE-01: Invalid JWT token
result=$(do_request "GET" "${API_URL}/cart" "" "invalid.jwt.token.here")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-EDGE-01" "Invalid JWT token → 401" "$http_code" "401" "$body"

# TC-EDGE-02: Empty Authorization header
result=$(do_request "GET" "${API_URL}/orders" "" "")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-EDGE-02" "Empty auth header → 401" "$http_code" "401" "$body"

# TC-EDGE-03: SQL injection attempt in search
result=$(do_request "GET" "${API_URL}/products/search?q=%27%3B%20DROP%20TABLE%20products%3B%20--")
http_code="${result%%|*}"
body="${result#*|}"
assert_test "TC-EDGE-03" "SQL injection in search → safe response" "$http_code" "200" "$body" ".success" "true"

# TC-EDGE-04: XSS attempt in review comment
if [[ -n "$BUYER_TOKEN" && -n "$FIRST_PRODUCT_ID" ]]; then
    result=$(do_request "POST" "${API_URL}/products/${FIRST_PRODUCT_ID}/reviews" \
        '{"rating":3,"comment":"<script>alert(1)</script>"}' \
        "$BUYER_TOKEN")
    http_code="${result%%|*}"
    body="${result#*|}"
    # Should either create or reject (409 if already reviewed), but NOT crash
    if [[ "$http_code" == "201" || "$http_code" == "409" || "$http_code" == "422" ]]; then
        TOTAL=$((TOTAL + 1)); PASS=$((PASS + 1))
        log -e "  ${GREEN}✓ PASS${NC} TC-EDGE-04: XSS in comment → server handled safely (HTTP ${http_code})"
    else
        TOTAL=$((TOTAL + 1)); FAIL=$((FAIL + 1))
        log -e "  ${RED}✗ FAIL${NC} TC-EDGE-04: XSS in comment → unexpected HTTP ${http_code}"
        CONCERNS+=("TC-EDGE-04: XSS handling returned HTTP ${http_code}")
    fi
fi

# TC-EDGE-05: Very long input in search
LONG_STRING=$(printf 'A%.0s' {1..500})
result=$(do_request "GET" "${API_URL}/products/search?q=${LONG_STRING}")
http_code="${result%%|*}"
body="${result#*|}"
if [[ "$http_code" == "200" || "$http_code" == "414" || "$http_code" == "422" ]]; then
    TOTAL=$((TOTAL + 1)); PASS=$((PASS + 1))
    log -e "  ${GREEN}✓ PASS${NC} TC-EDGE-05: Long search query → handled safely (HTTP ${http_code})"
else
    TOTAL=$((TOTAL + 1)); FAIL=$((FAIL + 1))
    log -e "  ${RED}✗ FAIL${NC} TC-EDGE-05: Long search query → HTTP ${http_code}"
    CONCERNS+=("TC-EDGE-05: Long search query returned HTTP ${http_code}")
fi

log ""

# ==============================================================================
# REPORT SUMMARY
# ==============================================================================
log -e "${BOLD}${CYAN}╔══════════════════════════════════════════════════════════════╗${NC}"
log -e "${BOLD}${CYAN}║                    TEST REPORT SUMMARY                      ║${NC}"
log -e "${BOLD}${CYAN}╠══════════════════════════════════════════════════════════════╣${NC}"
log -e "${BOLD}${CYAN}║${NC} Total Tests:  ${TOTAL}"
log -e "${BOLD}${CYAN}║${NC} ${GREEN}Passed:       ${PASS}${NC}"
log -e "${BOLD}${CYAN}║${NC} ${RED}Failed:       ${FAIL}${NC}"
log -e "${BOLD}${CYAN}║${NC} ${YELLOW}Skipped:      ${SKIP}${NC}"

if [[ $TOTAL -gt 0 ]]; then
    PASS_RATE=$((PASS * 100 / TOTAL))
    log -e "${BOLD}${CYAN}║${NC} Pass Rate:    ${PASS_RATE}%"
fi

log -e "${BOLD}${CYAN}╠══════════════════════════════════════════════════════════════╣${NC}"
log -e "${BOLD}${CYAN}║${NC} Target:       ${BASE_URL}"
log -e "${BOLD}${CYAN}║${NC} Time:         $(date '+%Y-%m-%d %H:%M:%S')"
log -e "${BOLD}${CYAN}║${NC} Log File:     ${LOG_FILE}"
log -e "${BOLD}${CYAN}╚══════════════════════════════════════════════════════════════╝${NC}"

if [[ ${#CONCERNS[@]} -gt 0 ]]; then
    log ""
    log -e "${BOLD}${RED}━━━ FAILED TESTS (Requires Manual Review) ━━━${NC}"
    for concern in "${CONCERNS[@]}"; do
        log -e "  ${RED}•${NC} ${concern}"
    done
fi

log ""
if [[ $FAIL -eq 0 && $SKIP -eq 0 ]]; then
    log -e "${GREEN}${BOLD}🎉 ALL TESTS PASSED!${NC}"
elif [[ $FAIL -eq 0 ]]; then
    log -e "${GREEN}${BOLD}✅ All executed tests passed (${SKIP} skipped).${NC}"
else
    log -e "${RED}${BOLD}⚠ ${FAIL} test(s) failed — see details above for manual review.${NC}"
fi

log ""
exit $FAIL
