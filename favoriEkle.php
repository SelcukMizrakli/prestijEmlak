<?php
session_start();
include("ayar.php");

// JSON header'ı ekle
header('Content-Type: application/json');

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Oturum kontrolü
    if (!isset($_SESSION['giris']) || !$_SESSION['giris']) {
        throw new Exception('Lütfen önce giriş yapın.');
    }

    // POST verisi kontrolü
    if (!isset($_POST['ilanID'])) {
        throw new Exception('İlan ID bulunamadı.');
    }

    $ilanID = intval($_POST['ilanID']);
    $uyeID = $_SESSION['uyeID'];

    $baglan->begin_transaction();

    // Mevcut favori durumunu kontrol et
    $kontrolSorgu = $baglan->prepare("SELECT favoriID, favoriDurum FROM t_favoriler WHERE favoriUyeID = ? AND favoriIlanID = ?");
    $kontrolSorgu->bind_param("ii", $uyeID, $ilanID);
    $kontrolSorgu->execute();
    $result = $kontrolSorgu->get_result();

    if ($result->num_rows > 0) {
        $favori = $result->fetch_assoc();
        $yeniDurum = $favori['favoriDurum'] == 1 ? 0 : 1;
        
        $guncelleSorgu = $baglan->prepare("UPDATE t_favoriler SET favoriDurum = ? WHERE favoriID = ?");
        $guncelleSorgu->bind_param("ii", $yeniDurum, $favori['favoriID']);
        $guncelleSorgu->execute();

        $message = $yeniDurum == 1 ? 'İlan favorilere eklendi.' : 'İlan favorilerden çıkarıldı.';
        $action = $yeniDurum == 1 ? 'added' : 'removed';
    } else {
        $ekleSorgu = $baglan->prepare("INSERT INTO t_favoriler (favoriUyeID, favoriIlanID, favoriDurum, favoriEklenmeTarihi) VALUES (?, ?, 1, NOW())");
        $ekleSorgu->bind_param("ii", $uyeID, $ilanID);
        $ekleSorgu->execute();

        $message = 'İlan favorilere eklendi.';
        $action = 'added';
    }

    // İstatistikleri güncelle
    $istatistikSorgu = $baglan->prepare("
        INSERT INTO t_istatistik 
            (istatistikIlanID, istatistikFavoriSayisi, istatistikSonGuncellenmeTarihi)
        VALUES 
            (?, (SELECT COUNT(*) FROM t_favoriler WHERE favoriIlanID = ? AND favoriDurum = 1), NOW())
        ON DUPLICATE KEY UPDATE 
            istatistikFavoriSayisi = (SELECT COUNT(*) FROM t_favoriler WHERE favoriIlanID = ? AND favoriDurum = 1),
            istatistikSonGuncellenmeTarihi = NOW()
    ");
    $istatistikSorgu->bind_param("iii", $ilanID, $ilanID, $ilanID);
    $istatistikSorgu->execute();

    $baglan->commit();

    // Güncel istatistik değerlerini al
    $istatistikGetir = $baglan->prepare("
        SELECT istatistikFavoriSayisi 
        FROM t_istatistik 
        WHERE istatistikIlanID = ?
    ");
    $istatistikGetir->bind_param("i", $ilanID);
    $istatistikGetir->execute();
    $istatistik = $istatistikGetir->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action,
        'yeniFavoriSayisi' => $istatistik['istatistikFavoriSayisi']
    ]);

} catch (Exception $e) {
    if (isset($baglan) && $baglan->connect_errno === 0) {
        $baglan->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>