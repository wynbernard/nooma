<?php
    require_once __DIR__ . "/../config/database.php";
// Get active owner accounts
$owner_accounts = [];

$sql = "SELECT 
            account_holder_id,
            account_name,
            account_type
        FROM account_holders
        WHERE is_active = 1 and account_type = 'owner'
        ORDER BY account_name ASC";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {
        $owner_accounts[] = $row;
    }

}
?>