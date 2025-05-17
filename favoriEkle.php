<?php
session_start();
include("ayar.php");

// Set proper headers
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!isset($_SESSION['giris']) || !$_SESSION['giris']) {
        throw new Exception('Lütfen önce giriş yapın');
    }

    if (!isset($_POST['ilanID'])) {
        throw new Exception('İlan ID bulunamadı');
    }

    $ilanID = intval($_POST['ilanID']);
    $uyeID = $_SESSION['uyeID'];

    // Validate ilan exists
    $ilanKontrol = $baglan->prepare("SELECT ilanID FROM t_ilanlar WHERE ilanID = ?");
    $ilanKontrol->bind_param("i", $ilanID);
    $ilanKontrol->execute();
    if ($ilanKontrol->get_result()->num_rows === 0) {
        throw new Exception('İlan bulunamadı');
    }

    $baglan->begin_transaction();

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
        
        // İstatistikleri güncelle
        if ($yeniDurum == 1) {
            // Favorilere eklendiğinde sayıyı 1 artır
            $istatistikGuncelle = $baglan->prepare("
                UPDATE t_istatistik 
                SET istatistikFavoriSayisi = istatistikFavoriSayisi + 1,
                    istatistikSonGuncellenmeTarihi = NOW()
                WHERE istatistikIlanID = ?
            ");
        } else {
            // Favorilerden çıkarıldığında sayıyı 1 azalt
            $istatistikGuncelle = $baglan->prepare("
                UPDATE t_istatistik 
                SET istatistikFavoriSayisi = GREATEST(istatistikFavoriSayisi - 1, 0),
                    istatistikSonGuncellenmeTarihi = NOW()
                WHERE istatistikIlanID = ?
            ");
        }
        $istatistikGuncelle->bind_param("i", $ilanID);
        $istatistikGuncelle->execute();
        
        $action = $yeniDurum == 1 ? 'added' : 'removed';
        $message = $yeniDurum == 1 ? 'İlan favorilere eklendi' : 'İlan favorilerden çıkarıldı';
    } else {
        $ekleSorgu = $baglan->prepare("INSERT INTO t_favoriler (favoriUyeID, favoriIlanID, favoriDurum, favoriEklenmeTarihi) VALUES (?, ?, 1, NOW())");
        $ekleSorgu->bind_param("ii", $uyeID, $ilanID);
        $ekleSorgu->execute();
        
        // Yeni favori eklendiğinde istatistikleri güncelle
        $istatistikGuncelle = $baglan->prepare("
            INSERT INTO t_istatistik 
                (istatistikIlanID, istatistikFavoriSayisi, istatistikSonGuncellenmeTarihi)
            VALUES 
                (?, 1, NOW())
            ON DUPLICATE KEY UPDATE 
                istatistikFavoriSayisi = istatistikFavoriSayisi + 1,
                istatistikSonGuncellenmeTarihi = NOW()
        ");
        $istatistikGuncelle->bind_param("i", $ilanID);
        $istatistikGuncelle->execute();
        
        $action = 'added';
        $message = 'İlan favorilere eklendi';
    }

    // Get updated favorite count
    $sayiSorgu = $baglan->prepare("SELECT COUNT(*) as total FROM t_favoriler WHERE favoriIlanID = ? AND favoriDurum = 1");
    $sayiSorgu->bind_param("i", $ilanID);
    $sayiSorgu->execute();
    $yeniFavoriSayisi = $sayiSorgu->get_result()->fetch_assoc()['total'];

    $baglan->commit();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'message' => $message,
        'yeniFavoriSayisi' => $yeniFavoriSayisi
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