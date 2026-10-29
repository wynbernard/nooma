<?php
// =====================================================
// STOREHUB POS API EXTRACTOR  (FINAL)
// File: extraction.php
//
// Date logic:
//   - The date (YYYY-MM-DD) and time (HH:MM:SS) are read directly from the
//     transactionTime text, e.g. "2026-10-27T16:48:39.243Z" -> 2026-10-27 16:48:39
//   - NO timezone conversion is applied. Every transaction that has the same
//     date in transactionTime is displayed, filtered and exported under that date.
//   - The API is queried with a 1-day buffer on each side, then the strict
//     date filter keeps only transactions whose transactionTime date is inside
//     the selected range. This avoids losing transactions near midnight when
//     StoreHub applies from/to in a different timezone.
// =====================================================

date_default_timezone_set('Asia/Manila'); // only used for the default "today" value

// ---------- API SETTINGS ----------
// Tip: move these to environment variables or a file outside the web root.
$endpoint        = 'https://api.storehubhq.com/transactions';
$productEndpoint = 'https://api.storehubhq.com/products';
$apiUsername     = getenv('STOREHUB_USER') ?: 'noomakitchen';
$apiPassword     = getenv('STOREHUB_PASS') ?: '6ba682dd17084c7b94a116cce39c72f2';

// Optional: StoreHub product IDs to force bar / kitchen classification.
$BAR_PRODUCT_IDS     = [];
$KITCHEN_PRODUCT_IDS = [];

// Lunch is before this time (HH:MM:SS), Dinner is this time onwards.
$SHIFT_CUTOFF = '09:00:00';

// Recognized card / electronic payment methods
$cardNames = [
    'Visa',
    'Mastercard',
    'American Express',
    'JCB',
    'Diners Club',
    'GCash',
    'Other Credit Card'
];

// =====================================================
// HELPER FUNCTIONS
// =====================================================

function numberValue($value)
{
    return is_numeric($value) ? (float) $value : 0.0;
}

function peso($amount)
{
    return '₱' . number_format((float) $amount, 2);
}

// Date (YYYY-MM-DD) taken directly from the transactionTime text. No timezone shifting.
function extractDateFromTransactionTime($transactionTime)
{
    if (empty($transactionTime)) {
        return '';
    }

    if (preg_match('/^(\d{4}-\d{2}-\d{2})/', (string) $transactionTime, $m)) {
        return $m[1];
    }

    return substr((string) $transactionTime, 0, 10);
}

// Time (HH:MM:SS) taken directly from the transactionTime text.
function extractTimeFromTransactionTime($transactionTime)
{
    if (empty($transactionTime)) {
        return '';
    }

    if (preg_match('/T(\d{2}:\d{2}:\d{2})/', (string) $transactionTime, $m)) {
        return $m[1];
    }

    if (preg_match('/(\d{2}:\d{2}:\d{2})/', (string) $transactionTime, $m)) {
        return $m[1];
    }

    return '';
}

// "2026-10-27T16:48:39.243Z" -> "2026-10-27 16:48:39"
function formatTransactionDateTime($transactionTime)
{
    if (empty($transactionTime)) {
        return '';
    }

    if (preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})/', (string) $transactionTime, $m)) {
        return $m[1] . ' ' . $m[2];
    }

    return (string) $transactionTime;
}

function classifyMealPeriod($transactionTime, $cutoffTime)
{
    $timeStr = extractTimeFromTransactionTime($transactionTime);

    if ($timeStr === '') {
        return 'Lunch';
    }

    return ($timeStr < $cutoffTime) ? 'Lunch' : 'Dinner';
}

// Cache so each product is fetched from the API only once per request
$productCache = [];

function fetchProductDetails($productId, $productEndpoint, $username, $password, &$cache)
{
    if (array_key_exists($productId, $cache)) {
        return $cache[$productId];
    }

    $ch = curl_init($productEndpoint . '/' . rawurlencode($productId));

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => $username . ':' . $password,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => ['Accept: application/json']
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        $cache[$productId] = null;
        return null;
    }

    $json = json_decode($response, true);
    $cache[$productId] = is_array($json) ? $json : null;

    return $cache[$productId];
}

