<?php
session_start();
include("ayar.php");

// Hata raporlamayı aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON header'ı ekle
header('Content-Type: application/json');

try {
    // Oturum kontrolü
    if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
        throw new Exception('Oturum açmanız gerekiyor');
    }

    // POST verilerini kontrol et
    if (empty($_POST['ilanID']) || empty($_POST['aliciID']) || empty($_POST['mesajText'])) {
        throw new Exception('Eksik bilgi gönderildi');
    }

    // Verileri al ve temizle
    $ilanID = intval($_POST['ilanID']);
    $aliciID = intval($_POST['aliciID']);
    $gonderenID = intval($_SESSION['uyeID']);
    $mesajText = trim($_POST['mesajText']);

    // Verilerin geçerliliğini kontrol et
    if ($ilanID <= 0 || $aliciID <= 0 || $gonderenID <= 0) {
        throw new Exception('Geçersiz kullanıcı veya ilan bilgisi');
    }

    // Transaction başlat
    $baglan->begin_transaction();

    // Konuşma var mı kontrol et
    $konusmaSorgu = $baglan->prepare("
        SELECT konusmaID 
        FROM t_konusmalar 
        WHERE konusmaIlanID = ?
    ");
    $konusmaSorgu->bind_param("i", $ilanID);
    $konusmaSorgu->execute();
    $result = $konusmaSorgu->get_result();

    if ($result->num_rows === 0) {
        // Yeni konuşma oluştur
        $konusmaSorgu = $baglan->prepare("
            INSERT INTO t_konusmalar (konusmaIlanID, konusmaBaslangicTarihi, konusmaGuncellenmeTarihi) 
            VALUES (?, NOW(), NOW())
        ");
        $konusmaSorgu->bind_param("i", $ilanID);
        $konusmaSorgu->execute();
        $konusmaID = $baglan->insert_id;
    } else {
        $konusma = $result->fetch_assoc();
        $konusmaID = $konusma['konusmaID'];
    }

    // Mesajı kaydet
    $mesajSorgu = $baglan->prepare("
        INSERT INTO t_mesajlar 
        (mesajIletenID, mesajAlanID, mesajText, mesajKonusmaID, mesajOkunduDurumu, mesajGonderilmeTarihi) 
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $mesajSorgu->bind_param("iisi", $gonderenID, $aliciID, $mesajText, $konusmaID);
    
    if (!$mesajSorgu->execute()) {
        throw new Exception('Mesaj kaydedilemedi: ' . $baglan->error);
    }

    // İstatistikleri güncelle
    $istatistikSorgu = $baglan->prepare("
        INSERT INTO t_istatistik 
        (istatistikIlanID, istatistikMesajSayisi, istatistikSonGuncellenmeTarihi)
        VALUES (?, 1, NOW())
        ON DUPLICATE KEY UPDATE 
        istatistikMesajSayisi = istatistikMesajSayisi + 1,
        istatistikSonGuncellenmeTarihi = NOW()
    ");
    $istatistikSorgu->bind_param("i", $ilanID);
    $istatistikSorgu->execute();

    // Transaction'ı tamamla
    $baglan->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Mesaj başarıyla gönderildi'
    ]);

} catch (Exception $e) {
    // Hata durumunda rollback yap
    if (isset($baglan) && $baglan->connect_errno === 0) {
        $baglan->rollback();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>