<?php
// includes/functions.php
require_once __DIR__ . '/../config/database.php';

class Inventory
{
    private $conn;
    private $table_name = "inventory";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->createTables();
    }

    private function createTables()
    {
        $queries = [
            "CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                kontak VARCHAR(20),
                email VARCHAR(100) UNIQUE
            )",
            "CREATE TABLE IF NOT EXISTS vendor (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                kontak VARCHAR(20),
                nama_barang VARCHAR(100),
                nomor_invoice VARCHAR(50)
            )",
            "CREATE TABLE IF NOT EXISTS storage_unit (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama_gudang VARCHAR(100) NOT NULL,
                lokasi VARCHAR(200)
            )",
            "CREATE TABLE IF NOT EXISTS inventory (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama_barang VARCHAR(100) NOT NULL,
                jenis_barang VARCHAR(50),
                kuantitas INT DEFAULT 0,
                lokasi_gudang_id INT,
                barcode VARCHAR(50) UNIQUE,
                vendor_id INT,
                FOREIGN KEY (lokasi_gudang_id) REFERENCES storage_unit(id),
                FOREIGN KEY (vendor_id) REFERENCES vendor(id)
            )"
        ];

        foreach ($queries as $query) {
            try {
                $this->conn->exec($query);
            } catch (PDOException $e) {
                echo "Table creation failed: " . $e->getMessage();
            }
        }
    }

    public function getAll()
    {
        try {
            $query = "SELECT i.*, v.nama as vendor_nama, s.nama_gudang 
                     FROM " . $this->table_name . " i 
                     LEFT JOIN vendor v ON i.vendor_id = v.id 
                     LEFT JOIN storage_unit s ON i.lokasi_gudang_id = s.id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function search($keyword)
    {
        try {
            $query = "SELECT i.*, v.nama as vendor_nama, s.nama_gudang 
                     FROM " . $this->table_name . " i 
                     LEFT JOIN vendor v ON i.vendor_id = v.id 
                     LEFT JOIN storage_unit s ON i.lokasi_gudang_id = s.id 
                     WHERE i.nama_barang LIKE :keyword 
                     OR i.jenis_barang LIKE :keyword 
                     OR i.barcode LIKE :keyword";
            $stmt = $this->conn->prepare($query);
            $keyword = "%{$keyword}%";
            $stmt->bindParam(":keyword", $keyword);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function create($data)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     SET nama_barang=:nama_barang, 
                         jenis_barang=:jenis_barang, 
                         kuantitas=:kuantitas, 
                         lokasi_gudang_id=:lokasi_gudang_id, 
                         barcode=:barcode, 
                         vendor_id=:vendor_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($data);
            return true; // Return true if successful
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage(); // Display the error message
            return false; // Return false if there was an error
        }
    }

    public function editItem($data)
    {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET nama_barang=:nama_barang, 
                         jenis_barang=:jenis_barang, 
                         kuantitas=:kuantitas, 
                         lokasi_gudang_id=:lokasi_gudang_id, 
                         barcode=:barcode, 
                         vendor_id=:vendor_id 
                     WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }
    public function delete($id)
    {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Fungsi untuk mengambil semua gudang
    public function getStorageUnits()
    {
        try {
            $query = "SELECT id, nama_gudang, lokasi FROM storage_unit";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // fungsi untuk buat & edit & delete gudang
    public function createStorageUnit($data)
    {
        try {
            $query = "INSERT INTO storage_unit (nama_gudang, lokasi) 
                      VALUES (:nama_gudang, :lokasi)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($data);
            return true; // Return true if successful
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage(); // Display the error message
            return false; // Return false if there was an error
        }
    }

    public function editStorageUnit($data)
    {
        try {
            $query = "UPDATE storage_unit 
                      SET nama_gudang = :nama_gudang, 
                          lokasi = :lokasi 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteStorageUnit($id)
    {
        try {
            $query = "DELETE FROM storage_unit WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getStorageUnitById($id)
    {
        try {
            // Prepare the SQL statement to prevent SQL injection
            $query = "SELECT * FROM storage_unit WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Bind the ID parameter
            $stmt->execute();

            // Fetch the storage unit data
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle any errors (you can log the error message if needed)
            return false;
        }
    }

    // Fungsi untuk mengambil semua vendor
    public function getVendors()
    {
        try {
            $query = "SELECT * FROM vendor";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    

    // Create a new vendor
    public function createVendor($nama, $kontak, $nama_barang = null, $nomor_invoice = null)
    {
        $query = "INSERT INTO vendor (nama, kontak, nama_barang, nomor_invoice) VALUES (:nama, :kontak, :nama_barang, :nomor_invoice)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':kontak', $kontak);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':nomor_invoice', $nomor_invoice);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId(); // Return the ID of the newly created vendor
        }
        return false; // Return false if the insertion failed
    }

    // Update an existing vendor
    public function updateVendor($id, $nama, $kontak, $nama_barang = null, $nomor_invoice = null)
    {
        $query = "UPDATE vendor SET nama = :nama, kontak = :kontak, nama_barang = :nama_barang, nomor_invoice = :nomor_invoice WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':kontak', $kontak);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':nomor_invoice', $nomor_invoice);
        return $stmt->execute(); // Return true if the update was successful, false otherwise
    }

    // Get vendor by ID (optional, if you need it)
    public function getVendorById($id)
    {
        $query = "SELECT * FROM vendor WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a vendor (optional, if you need it)
    public function deleteVendor($id)
    {
        $query = "DELETE FROM vendor WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute(); // Return true if the deletion was successful, false otherwise
    }
}
