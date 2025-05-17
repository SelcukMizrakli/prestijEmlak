<?php
session_start();
include("ayar.php");

// Admin kontrolü
if (!isset($_SESSION['giris']) || !$_SESSION['giris'] || $_SESSION['uyeYetkiID'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkiniz yok']);
    exit;
}

if (!isset($_POST['ilanID'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ilan ID']);
    exit;
}

$ilanID = intval($_POST['ilanID']);

$query = $baglan->prepare("UPDATE t_ilanlar SET ilanDurum = 1 WHERE ilanID = ?");
$query->bind_param("i", $ilanID);

if ($query->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'İlan başarıyla kaldırıldı'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
}
?>