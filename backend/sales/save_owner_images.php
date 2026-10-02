<?php

function save_owner_account_images(array &$payload): void
{
    $uploadDir = dirname(__DIR__, 2) . "/uploads/owner-accounts";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        return;
    }

    $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif", "webp" => "image/webp"];

    foreach ($_FILES as $key => $file) {
        if (!preg_match('/^owner_image_(\d+)$/', $key, $matches)) {
            continue;
        }
        if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }
        if (($file["size"] ?? 0) > 5 * 1024 * 1024) {
            continue;
        }

        $accountId = $matches[1];
        $extension = strtolower(pathinfo((string) ($file["name"] ?? ""), PATHINFO_EXTENSION));
        $mime = (string) ($file["type"] ?? "");
        if (!isset($allowed[$extension])) {
            continue;
        }
        if ($mime !== "" && $mime !== "application/octet-stream" && !in_array($mime, $allowed, true)) {
            continue;
        }

        $filename = "owner_" . $accountId . "_" . date("YmdHis") . "_" . bin2hex(random_bytes(4)) . "." . $extension;
        $destination = $uploadDir . "/" . $filename;
        if (!is_uploaded_file($file["tmp_name"]) || !move_uploaded_file($file["tmp_name"], $destination)) {
            continue;
        }

        $payload["ownerImage_" . $accountId] = "uploads/owner-accounts/" . $filename;
    }
}
