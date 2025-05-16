<?php
session_start();
include("ayar.php");

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['giris']) || !isset($_SESSION['uyeID'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Lütfen önce giriş yapın'
    ]);
    exit;
}

// POST verisi kontrolü
if (!isset($_POST['ilanID'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'İlan ID bulunamadı'
    ]);
    exit;
}

$ilanID = intval($_POST['ilanID']);
$uyeID = intval($_SESSION['uyeID']);

// İlanın var olup olmadığını kontrol et
$ilanKontrol = $baglan->prepare("SELECT ilanID FROM t_ilanlar WHERE ilanID = ? AND ilanDurum = 1");
$ilanKontrol->bind_param("i", $ilanID);
$ilanKontrol->execute();
$ilanSonuc = $ilanKontrol->get_result();

if ($ilanSonuc->num_rows === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'İlan bulunamadı veya aktif değil'
    ]);
    exit;
}

// Favori kontrolü
$kontrolSorgu = $baglan->prepare("SELECT favoriID, favoriDurum FROM t_favoriler 
    WHERE favoriUyeID = ? AND favoriIlanID = ?");
$kontrolSorgu->bind_param("ii", $uyeID, $ilanID);
$kontrolSorgu->execute();
$result = $kontrolSorgu->get_result();
$favori = $result->fetch_assoc();

if ($result->num_rows > 0 && $favori['favoriDurum'] == 1) {
    // Favoriden çıkar (favoriDurum = 0 yap)
    $simdikiZaman = date('Y-m-d H:i:s');
    $silmeSorgu = $baglan->prepare("UPDATE t_favoriler 
        SET favoriDurum = 0, favoriSilinmeTarihi = ? 
        WHERE favoriID = ?");
    $silmeSorgu->bind_param("si", $simdikiZaman, $favori['favoriID']);
    
    if ($silmeSorgu->execute()) {
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'İlan favorilerden kaldırıldı'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Favori kaldırılırken bir hata oluştu'
        ]);
    }
} else {
    // Eğer daha önce eklenmiş ama silinmiş bir favori varsa, onu aktif et
    if ($result->num_rows > 0) {
        $guncellemeSorgu = $baglan->prepare("UPDATE t_favoriler 
            SET favoriDurum = 1, favoriSilinmeTarihi = NULL, favoriEklenmeTarihi = NOW() 
            WHERE favoriID = ?");
        $guncellemeSorgu->bind_param("i", $favori['favoriID']);
        $isSuccessful = $guncellemeSorgu->execute();
    } else {
        // Yeni favori ekle
        $eklemeSorgu = $baglan->prepare("INSERT INTO t_favoriler 
            (favoriUyeID, favoriIlanID, favoriDurum, favoriEklenmeTarihi) 
            VALUES (?, ?, 1, NOW())");
        $eklemeSorgu->bind_param("ii", $uyeID, $ilanID);
        $isSuccessful = $eklemeSorgu->execute();
    }
    
    if ($isSuccessful) {
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'İlan favorilere eklendi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Favori eklenirken bir hata oluştu'
        ]);
    }
}
?>