// Read the category name from a StoreHub product response (string, or object with a name)
function getProductCategoryName($product)
{
    if (!is_array($product)) {
        return '';
    }

    foreach (['category', 'categoryName', 'productCategory'] as $key) {
        if (!isset($product[$key])) {
            continue;
        }

        $value = $product[$key];

        if (is_array($value)) {
            $value = $value['name'] ?? $value['categoryName'] ?? '';
        }

        $value = strtolower(trim((string) $value));

        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

function createEmptyTotals($cardNames)
{
    return [
        'count'         => 0,
        'cash'          => 0.0,
        'serviceCharge' => 0.0,
        'barSale'       => 0.0,
        'kitchenSale'   => 0.0,
        'creditCard'    => 0.0,
        'unmappedSale'  => 0.0,
        'cardTypes'     => array_fill_keys($cardNames, 0.0)
    ];
}

function accumulateAmounts(&$totals, $amounts, $cardNames)
{
    $totals['count']++;

    foreach (['cash', 'serviceCharge', 'barSale', 'kitchenSale', 'creditCard', 'unmappedSale'] as $key) {
        $totals[$key] += $amounts[$key];
    }

    foreach ($cardNames as $cardName) {
        $totals['cardTypes'][$cardName] += $amounts['cardTypes'][$cardName];
    }
}

function fetchTransactions($endpoint, $username, $password, $from, $to)
{
    $url = $endpoint . '?' . http_build_query([
        'from' => $from,
        'to'   => $to
    ]);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => $username . ':' . $password,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => ['Accept: application/json']
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        throw new Exception('Connection failed: ' . $curlError);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception(
            'StoreHub API returned HTTP ' . $httpCode .
            '. Check your API credentials and permissions.'
        );
    }

    $json = json_decode($response, true);

    if (!is_array($json)) {
        throw new Exception('The API response is not valid JSON.');
    }

    if (isset($json['transactions']) && is_array($json['transactions'])) {
        return $json['transactions'];
    }

    if (isset($json['data']['transactions']) && is_array($json['data']['transactions'])) {
        return $json['data']['transactions'];
    }

    if (isset($json['data']) && is_array($json['data'])) {
        return $json['data'];
    }

    if (isset($json['results']) && is_array($json['results'])) {
        return $json['results'];
    }

    if (isset($json[0]) && is_array($json[0])) {
        return $json;
    }

    // Valid JSON that is an empty list
    if (empty($json)) {
        return [];
    }

    throw new Exception('Cannot find the transactions array in the API response.');
}

// Identify card / electronic payment types, including GCash
function identifyCardType($method)
{
    $method = strtolower(trim($method));

    if (
        strpos($method, 'gcash') !== false ||
        strpos($method, 'g-cash') !== false ||
        strpos($method, 'g cash') !== false
    ) {
        return 'GCash';
    }

    if (strpos($method, 'visa') !== false) {
        return 'Visa';
    }

    if (strpos($method, 'mastercard') !== false || strpos($method, 'master card') !== false) {
        return 'Mastercard';
    }

    if (strpos($method, 'amex') !== false || strpos($method, 'american express') !== false) {
        return 'American Express';
    }

    if (strpos($method, 'jcb') !== false) {
        return 'JCB';
    }

    if (strpos($method, 'diners') !== false) {
        return 'Diners Club';
    }

    if (
        strpos($method, 'credit card') !== false ||
        strpos($method, 'creditcard') !== false ||
        strpos($method, 'card payment') !== false ||
        strpos($method, 'debit') !== false
    ) {
        return 'Other Credit Card';
    }

    return '';
}

function getTransactionAmounts(
    $transaction,
    $barProductIds,
    $kitchenProductIds,
    $cardNames,
    $productEndpoint,
    $apiUsername,
    $apiPassword,
    &$productCache
) {
    $amounts = [
        'cash'          => 0.0,
        'serviceCharge' => numberValue($transaction['serviceCharge'] ?? 0),
        'barSale'       => 0.0,
        'kitchenSale'   => 0.0,
        'creditCard'    => 0.0,
        'unmappedSale'  => 0.0,
        'cardTypes'     => array_fill_keys($cardNames, 0.0)
    ];

    // ---------- PAYMENT TOTALS ----------
    $payments = $transaction['payments'] ?? [];

    if (is_array($payments)) {
        foreach ($payments as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            $method           = trim((string) ($payment['paymentMethod'] ?? ''));
            $normalizedMethod = strtolower($method);
            $amount           = numberValue($payment['amount'] ?? 0);

            // Card / e-wallet first (GCash, Visa, Mastercard, ...)
            $cardType = identifyCardType($method);

            if ($cardType !== '') {
                $amounts['creditCard'] += $amount;
                $amounts['cardTypes'][$cardType] += $amount;
                continue;
            }

            // Physical cash
            if (strpos($normalizedMethod, 'cash') !== false) {
                $amounts['cash'] += $amount;
            }
        }
    }

    // ---------- BAR / KITCHEN ITEM TOTALS ----------
    $items = $transaction['items'] ?? [];

    if (is_array($items)) {
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $itemType = strtolower(trim((string) ($item['itemType'] ?? '')));

            // Prevent double-counting service charge items
            if ($itemType === 'servicecharge') {
                continue;
            }

            $productId = (string) ($item['productId'] ?? '');

            $category = strtolower(trim((string) (
                $item['category'] ??
                $item['productCategory'] ??
                $item['productName'] ??
                $item['itemName'] ??
                $item['name'] ??
                ''
            )));

            // BASIS FOR BAR / KITCHEN SALE:
            // 1. Total of the subtotal and deductedTax (multiplied by quantity if per-unit, or line subTotal + deductedTax)
            // 2. Minus 12% of total: total - (total * 0.12)
            $quantity    = numberValue($item['quantity'] ?? 1);
            if ($quantity <= 0) {
                $quantity = 1;
            }
            $subTotal    = numberValue($item['subTotal'] ?? 0);
            $deductedTax = numberValue($item['deductedTax'] ?? 0);
            $tax         = numberValue($item['tax'] ?? 0);
            $lineTotal   = numberValue($item['total'] ?? 0);

            if ($deductedTax > 0) {
                // For discounted/exempt items: subTotal + deductedTax (+ tax if partial exemption)
                $grossTotal = $subTotal + $deductedTax + $tax;
            } elseif ($lineTotal > 0) {
                $grossTotal = $lineTotal;
            } elseif ($subTotal > 0) {
                $grossTotal = $subTotal + $tax;
            } else {
                $grossTotal = numberValue($item['unitPrice'] ?? $item['price'] ?? 0) * $quantity;
            }

            $itemTotal = $grossTotal - ($grossTotal * 0.12);

            // Predefined product IDs first
            if (in_array($productId, $barProductIds, true)) {
                $amounts['barSale'] += $itemTotal;
                continue;
            }

            if (in_array($productId, $kitchenProductIds, true)) {
                $amounts['kitchenSale'] += $itemTotal;
                continue;
            }

            // Ask the Product API only when the item itself does not reveal bar/kitchen
            $apiCategory = null;

            if (
                $productId !== '' &&
                strpos($category, 'bar') === false &&
                strpos($category, 'kitchen') === false
            ) {
                $productDetails = fetchProductDetails(
                    $productId,
                    $productEndpoint,
                    $apiUsername,
                    $apiPassword,
                    $productCache
                );

                $apiName = getProductCategoryName($productDetails);

                if ($apiName !== '') {
                    $apiCategory = $apiName;
                }
            }

            $effectiveCategory = $apiCategory !== null ? $apiCategory : $category;

            if (strpos($effectiveCategory, 'bar') !== false) {
                $amounts['barSale'] += $itemTotal;
            } elseif (strpos($effectiveCategory, 'kitchen') !== false) {
                $amounts['kitchenSale'] += $itemTotal;
            } else {
                $amounts['unmappedSale'] += $itemTotal;
            }
        }
    }

    return $amounts;
}

