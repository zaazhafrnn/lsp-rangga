<?php
// gudang.php
require_once 'includes/functions.php';

$inventory = new Inventory();
$storage_units = $inventory->getStorageUnits();  // Ambil data gudang
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle form submission for adding a new storage unit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gudang'])) {
    $data = [
        'nama_gudang' => $_POST['nama_gudang'],
        'lokasi' => $_POST['lokasi']
    ];

    $result = $inventory->createStorageUnit($data);
    if ($result) {
        echo "<script>alert('Gudang berhasil ditambahkan.');</script>";
        echo "<script>window.location.href='gudang.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan gudang.');</script>";
    }
}

// Handle form submission for editing a storage unit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_gudang'])) {
    $data = [
        'id' => $_POST['id'],
        'nama_gudang' => $_POST['nama_gudang'],
        'lokasi' => $_POST['lokasi']
    ];

    if ($inventory->editStorageUnit($data)) {
        echo "<script>alert('Gudang berhasil diperbarui.');</script>";
        echo "<script>window.location.href='gudang.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui gudang.');</script>";
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gudang'])) {
    $id = $_POST['id'];
    $result = $inventory->deleteStorageUnit($id);
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
    <title>Pengelolaan Gudang</title>
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
                    <a class="nav-link" href="vendor.php">Vendor</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="gudang.php">Gudang</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4" style="margin-left: 250px;">
        <h1>Pengelolaan Gudang</h1>

        <!-- Search Form -->
        <form class="mb-4" method="GET" action="gudang.php">
            <div class="input-group">
                <input type="text" class="form-control" name="search"
                    placeholder="Cari gudang..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>

        <!-- Add New Storage Unit Button -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            Tambah Gudang
        </button>

        <!-- Storage Units Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Gudang</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($storage_units)): ?>
                        <?php foreach ($storage_units as $unit): ?>
                            <tr>
                                <td><?= htmlspecialchars($unit['nama_gudang']) ?></td>
                                <td><?= htmlspecialchars($unit['lokasi']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editGudang(<?= $unit['id'] ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteGudang(<?= $unit['id'] ?>)">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data</td>
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
                        <h5 class="modal-title">Tambah Gudang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addForm" method="POST" action="gudang.php">
                            <input type="hidden" name="add_gudang" value="1">
                            <div class="mb-3">
                                <label class="form-label">Nama Gudang</label>
                                <input type="text" class="form-control" name="nama_gudang" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" name="lokasi" required>
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
                        <h5 class="modal-title">Edit Gudang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm" method="POST" action="gudang.php">
                            <input type="hidden" name="edit_gudang" value="1">
                            <input type="hidden" name="id" id="editId">
                            <div class="mb-3">
                                <label class="form-label">Nama Gudang</label>
                                <input type="text" class="form-control" name="nama_gudang" id="editNamaGudang" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" name="lokasi" id="editLokasi" required>
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
        function editGudang(id) {
            fetch('get_item.php?id=' + id + '&type=storage_unit')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error); // Log error if data not found
                        return;
                    }
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editNamaGudang').value = data.nama_gudang;
                    document.getElementById('editLokasi').value = data.lokasi;

                    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                })
                .catch(error => console.error('Error fetching data:', error)); // Log fetch errors
        }

        function deleteGudang(id) {
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
                    fetch('gudang.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'delete_gudang': '1',
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