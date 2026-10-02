<?php

session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "You must be logged in to mark accounts as paid."]);
    exit;
}

require_once __DIR__ . "/../config/database.php";

$reportId = (int) ($_POST["report_id"] ?? 0);
$accountType = $_POST["account_type"] ?? "";

if ($reportId <= 0 || !in_array($accountType, ["owner", "non-owner"], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Invalid request parameters."]);
    exit;
}

// Get current notes
$stmt = mysqli_prepare($conn, "SELECT notes FROM daily_reports WHERE report_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $reportId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Sale not found."]);
    exit;
}

$payload = json_decode($row["notes"] ?? "", true);
if (!is_array($payload)) {
    $payload = [];
}

$hasPaidNote = static function ($note): bool {
    return preg_match('/(?:^|[^a-z])paid(?:$|[^a-z])/i', (string) $note) === 1;
};

// Handle notes from form submission if provided
$unpaidAccountNotes = $_POST["unpaid_account_notes"] ?? "";
if ($unpaidAccountNotes) {
    $notesArray = json_decode($unpaidAccountNotes, true);
    if (is_array($notesArray)) {
        $payload["unpaidAccountNotes"] = $notesArray;
    }
}

try {
    if ($accountType === "owner") {
        $accountId = (int) ($_POST["account_id"] ?? 0);
        if ($accountId <= 0) {
            http_response_code(422);
            echo json_encode(["success" => false, "message" => "Invalid account ID."]);
            exit;
        }

        // Add "paid" to the note
        $noteKey = "ownerNote_" . $accountId;
        if (isset($payload[$noteKey])) {
            $currentNote = $payload[$noteKey];
            // Check if "paid" is already in the note to avoid duplicates
            if (!$hasPaidNote($currentNote)) {
                $payload[$noteKey] = trim($currentNote) . " (paid)";
            }
        }

    } else {
        // Non-owner account
        $fieldIndex = $_POST["field_index"] ?? "";

        if ($fieldIndex === "") {
            // Single unpaid account
            $noteKey = "unpaidAccountNote";
            if (isset($payload[$noteKey])) {
                $currentNote = $payload[$noteKey];
                // Check if "paid" is already in the note to avoid duplicates
                if (!$hasPaidNote($currentNote)) {
                    $payload[$noteKey] = trim($currentNote) . " (paid)";
                }
            } else {
                // If no note exists, create one
                $payload[$noteKey] = "paid";
            }
        } else {
            // Multiple unpaid accounts
            $noteKey = "unpaidAccountNote_" . $fieldIndex;

            if (isset($payload[$noteKey])) {
                $currentNote = $payload[$noteKey];
                // Check if "paid" is already in the note to avoid duplicates
                if (!$hasPaidNote($currentNote)) {
                    $payload[$noteKey] = trim($currentNote) . " (paid)";
                }
            } else {
                // If no note exists, create one
                $payload[$noteKey] = "paid";
            }
        }
    }
    
    // Update the notes
    $notes = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $updateStmt = mysqli_prepare($conn, "UPDATE daily_reports SET notes = ? WHERE report_id = ?");
    mysqli_stmt_bind_param($updateStmt, "si", $notes, $reportId);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
    
    echo json_encode(["success" => true, "message" => "Account marked as paid successfully."]);
    
} catch (Throwable $error) {
    error_log("Mark as paid error: " . $error->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "The account could not be marked as paid: " . $error->getMessage()]);
}