// =====================================================
// DATE RANGE AND EXTRACTION
// =====================================================

$today = date('Y-m-d');
$from  = $_POST['from'] ?? $_GET['from'] ?? $today;
$to    = $_POST['to']   ?? $_GET['to']   ?? $today;

$error     = '';
$rows      = [];
$extracted = false;
$isExport  = isset($_GET['export']);

$lunchTotals  = createEmptyTotals($cardNames);
$dinnerTotals = createEmptyTotals($cardNames);
$grandTotals  = createEmptyTotals($cardNames);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $isExport) {
    $validFrom = DateTime::createFromFormat('!Y-m-d', $from);
    $validTo   = DateTime::createFromFormat('!Y-m-d', $to);

    if (
        !$validFrom ||
        !$validTo ||
        $validFrom->format('Y-m-d') !== $from ||
        $validTo->format('Y-m-d') !== $to
    ) {
        $error = 'Please select valid dates.';
    } elseif ($from > $to) {
        $error = 'The start date cannot be later than the end date.';
    } elseif ($apiUsername === '' || $apiPassword === '') {
        $error = 'Configure your StoreHub username and password in the PHP code.';
    } else {
        try {
            // 1-day buffer on each side so no transaction near midnight is missed.
            // The strict date filter below removes anything outside the selected range.
            $apiFrom = (clone $validFrom)->modify('-1 day')->format('Y-m-d');
            $apiTo   = (clone $validTo)->modify('+1 day')->format('Y-m-d');

            $transactions = fetchTransactions(
                $endpoint,
                $apiUsername,
                $apiPassword,
                $apiFrom,
                $apiTo
            );

            foreach ($transactions as $transaction) {
                if (!is_array($transaction)) {
                    continue;
                }

                // Skip cancelled transactions
                $cancelled = $transaction['isCancelled'] ?? false;

                if (
                    $cancelled === true ||
                    $cancelled === 1 ||
                    $cancelled === '1' ||
                    strtolower((string) $cancelled) === 'true'
                ) {
                    continue;
                }

                $rawTime = $transaction['transactionTime'] ?? '';

                // STRICT date basis: the date written in transactionTime
                $txDate = extractDateFromTransactionTime($rawTime);

                if ($txDate === '' || $txDate < $from || $txDate > $to) {
                    continue;
                }

                $formattedDateTime = formatTransactionDateTime($rawTime);
                $period            = classifyMealPeriod($rawTime, $SHIFT_CUTOFF);

                $amounts = getTransactionAmounts(
                    $transaction,
                    $BAR_PRODUCT_IDS,
                    $KITCHEN_PRODUCT_IDS,
                    $cardNames,
                    $productEndpoint,
                    $apiUsername,
                    $apiPassword,
                    $productCache
                );

                if ($period === 'Dinner') {
                    accumulateAmounts($dinnerTotals, $amounts, $cardNames);
                } else {
                    accumulateAmounts($lunchTotals, $amounts, $cardNames);
                }
                accumulateAmounts($grandTotals, $amounts, $cardNames);

                $rows[] = [
                    'invoice'       => $transaction['invoiceNumber'] ?? '',
                    'date'          => $formattedDateTime,
                    'txDate'        => $txDate,
                    'period'        => $period,
                    'cash'          => $amounts['cash'],
                    'serviceCharge' => $amounts['serviceCharge'],
                    'barSale'       => $amounts['barSale'],
                    'kitchenSale'   => $amounts['kitchenSale'],
                    'creditCard'    => $amounts['creditCard'],
                    'unmappedSale'  => $amounts['unmappedSale'],
                    'cardTypes'     => $amounts['cardTypes']
                ];
            }

            // Sort by transaction date/time (oldest first)
            usort($rows, function ($a, $b) {
                return strcmp($a['date'], $b['date']);
            });

            $extracted = true;

            // ---------- EXCEL WORKBOOK DOWNLOAD ----------
            if ($isExport) {
                $xmlValue = function ($value) {
                    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $value);
                    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
                };
                $cell = function ($value = null, $style = 'Default', $type = null, $formula = null, $mergeAcross = null) use ($xmlValue) {
                    $attributes = ' ss:StyleID="' . $style . '"';

                    if ($formula !== null) {
                        $attributes .= ' ss:Formula="' . $xmlValue($formula) . '"';
                    }

                    if ($mergeAcross !== null) {
                        $attributes .= ' ss:MergeAcross="' . (int) $mergeAcross . '"';
                    }

                    if ($value === null) {
                        return '<Cell' . $attributes . '/>';
                    }

                    if ($type === null) {
                        $type = is_numeric($value) ? 'Number' : 'String';
                    }

                    return '<Cell' . $attributes . '><Data ss:Type="' . $type . '">' .
                        $xmlValue($value) . '</Data></Cell>';
                };
                $worksheetRow = function ($cells, $height = null) {
                    $attributes = $height === null ? '' : ' ss:Height="' . (float) $height . '"';
                    return '<Row' . $attributes . '>' . implode('', $cells) . '</Row>';
                };
                $numberCell = function ($value, $style = 'Amount') use ($cell) {
                    return $cell(number_format((float) $value, 2, '.', ''), $style, 'Number');
                };

                $posTotal = $grandTotals['cash'] + $grandTotals['creditCard'];
                $grossTotal = $posTotal;
                $dateLabel = strtoupper(
                    date('M j (l)', strtotime($from)) . ' TO ' . date('M j (l)', strtotime($to))
                );

                $summaryRows = [];
                $summaryRows[] = $worksheetRow([
                    $cell('TOTAL GROSS SALES:', 'Title'),
                    $cell($grossTotal, 'TotalAmount', 'Number', '=SUM(R3C2:R3C4)'),
                    $cell($dateLabel, 'DateBand', 'String', null, 5)
                ], 27);
                $summaryRows[] = $worksheetRow([
                    $cell('', 'Blank'),
                    $cell('POS', 'ColumnHeader'),
                    $cell('NON POS', 'ColumnHeader'),
                    $cell('GRAB SALES (NET)', 'ColumnHeader'),
                    $cell('OTHER PAYMENTS', 'ColumnHeader'),
                    $cell('TOTAL COLLECTION', 'ColumnHeader'),
                    $cell('FOR DEPOSIT', 'ColumnHeader'),
                    $cell('UNSETTLED', 'ColumnHeader')
                ], 30);
                $summaryRows[] = $worksheetRow([
                    $cell('', 'Blank'),
                    $cell($posTotal, 'TopAmount', 'Number', '=SUM(R6C2:R7C2)'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell($posTotal, 'CollectionAmount', 'Number', '=R3C2+R3C5'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ], 24);
                $summaryRows[] = $worksheetRow([$cell('', 'Blank')], 8);
                $summaryRows[] = $worksheetRow([
                    $cell('BREAKDOWN:', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section')
                ], 20);
                $summaryRows[] = $worksheetRow([
                    $cell('CASH SALES', 'ReportLabel'),
                    $numberCell($grandTotals['cash']),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $numberCell($grandTotals['cash'], 'CollectionAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);
                $summaryRows[] = $worksheetRow([
                    $cell('CARD / E-WALLET SALES', 'ReportLabel'),
                    $numberCell($grandTotals['creditCard']),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $numberCell($grandTotals['creditCard'], 'CollectionAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);
                $summaryRows[] = $worksheetRow([
                    $cell('SERVICE CHARGE (INFO ONLY)', 'ReportLabel'),
                    $numberCell($grandTotals['serviceCharge']),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);

                foreach ([
                    'DIRECT BT BPI NOOMA',
                    'DIRECT BT EWB NOOMA',
                    'GIFT CHECK PAYMENTS',
                    'CHEQUE PAYMENTS'
                ] as $label) {
                    $summaryRows[] = $worksheetRow([
                        $cell($label, 'ReportLabel'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount')
                    ]);
                }

                $summaryRows[] = $worksheetRow([$cell('', 'Blank')], 14);
                $summaryRows[] = $worksheetRow([
                    $cell('RECEIVABLES:', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section')
                ]);
                $summaryRows[] = $worksheetRow([
                    $cell('RECEIVABLES (MANUAL ENTRY)', 'ReportLabel'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);
                $summaryRows[] = $worksheetRow([
                    $cell('UNPAID (MANUAL ENTRY)', 'ReportLabel'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);
                $summaryRows[] = $worksheetRow([$cell('', 'Blank')], 14);
                $summaryRows[] = $worksheetRow([
                    $cell('C/O MARKETING:', 'Section'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);
                $summaryRows[] = $worksheetRow([
                    $cell('OTHER PAYMENTS:', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section'),
                    $cell('', 'Section')
                ]);

                foreach ([
                    'PAID ACCTS (CURRENT YEAR)',
                    'ADVANCE PAYMENTS',
                    'TIP COLLECTED',
                    'PAID ACCTS (PREV YEAR)'
                ] as $label) {
                    $summaryRows[] = $worksheetRow([
                        $cell($label, 'ReportLabel'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount'),
                        $cell(null, 'ManualAmount')
                    ]);
                }

                $summaryRows[] = $worksheetRow([$cell('', 'Blank')], 12);
                $summaryRows[] = $worksheetRow([
                    $cell('SHORT/OVER', 'Section'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount'),
                    $cell(null, 'ManualAmount')
                ]);
                $summaryRows[] = $worksheetRow([$cell('', 'Blank')], 14);
                $summaryRows[] = $worksheetRow([
                    $cell('TOTAL GROSS SALES:', 'TotalLabel'),
                    $cell($posTotal, 'TotalAmount', 'Number', '=R3C2'),
                    $cell(0, 'ManualAmount', 'Number', '=R3C3'),
                    $cell(0, 'ManualAmount', 'Number', '=R3C4'),
                    $cell('', 'Blank'),
                    $cell('', 'Blank'),
                    $cell('', 'Blank'),
                    $cell('', 'Blank')
                ], 25);

                $detailRows = [];
                $detailHeaders = [
                    'Invoice',
                    'Transaction Date',
                    'Shift',
                    'Cash',
                    'Service Charge',
                    'Bar Sale',
                    'Kitchen Sale',
                    'Total Card & GCash'
                ];
                foreach ($cardNames as $cardName) {
                    $detailHeaders[] = $cardName;
                }
                $detailHeaders[] = 'Unmapped Item Sales';
                $detailRows[] = $worksheetRow(array_map(function ($heading) use ($cell) {
                    return $cell($heading, 'ColumnHeader');
                }, $detailHeaders), 32);

                foreach ($rows as $row) {
                    $detailCells = [
                        $cell($row['invoice'], 'Detail'),
                        $cell($row['date'], 'Detail'),
                        $cell($row['period'], 'Detail'),
                        $numberCell($row['cash']),
                        $numberCell($row['serviceCharge']),
                        $numberCell($row['barSale']),
                        $numberCell($row['kitchenSale']),
                        $numberCell($row['creditCard'])
                    ];

                    foreach ($cardNames as $cardName) {
                        $detailCells[] = $numberCell($row['cardTypes'][$cardName] ?? 0.0);
                    }

                    $detailCells[] = $numberCell($row['unmappedSale']);
                    $detailRows[] = $worksheetRow($detailCells);
                }

                $detailRows[] = $worksheetRow(array_fill(0, count($detailHeaders), $cell('', 'Blank')), 10);
                foreach ([
                    ['LUNCH TOTAL', 'Before ' . $SHIFT_CUTOFF, $lunchTotals],
                    ['DINNER TOTAL', $SHIFT_CUTOFF . ' onwards', $dinnerTotals],
                    ['GRAND TOTAL', 'Full Date Range', $grandTotals]
                ] as $summary) {
                    $totals = $summary[2];
                    $summaryCells = [
                        $cell($summary[0], 'TotalLabel'),
                        $cell($summary[1], 'Detail'),
                        $cell($totals['count'] . ' transactions', 'Detail'),
                        $numberCell($totals['cash'], 'TotalAmount'),
                        $numberCell($totals['serviceCharge'], 'TotalAmount'),
                        $numberCell($totals['barSale'], 'TotalAmount'),
                        $numberCell($totals['kitchenSale'], 'TotalAmount'),
                        $numberCell($totals['creditCard'], 'TotalAmount')
                    ];

                    foreach ($cardNames as $cardName) {
                        $summaryCells[] = $numberCell($totals['cardTypes'][$cardName], 'TotalAmount');
                    }

                    $summaryCells[] = $numberCell($totals['unmappedSale'], 'TotalAmount');
                    $detailRows[] = $worksheetRow($summaryCells);
                }

                $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                    '<?mso-application progid="Excel.Sheet"?>' . "\n" .
                    '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ' .
                    'xmlns:o="urn:schemas-microsoft-com:office:office" ' .
                    'xmlns:x="urn:schemas-microsoft-com:office:excel" ' .
                    'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ' .
                    'xmlns:html="http://www.w3.org/TR/REC-html40">' .
                    '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' .
                    '<Title>StoreHub POS Sales Report</Title></DocumentProperties>' .
                    '<Styles>' .
                    '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/>' .
                    '<Font ss:FontName="Arial" ss:Size="10"/></Style>' .
                    '<Style ss:ID="Blank"><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#222222"/></Borders></Style>' .
                    '<Style ss:ID="Title"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="11" ss:Bold="1"/>' .
                    '<Interior ss:Color="#D9EAF7" ss:Pattern="Solid"/><Borders><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/></Borders></Style>' .
                    '<Style ss:ID="TotalAmount"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#C00000"/><NumberFormat ss:Format="#,##0.00;[Red]-#,##0.00;-"/><Interior ss:Color="#D9EAF7" ss:Pattern="Solid"/><Borders><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/></Borders></Style>' .
                    '<Style ss:ID="DateBand"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="11" ss:Bold="1"/><Interior ss:Color="#D9EAF7" ss:Pattern="Solid"/></Style>' .
                    '<Style ss:ID="ColumnHeader"><Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/><Font ss:FontName="Arial" ss:Size="9" ss:Bold="1"/><Interior ss:Color="#D9EAF7" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#222222"/></Borders></Style>' .
                    '<Style ss:ID="TopAmount"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#C00000"/><NumberFormat ss:Format="#,##0.00;[Red]-#,##0.00;-"/></Style>' .
                    '<Style ss:ID="CollectionAmount"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#0563C1"/><NumberFormat ss:Format="#,##0.00;[Red]-#,##0.00;-"/><Interior ss:Color="#DFF0D8" ss:Pattern="Solid"/><Borders><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/></Borders></Style>' .
                    '<Style ss:ID="Section"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9" ss:Bold="1" ss:Color="#FF0000"/></Style>' .
                    '<Style ss:ID="ReportLabel"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9" ss:Color="#C00000"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#222222"/></Borders></Style>' .
                    '<Style ss:ID="Amount"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9" ss:Color="#C00000"/><NumberFormat ss:Format="#,##0.00;[Red]-#,##0.00;-"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#222222"/></Borders></Style>' .
                    '<Style ss:ID="ManualAmount"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><NumberFormat ss:Format="#,##0.00;[Red]-#,##0.00;-"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#222222"/></Borders></Style>' .
                    '<Style ss:ID="TotalLabel"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#C00000"/><Borders><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#222222"/></Borders></Style>' .
                    '<Style ss:ID="Detail"><Alignment ss:Vertical="Center"/><Font ss:FontName="Arial" ss:Size="9"/></Style>' .
                    '</Styles>' .
                    '<Worksheet ss:Name="Sales Summary"><Table>' .
                    '<Column ss:Width="210"/><Column ss:Width="100"/><Column ss:Width="100"/><Column ss:Width="112"/>' .
                    '<Column ss:Width="112"/><Column ss:Width="112"/><Column ss:Width="100"/><Column ss:Width="105"/>' .
                    implode('', $summaryRows) .
                    '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>2</SplitHorizontal><TopRowBottomPane>2</TopRowBottomPane><ActivePane>2</ActivePane></WorksheetOptions></Worksheet>' .
                    '<Worksheet ss:Name="Transaction Details"><Table>' .
                    '<Column ss:Width="110"/><Column ss:Width="145"/><Column ss:Width="75"/>' .
                    str_repeat('<Column ss:Width="95"/>', count($detailHeaders) - 3) .
                    implode('', $detailRows) .
                    '</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>1</SplitHorizontal><TopRowBottomPane>1</TopRowBottomPane><ActivePane>2</ActivePane></WorksheetOptions></Worksheet>' .
                    '</Workbook>';

                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header(
                    'Content-Disposition: attachment; filename="storehub_' .
                    $from . '_to_' . $to . '.xls"'
                );
                header('Cache-Control: max-age=0');
                echo $xml;
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Shift summary cards (Lunch / Dinner / Grand Total) used by the HTML below
$summaryCards = [
    [
        'badge'     => '☀️ Lunch Shift',
        'subtitle'  => $lunchTotals['count'] . ' orders (&lt; 9:00 AM)',
        'border'    => 'border-amber-200',
        'divider'   => 'border-amber-100',
        'badgeCss'  => 'bg-amber-100 text-amber-800',
        'cashCss'   => 'text-emerald-600',
        'cardCss'   => 'text-blue-600',
        'totals'    => $lunchTotals
    ],
    [
        'badge'     => '🌙 Dinner Shift',
        'subtitle'  => $dinnerTotals['count'] . ' orders (&ge; 9:00 AM)',
        'border'    => 'border-indigo-200',
        'divider'   => 'border-indigo-100',
        'badgeCss'  => 'bg-indigo-100 text-indigo-800',
        'cashCss'   => 'text-emerald-600',
        'cardCss'   => 'text-blue-600',
        'totals'    => $dinnerTotals
    ],
    [
        'badge'     => '📊 Full Date Range Total',
        'subtitle'  => $grandTotals['count'] . ' orders total',
        'border'    => 'border-slate-300',
        'divider'   => 'border-slate-200',
        'badgeCss'  => 'bg-slate-200 text-slate-800',
        'cashCss'   => 'text-emerald-700',
        'cardCss'   => 'text-blue-700',
        'totals'    => $grandTotals
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoreHub POS Extractor - Lunch & Dinner Breakdown</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="mx-auto max-w-7xl px-4 py-8">

    <header class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">StoreHub POS Extractor</h1>
        <p class="mt-2 text-slate-600">
            Extract sales, service charges, card payments, and GCash with <strong>Lunch</strong> (before 9:00 AM) and <strong>Dinner</strong> (9:00 AM onwards) separation based on the date and time in each transaction.
        </p>
    </header>

    <!-- DATE RANGE FILTER -->
    <section class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
        <form method="POST" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="from" class="mb-2 block text-sm font-semibold text-slate-700">From Date</label>
                <input
                    type="date"
                    id="from"
                    name="from"
                    value="<?= htmlspecialchars($from) ?>"
                    required
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                >
            </div>

            <div>
                <label for="to" class="mb-2 block text-sm font-semibold text-slate-700">To Date</label>
                <input
                    type="date"
                    id="to"
                    name="to"
                    value="<?= htmlspecialchars($to) ?>"
                    required
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                >
            </div>

            <button
                type="submit"
                class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition-colors"
            >
                Extract Data
            </button>
        </form>
        <p class="mt-3 text-xs text-slate-500">
            Filtering uses the date written in each transaction's <code>transactionTime</code> (no timezone conversion). Shift: <strong>☀️ Lunch</strong> is before <strong>9:00 AM (09:00:00)</strong>, and <strong>🌙 Dinner</strong> is <strong>9:00 AM onwards</strong>.
        </p>
    </section>

    <?php if ($error !== ''): ?>
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($extracted): ?>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Extraction Results</h2>
                <p class="text-sm text-slate-500">
                    Date Range: <strong class="text-slate-700"><?= htmlspecialchars($from) ?></strong> to <strong class="text-slate-700"><?= htmlspecialchars($to) ?></strong> &bull;
                    <span class="font-semibold text-slate-700"><?= count($rows) ?> total transactions</span>
                    (☀️ <?= $lunchTotals['count'] ?> Lunch &bull; 🌙 <?= $dinnerTotals['count'] ?> Dinner)
                </p>
            </div>

            <a
                href="?export=1&amp;from=<?= urlencode($from) ?>&amp;to=<?= urlencode($to) ?>"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 font-semibold text-white shadow hover:bg-emerald-700 transition-colors text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Excel Report
            </a>
        </div>

        <!-- SHIFT HIGHLIGHT CARDS (LUNCH, DINNER, GRAND TOTAL) -->
        <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-3">
            <?php foreach ($summaryCards as $card): $t = $card['totals']; ?>
                <div class="rounded-xl border <?= $card['border'] ?> bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b <?= $card['divider'] ?> pb-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full <?= $card['badgeCss'] ?> px-3 py-1 text-xs font-bold">
                            <?= $card['badge'] ?>
                        </span>
                        <span class="text-xs font-semibold text-slate-500"><?= $card['subtitle'] ?></span>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between items-baseline">
                            <span class="text-sm text-slate-600">💵 Cash Sales</span>
                            <span class="text-xl font-bold <?= $card['cashCss'] ?>"><?= peso($t['cash']) ?></span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-sm text-slate-600">🛎️ Service Charge</span>
                            <span class="text-xl font-bold text-slate-800"><?= peso($t['serviceCharge']) ?></span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-sm text-slate-600">💳 Card &amp; GCash</span>
                            <span class="text-xl font-bold <?= $card['cardCss'] ?>"><?= peso($t['creditCard']) ?></span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 flex justify-between text-xs text-slate-500">
                            <span>Kitchen: <strong><?= peso($t['kitchenSale']) ?></strong></span>
                            <span>Bar: <strong><?= peso($t['barSale']) ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- SIDE-BY-SIDE LUNCH VS DINNER COMPARISON TABLE -->
        <section class="mb-8 overflow-hidden rounded-xl bg-white shadow-sm border border-slate-200">
            <div class="border-b border-slate-200 bg-slate-50/75 p-5">
                <h3 class="text-lg font-bold text-slate-900">Shift Breakdown Comparison</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Direct comparison of Cash, Service Charge, and Card &amp; GCash amounts between Lunch and Dinner.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3 text-right text-amber-800">☀️ Lunch (&lt; 9:00 AM)</th>
                        <th class="px-5 py-3 text-right text-indigo-800">🌙 Dinner (&ge; 9:00 AM)</th>
                        <th class="px-5 py-3 text-right text-slate-800">📊 Combined Total</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">💵 Cash Sales</td>
                        <td class="px-5 py-3 text-right font-semibold text-emerald-600"><?= peso($lunchTotals['cash']) ?></td>
                        <td class="px-5 py-3 text-right font-semibold text-emerald-600"><?= peso($dinnerTotals['cash']) ?></td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-700"><?= peso($grandTotals['cash']) ?></td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">🛎️ Service Charge</td>
                        <td class="px-5 py-3 text-right font-medium text-slate-800"><?= peso($lunchTotals['serviceCharge']) ?></td>
                        <td class="px-5 py-3 text-right font-medium text-slate-800"><?= peso($dinnerTotals['serviceCharge']) ?></td>
                        <td class="px-5 py-3 text-right font-bold text-slate-900"><?= peso($grandTotals['serviceCharge']) ?></td>
                    </tr>
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-800">💳 Total Card &amp; GCash</td>
                        <td class="px-5 py-3 text-right font-semibold text-blue-600"><?= peso($lunchTotals['creditCard']) ?></td>
                        <td class="px-5 py-3 text-right font-semibold text-blue-600"><?= peso($dinnerTotals['creditCard']) ?></td>
                        <td class="px-5 py-3 text-right font-bold text-blue-700"><?= peso($grandTotals['creditCard']) ?></td>
                    </tr>
                    <tr class="hover:bg-slate-50 text-slate-600">
                        <td class="px-5 py-2.5">🍸 Bar Sales</td>
                        <td class="px-5 py-2.5 text-right"><?= peso($lunchTotals['barSale']) ?></td>
                        <td class="px-5 py-2.5 text-right"><?= peso($dinnerTotals['barSale']) ?></td>
                        <td class="px-5 py-2.5 text-right font-semibold text-slate-800"><?= peso($grandTotals['barSale']) ?></td>
                    </tr>
                    <tr class="hover:bg-slate-50 text-slate-600">
                        <td class="px-5 py-2.5">🍳 Kitchen Sales</td>
                        <td class="px-5 py-2.5 text-right"><?= peso($lunchTotals['kitchenSale']) ?></td>
                        <td class="px-5 py-2.5 text-right"><?= peso($dinnerTotals['kitchenSale']) ?></td>
                        <td class="px-5 py-2.5 text-right font-semibold text-slate-800"><?= peso($grandTotals['kitchenSale']) ?></td>
                    </tr>
                    <tr class="hover:bg-slate-50 text-slate-600">
                        <td class="px-5 py-2.5">📦 Unmapped Item Sales</td>
                        <td class="px-5 py-2.5 text-right"><?= peso($lunchTotals['unmappedSale']) ?></td>
                        <td class="px-5 py-2.5 text-right"><?= peso($dinnerTotals['unmappedSale']) ?></td>
                        <td class="px-5 py-2.5 text-right font-semibold text-slate-800"><?= peso($grandTotals['unmappedSale']) ?></td>
                    </tr>
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50 font-bold text-slate-800">
                    <tr>
                        <td class="px-5 py-3">Total Orders / Transactions</td>
                        <td class="px-5 py-3 text-right text-amber-800"><?= $lunchTotals['count'] ?> orders</td>
                        <td class="px-5 py-3 text-right text-indigo-800"><?= $dinnerTotals['count'] ?> orders</td>
                        <td class="px-5 py-3 text-right text-slate-900"><?= $grandTotals['count'] ?> orders</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- CARD & GCASH BREAKDOWN BY SHIFT -->
        <section class="mb-8 overflow-hidden rounded-xl bg-white shadow-sm border border-slate-200">
            <div class="border-b border-slate-200 p-5">
                <h3 class="text-lg font-bold text-slate-900">Card &amp; GCash Breakdown by Shift</h3>
                <p class="mt-1 text-sm text-slate-500">Payment method totals separated by Lunch and Dinner shifts.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Payment Method</th>
                        <th class="px-5 py-3 text-right text-amber-800">☀️ Lunch</th>
                        <th class="px-5 py-3 text-right text-indigo-800">🌙 Dinner</th>
                        <th class="px-5 py-3 text-right">Total Amount</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php foreach ($cardNames as $cardName): ?>
                        <tr class="hover:bg-slate-50 <?= ($cardName === 'GCash') ? 'bg-blue-50/30' : '' ?>">
                            <td class="px-5 py-3 font-medium text-slate-800">
                                <?php if ($cardName === 'GCash'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">GCash</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($cardName) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-right text-slate-600"><?= peso($lunchTotals['cardTypes'][$cardName]) ?></td>
                            <td class="px-5 py-3 text-right text-slate-600"><?= peso($dinnerTotals['cardTypes'][$cardName]) ?></td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900"><?= peso($grandTotals['cardTypes'][$cardName]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50 font-bold">
                    <tr>
                        <td class="px-5 py-3">Total Card &amp; GCash</td>
                        <td class="px-5 py-3 text-right text-amber-800"><?= peso($lunchTotals['creditCard']) ?></td>
                        <td class="px-5 py-3 text-right text-indigo-800"><?= peso($dinnerTotals['creditCard']) ?></td>
                        <td class="px-5 py-3 text-right text-blue-700"><?= peso($grandTotals['creditCard']) ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <!-- TRANSACTION DETAILS -->
        <section class="overflow-hidden rounded-xl bg-white shadow-sm border border-slate-200">
            <div class="border-b border-slate-200 p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Transaction Details</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Date and time are shown exactly as recorded in transactionTime (not converted).</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase">View:</span>
                    <button
                        type="button"
                        onclick="filterTransactions('all')"
                        id="btn-filter-all"
                        class="shift-filter-btn rounded-full bg-slate-800 px-3.5 py-1 text-xs font-semibold text-white shadow-sm transition-all"
                    >
                        All (<?= count($rows) ?>)
                    </button>
                    <button
                        type="button"
                        onclick="filterTransactions('Lunch')"
                        id="btn-filter-lunch"
                        class="shift-filter-btn rounded-full bg-slate-100 px-3.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-all"
                    >
                        ☀️ Lunch (<?= $lunchTotals['count'] ?>)
                    </button>
                    <button
                        type="button"
                        onclick="filterTransactions('Dinner')"
                        id="btn-filter-dinner"
                        class="shift-filter-btn rounded-full bg-slate-100 px-3.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-all"
                    >
                        🌙 Dinner (<?= $dinnerTotals['count'] ?>)
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="transactions-table">
                    <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Date / Time (as recorded)</th>
                        <th class="px-4 py-3">Shift</th>
                        <th class="px-4 py-3 text-right">Cash</th>
                        <th class="px-4 py-3 text-right">Service Charge</th>
                        <th class="px-4 py-3 text-right">Bar Sale</th>
                        <th class="px-4 py-3 text-right">Kitchen Sale</th>
                        <th class="px-4 py-3 text-right">Card &amp; GCash</th>
                        <th class="px-4 py-3">Card / GCash Details</th>
                        <th class="px-4 py-3 text-right">Unmapped</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                No transactions found for this date range.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="transaction-row hover:bg-slate-50" data-period="<?= htmlspecialchars($row['period']) ?>">
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-800">
                                    <?= htmlspecialchars((string) $row['invoice']) ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                    <?= htmlspecialchars((string) $row['date']) ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <?php if ($row['period'] === 'Lunch'): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">☀️ Lunch</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">🌙 Dinner</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-emerald-700"><?= peso($row['cash']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-800"><?= peso($row['serviceCharge']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-slate-600"><?= peso($row['barSale']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-slate-600"><?= peso($row['kitchenSale']) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-blue-700"><?= peso($row['creditCard']) ?></td>
                                <td class="min-w-48 px-4 py-3">
                                    <?php
                                    $hasCard = false;
                                    foreach ($cardNames as $cardName):
                                        $cardAmount = $row['cardTypes'][$cardName];
                                        if ($cardAmount != 0):
                                            $hasCard = true;
                                    ?>
                                        <div class="whitespace-nowrap text-xs">
                                            <span class="<?= ($cardName === 'GCash') ? 'font-semibold text-blue-700' : 'text-slate-500' ?>"><?= htmlspecialchars($cardName) ?>:</span>
                                            <span class="font-medium text-slate-700"><?= peso($cardAmount) ?></span>
                                        </div>
                                    <?php
                                        endif;
                                    endforeach;

                                    if (!$hasCard):
                                    ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-slate-500"><?= peso($row['unmappedSale']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>

                    <tfoot class="border-t border-slate-200 text-sm">
                    <tr class="bg-amber-50/70 text-amber-950 font-semibold border-b border-amber-200/60">
                        <td colspan="3" class="px-4 py-2.5">☀️ LUNCH SUB-TOTAL (<?= $lunchTotals['count'] ?> orders)</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-700"><?= peso($lunchTotals['cash']) ?></td>
                        <td class="px-4 py-2.5 text-right font-bold"><?= peso($lunchTotals['serviceCharge']) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= peso($lunchTotals['barSale']) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= peso($lunchTotals['kitchenSale']) ?></td>
                        <td class="px-4 py-2.5 text-right font-bold text-blue-700"><?= peso($lunchTotals['creditCard']) ?></td>
                        <td class="px-4 py-2.5"></td>
                        <td class="px-4 py-2.5 text-right"><?= peso($lunchTotals['unmappedSale']) ?></td>
                    </tr>
                    <tr class="bg-indigo-50/70 text-indigo-950 font-semibold border-b border-indigo-200/60">
                        <td colspan="3" class="px-4 py-2.5">🌙 DINNER SUB-TOTAL (<?= $dinnerTotals['count'] ?> orders)</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-700"><?= peso($dinnerTotals['cash']) ?></td>
                        <td class="px-4 py-2.5 text-right font-bold"><?= peso($dinnerTotals['serviceCharge']) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= peso($dinnerTotals['barSale']) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= peso($dinnerTotals['kitchenSale']) ?></td>
                        <td class="px-4 py-2.5 text-right font-bold text-blue-700"><?= peso($dinnerTotals['creditCard']) ?></td>
                        <td class="px-4 py-2.5"></td>
                        <td class="px-4 py-2.5 text-right"><?= peso($dinnerTotals['unmappedSale']) ?></td>
                    </tr>
                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="3" class="px-4 py-3">📊 GRAND TOTAL (<?= $grandTotals['count'] ?> orders)</td>
                        <td class="px-4 py-3 text-right text-emerald-700"><?= peso($grandTotals['cash']) ?></td>
                        <td class="px-4 py-3 text-right"><?= peso($grandTotals['serviceCharge']) ?></td>
                        <td class="px-4 py-3 text-right"><?= peso($grandTotals['barSale']) ?></td>
                        <td class="px-4 py-3 text-right"><?= peso($grandTotals['kitchenSale']) ?></td>
                        <td class="px-4 py-3 text-right text-blue-700"><?= peso($grandTotals['creditCard']) ?></td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right"><?= peso($grandTotals['unmappedSale']) ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <script>
        function filterTransactions(shift) {
            document.querySelectorAll('.transaction-row').forEach(function (row) {
                row.style.display = (shift === 'all' || row.dataset.period === shift) ? '' : 'none';
            });

            var buttons = {
                'all': document.getElementById('btn-filter-all'),
                'Lunch': document.getElementById('btn-filter-lunch'),
                'Dinner': document.getElementById('btn-filter-dinner')
            };

            for (var key in buttons) {
                if (!buttons[key]) continue;
                buttons[key].className = (key === shift)
                    ? 'shift-filter-btn rounded-full bg-slate-800 px-3.5 py-1 text-xs font-semibold text-white shadow-sm transition-all'
                    : 'shift-filter-btn rounded-full bg-slate-100 px-3.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-all';
            }
        }
        </script>

    <?php endif; ?>

    <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <strong>Important:</strong> Filtering and display use the date written in <code>transactionTime</code> (YYYY-MM-DD) with no timezone conversion, so all transactions with the same date appear under that date. Lunch is before <strong>09:00:00</strong>, Dinner is <strong>09:00:00 onwards</strong>. GCash and credit cards are grouped as card/electronic totals. The Product API is used to fetch category information for bar/kitchen classification.
    </div>

</div>
</body>
</html>