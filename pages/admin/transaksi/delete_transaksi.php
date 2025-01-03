<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek jika parameter id_transaksi ada
if (isset($_GET['id_transaksi'])) {
    $id_transaksi = intval($_GET['id_transaksi']);

    // Query untuk menghapus transaksi
    $sql = "DELETE FROM transaksi WHERE id_transaksi = ?";

    // Persiapkan query
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameter dan eksekusi query
        $stmt->bind_param("i", $id_transaksi);

        // Cek apakah eksekusi berhasil
        if ($stmt->execute()) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $stmt->error;
        }

        // Tutup statement
        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
} else {
    echo "Invalid request!";
}

$conn->close();
?>
