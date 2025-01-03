<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id_stok_keluar'])) {
        http_response_code(400);
        echo "ID stok keluar tidak ditemukan.";
        exit;
    }

    $id_stok_keluar = $conn->real_escape_string($_POST['id_stok_keluar']);

    // Ambil data stok_keluar dan stok barang
    $sql = "
        SELECT stok_keluar.id_barang, stok_keluar.stok_keluar, stok.stok 
        FROM stok_keluar 
        JOIN stok ON stok_keluar.id_barang = stok.id_barang
        WHERE id_stok_keluar = '$id_stok_keluar'";
    $result = $conn->query($sql);

    if (!$result || $result->num_rows === 0) {
        http_response_code(404);
        echo "Data stok keluar tidak ditemukan.";
        exit;
    }

    $row = $result->fetch_assoc();
    $id_barang = $row['id_barang'];
    $stok_keluar = intval($row['stok_keluar']);
    $stok_sekarang = intval($row['stok']);

    // Kembalikan stok barang
    $stok_baru = $stok_sekarang + $stok_keluar;
    $update_stok_sql = "
        UPDATE stok 
        SET stok = $stok_baru 
        WHERE id_barang = '$id_barang'";

    if (!$conn->query($update_stok_sql)) {
        http_response_code(500);
        echo "Gagal memperbarui stok barang.";
        exit;
    }

    // Hapus data stok keluar
    $delete_sql = "
        DELETE FROM stok_keluar 
        WHERE id_stok_keluar = '$id_stok_keluar'";

    if ($conn->query($delete_sql)) {
        http_response_code(200);
        echo "Stok keluar berhasil dihapus.";
    } else {
        http_response_code(500);
        echo "Gagal menghapus data stok keluar.";
    }
} else {
    http_response_code(405);
    echo "Metode tidak diizinkan.";
}
?>
