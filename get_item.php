<?php
require_once 'includes/functions.php';

$inventory = new Inventory();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if we are fetching a storage unit or an inventory item
    if (isset($_GET['type']) && $_GET['type'] === 'storage_unit') {
        // Fetch storage unit by ID
        $unit = $inventory->getStorageUnitById($id); // Ensure this method exists
        if ($unit) {
            echo json_encode($unit);
        } else {
            echo json_encode(['error' => 'Storage unit not found']);
        }
    } elseif (isset($_GET['type']) && $_GET['type'] === 'vendor') {
        // Fetch vendor by ID
        $vendor = $inventory->getVendorById($id); // Ensure this method exists
        if ($vendor) {
            echo json_encode($vendor);
        } else {
            echo json_encode(['error' => 'Vendor not found']);
        }
    } else {
        // Fetch inventory item by ID
        $items = $inventory->getAll();
        foreach ($items as $item) {
            if ($item['id'] == $id) {
                echo json_encode($item);
                break;
            }
        }
    }
}
?>