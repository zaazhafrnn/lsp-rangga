<?php
require_once 'includes/functions.php';

$inventory = new Inventory();
$vendors = $inventory->getVendors();  // Fetch all vendors
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle form submission for adding a new vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vendor'])) {
    $data = [
        'nama' => $_POST['nama'],
        'kontak' => $_POST['kontak'],
        'nama_barang' => $_POST['nama_barang'],
        'nomor_invoice' => $_POST['nomor_invoice']
    ];

    $result = $inventory->createVendor($data['nama'], $data['kontak'], $data['nama_barang'], $data['nomor_invoice']);
    if ($result) {
        echo "<script>alert('Vendor berhasil ditambahkan.');</script>";
        echo "<script>window.location.href='vendor.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan vendor.');</script>";
    }
}

// Handle form submission for editing a vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_vendor'])) {
    $data = [
        'id' => $_POST['id'],
        'nama' => $_POST['nama'],
        'kontak' => $_POST['kontak'],
        'nama_barang' => $_POST['nama_barang'],
        'nomor_invoice' => $_POST['nomor_invoice']
    ];

    if ($inventory->updateVendor($data['id'], $data['nama'], $data['kontak'], $data['nama_barang'], $data['nomor_invoice'])) {
        echo "<script>alert('Vendor berhasil diperbarui.');</script>";
        echo "<script>window.location.href='vendor.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui vendor.');</script>";
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vendor'])) {
    $id = $_POST['id'];
    $result = $inventory->deleteVendor($id);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit; // Exit to prevent further output
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background-color: #f8f9fa;
            /* Light background for the sidebar */
            border-right: 1px solid #dee2e6;
            /* Right border for separation */
        }

        .nav-link {
            color: #495057;
            /* Dark text color */
        }

        .nav-link:hover {
            background-color: #e9ecef;
            /* Light gray background on hover */
        }

        .nav-link.active {
            background-color: #007bff;
            /* Blue background for active link */
            color: white;
            /* White text for active link */
        }

        .out-of-stock {
            background-color: #f8d7da;
            /* Light red background */
            color: #721c24;
            /* Dark red text */
        }
    </style>
</head>

<body>
    <nav class="sidebar bg-light" style="width: 250px; height: 100vh; position: fixed;">
        <div class="p-3">
            <h4 class="text-center">Menu</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="vendor.php">Vendor</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gudang.php">Gudang</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4" style="margin-left: 250px;">
        <h1>Pengelolaan Vendor</h1>

        <!-- Search Form -->
        <form class="mb-4" method="GET" action="vendor.php">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Cari vendor..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>

        <!-- Add New Vendor Button -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            Tambah Vendor
        </button>

        <!-- Vendors Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Vendor</th>
                        <th>Kontak</th>
                        <th>Nama Barang</th>
                        <th>Nomor Invoice</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vendors)): ?>
                        <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><?= htmlspecialchars($vendor['nama']) ?></td>
                                <td><?= htmlspecialchars($vendor['kontak']) ?></td>
                                <td><?= htmlspecialchars($vendor['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($vendor['nomor_invoice']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editVendor(<?= $vendor['id'] ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteVendor(<?= $vendor['id'] ?>)">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addVendorForm" method="POST" action="vendor.php">
                            <input type="hidden" name="add_vendor" value="1">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Vendor</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="kontak" class="form-label">Kontak</label>
                                <input type="text" class="form-control" id="kontak" name="kontak" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama_barang" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang">
                            </div>
                            <div class="mb-3">
                                <label for="nomor_invoice" class="form-label">Nomor Invoice</label>
                                <input type="text" class="form-control" id="nomor_invoice" name="nomor_invoice">
                            </div>
                            <button type="submit" class="btn btn-primary">Tambah Vendor</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm" method="POST" action="vendor.php">
                            <input type="hidden" name="edit_vendor" value="1">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3">
                                <label class="form-label">Nama Vendor</label>
                                <input type="text" class="form-control" name="nama" id="editName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kontak</label>
                                <input type="text" class="form-control" name="kontak" id="editContact" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_barang" id="editItemName">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor Invoice</label>
                                <input type="text" class="form-control" name="nomor_invoice" id="editInvoiceNumber">
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
    <script>
        function editVendor(id) {
            console.log("Editing vendor with ID:", id); // Debug log
            fetch('get_item.php?id=' + id + '&type=vendor')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error); // Log error if data not found
                        return;
                    }
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editName').value = data.nama;
                    document.getElementById('editContact').value = data.kontak;
                    document.getElementById('editItemName').value = data.nama_barang;
                    document.getElementById('editInvoiceNumber').value = data.nomor_invoice;

                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                })
                .catch(error => console.error('Error fetching data:', error)); // Log fetch errors
        }

        function deleteVendor(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('vendor.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'delete_vendor': '1',
                                'id': id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error!', 'Gagal menghapus data.', 'error');
                            }
                        });
                }
            });
        }
    </script>

</body>

</html>