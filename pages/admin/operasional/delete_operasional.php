<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Periksa apakah id_biaya dikirim
if (isset($_POST['id_biaya'])) {
    $id_biaya = $_POST['id_biaya'];

    // Query untuk menghapus data
    $query = "DELETE FROM operasional WHERE id_biaya = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_biaya);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
}

$conn->close();
?>
