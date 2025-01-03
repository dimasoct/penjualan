<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Periksa apakah id_kategori dikirim
if (isset($_POST['id_kategori'])) {
    $id_kategori = $_POST['id_kategori'];

    // Query untuk menghapus data terkait di tabel barang (jika ada referensi dari barang)
    $delete_barang_sql = "DELETE FROM barang WHERE kategori = ?";
    $stmt_barang = $conn->prepare($delete_barang_sql);
    $stmt_barang->bind_param("s", $id_kategori); // Assuming 'kategori' in barang table is a string (id_kategori)
    $stmt_barang->execute();
    $stmt_barang->close();

    // Query untuk menghapus data kategori di tabel kategori_barang
    $delete_kategori_sql = "DELETE FROM kategori_barang WHERE id_kategori = ?";
    $stmt_kategori = $conn->prepare($delete_kategori_sql);
    $stmt_kategori->bind_param("s", $id_kategori); // id_kategori should be a string

    if ($stmt_kategori->execute()) {
        // Mengembalikan response JSON sukses
        echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dihapus.']);
    } else {
        // Mengembalikan response JSON error
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus kategori.']);
    }

    $stmt_kategori->close();
} else {
    // Jika id_kategori tidak ditemukan
    echo json_encode(['status' => 'error', 'message' => 'ID kategori tidak ditemukan.']);
}

$conn->close();
?>
