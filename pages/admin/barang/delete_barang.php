<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Periksa apakah id_barang dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_barang'])) {
    $id_barang = $conn->real_escape_string($_POST['id_barang']);

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Cek apakah id_barang ada di tabel stok
        $check_stok = "SELECT id_barang FROM stok WHERE id_barang = '$id_barang'";
        $result = $conn->query($check_stok);

        if ($result->num_rows > 0) {
            // Hapus dari tabel stok jika ditemukan
            $delete_stok = "DELETE FROM stok WHERE id_barang = '$id_barang'";
            $conn->query($delete_stok);
        }

        // Hapus data dari tabel stok_masuk
        $delete_stok_masuk = "DELETE FROM stok_masuk WHERE id_barang = '$id_barang'";
        $conn->query($delete_stok_masuk);

        // Hapus data dari tabel stok_keluar 
        $delete_stok_keluar = "DELETE FROM stok_keluar WHERE id_barang = '$id_barang'";
        $conn->query($delete_stok_keluar);

        // Hapus data dari tabel barang
        $delete_barang = "DELETE FROM barang WHERE id_barang = '$id_barang'";
        $conn->query($delete_barang);

        // Commit transaksi jika semua query berhasil
        $conn->commit();
        echo "Data berhasil dihapus.";

    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}

// Tutup koneksi
$conn->close();
?>
