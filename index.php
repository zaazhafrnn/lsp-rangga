<?php
// index.php
require_once 'includes/functions.php';

$inventory = new Inventory();
$storage_units = $inventory->getStorageUnits();  // Ambil data gudang
$vendors = $inventory->getVendors();  // Ambil data vendor
$search = isset($_GET['search']) ? $_GET['search'] : '';
$items = $search ? $inventory->search($search) : $inventory->getAll();

// Handle form submission for adding a new item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $data = [
        'nama_barang' => $_POST['nama_barang'],
        'jenis_barang' => $_POST['jenis_barang'],
        'kuantitas' => $_POST['kuantitas'],
        'lokasi_gudang_id' => $_POST['lokasi_gudang_id'],
        'vendor_id' => $_POST['vendor_id'],
        'barcode' => $_POST['barcode']
    ];

    // Debugging: Print the data being sent
    echo "<pre>";
    print_r($data);
    echo "</pre>";

    $result = $inventory->create($data);
    if ($result) {
        echo "<script>alert('Data berhasil ditambahkan.');</script>";
        echo "<script>window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data.');</script>";
    }
}

// Handle form submission for editing an item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $data = [
        'id' => $_POST['id'],
        'nama_barang' => $_POST['nama_barang'],
        'jenis_barang' => $_POST['jenis_barang'],
        'kuantitas' => $_POST['kuantitas'],
        'lokasi_gudang_id' => $_POST['lokasi_gudang_id'],
        'barcode' => $_POST['barcode'],
        'vendor_id' => $_POST['vendor_id']
    ];

    if ($inventory->editItem($data)) {
        echo "<script>alert('Data berhasil diperbarui.');</script>";
        echo "<script>window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = $_POST['id'];
    $result = $inventory->delete($id);
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
    <title>Sistem Pengelolaan Inventory</title>
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
                    <a class="nav-link active" href="index.php">Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vendor.php">Vendor</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gudang.php">Gudang</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4" style="margin-left: 250px;">
        <h1>Sistem Pengelolaan Inventory</h1>

        <!-- Search Form -->
        <form class="mb-4" method="GET" action="index.php">
            <div class="input-group">
                <input type="text" class="form-control" name="search"
                    placeholder="Cari barang..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>

        <!-- Add New Item Button -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            Tambah Barang
        </button>

        <!-- Inventory Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Barang</th>
                        <th>Jenis</th>
                        <th>Kuantitas</th>
                        <th>Gudang</th>
                        <th>Vendor</th>
                        <th>Barcode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="<?= $item['kuantitas'] == 0 ? 'out-of-stock' : '' ?>">
                                <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($item['jenis_barang']) ?></td>
                                <td><?= htmlspecialchars($item['kuantitas']) ?></td>
                                <td><?= htmlspecialchars($item['nama_gudang']) ?></td>
                                <td><?= htmlspecialchars($item['vendor_nama']) ?></td>
                                <td><?= htmlspecialchars($item['barcode']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editItem(<?= $item['id'] ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?= $item['id'] ?>)">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
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
                        <h5 class="modal-title">Tambah Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="add_item" value="1">
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_barang" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Barang</label>
                                <input type="text" class="form-control" name="jenis_barang" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kuantitas</label>
                                <input type="number" class="form-control" name="kuantitas" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gudang</label>
                                <select class="form-control" name="lokasi_gudang_id" required>
                                    <option value="">Pilih Gudang</option>
                                    <?php foreach ($storage_units as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit['id']) ?>"><?= htmlspecialchars($unit['nama_gudang']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control" name="vendor_id" required>
                                    <option value="">Pilih Vendor</option>
                                    <?php foreach ($vendors as $vendor): ?>
                                        <option value="<?= htmlspecialchars($vendor['id']) ?>"><?= htmlspecialchars($vendor['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control" name="barcode" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
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
                        <h5 class="modal-title">Edit Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="edit_item" value="1">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_barang" id="editNamaBarang" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Barang</label>
                                <input type="text" class="form-control" name="jenis_barang" id="editJenisBarang" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kuantitas</label>
                                <input type="number" class="form-control" name="kuantitas" id="editKuantitas" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gudang</label>
                                <select class="form-control" name="lokasi_gudang_id" id="editLokasiGudangId" required>
                                    <option value="">Pilih Gudang</option>
                                    <?php foreach ($storage_units as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit['id']) ?>"><?= htmlspecialchars($unit['nama_gudang']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <select class="form-control" name="vendor_id" id="editVendorId" required>
                                    <option value="">Pilih Vendor</option>
                                    <?php foreach ($vendors as $vendor): ?>
                                        <option value="<?= htmlspecialchars($vendor['id']) ?>"><?= htmlspecialchars($vendor['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" class="form-control" name="barcode" id="editBarcode" required>
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
        function deleteItem(id) {
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
                    // Send a POST request to delete the item
                    fetch('index.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'delete_item': '1',
                                'id': id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Terhapus!',
                                    'Data berhasil dihapus.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Gagal menghapus data.',
                                    'error'
                                );
                            }
                        });
                }
            });
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('edit.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berhasil!',
                            'Data berhasil diperbarui.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Gagal memperbarui data.',
                            'error'
                        );
                    }
                });
        });

        function editItem(id) {
            fetch('get_item.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editNamaBarang').value = data.nama_barang;
                    document.getElementById('editJenisBarang').value = data.jenis_barang;
                    document.getElementById('editKuantitas').value = data.kuantitas;
                    document.getElementById('editLokasiGudangId').value = data.lokasi_gudang_id;
                    document.getElementById('editVendorId').value = data.vendor_id;
                    document.getElementById('editBarcode').value = data.barcode;

                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                });
        }
    </script>
</body>

</html>