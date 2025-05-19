<?php
session_start();
include("ayar.php");

header('Content-Type: application/json');

try {
    // Oturum kontrolü
    if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
        throw new Exception("Giriş yapmanız gerekiyor");
    }

    $gonderenID = $_SESSION['uyeID'];
    
    // GET/POST verilerini kontrol et
    $konusmaID = isset($_GET['konusmaID']) ? intval($_GET['konusmaID']) : 0;
    $mesajText = isset($_GET['mesajText']) ? trim($_GET['mesajText']) : '';

    // Debug için gelen verileri logla
    error_log("KonusmaID: " . $konusmaID);
    error_log("MesajText: " . $mesajText);
    error_log("GonderenID: " . $gonderenID);

    if (empty($mesajText)) {
        throw new Exception("Mesaj metni boş olamaz");
    }

    if ($konusmaID <= 0) {
        throw new Exception("Geçersiz konuşma ID");
    }

    // Konuşmanın diğer katılımcısını bul
    $konusmaSorgu = $baglan->prepare("
        SELECT 
            CASE 
                WHEN k.konusmaBaslatanID = ? THEN k.konusmaAliciID
                ELSE k.konusmaBaslatanID
            END as aliciID,
            konusmaIlanID
        FROM t_konusmalar k
        WHERE k.konusmaID = ?
    ");
    
    $konusmaSorgu->bind_param("ii", $gonderenID, $konusmaID);
    $konusmaSorgu->execute();
    $result = $konusmaSorgu->get_result();
    $konusma = $result->fetch_assoc();

    if (!$konusma) {
        throw new Exception("Konuşma bulunamadı");
    }

    // Mesajı veritabanına ekle
    $mesajEkle = $baglan->prepare("
        INSERT INTO t_mesajlar (
            mesajKonusmaID,
            mesajIletenID,
            mesajAlanID,
            mesajText,
            mesajOkunduDurumu,
            mesajGonderilmeTarihi
        ) VALUES (?, ?, ?, ?, 0, NOW())
    ");

    $mesajEkle->bind_param("iiis", 
        $konusmaID,
        $gonderenID,
        $konusma['aliciID'],
        $mesajText
    );

    if (!$mesajEkle->execute()) {
        throw new Exception("Mesaj gönderilemedi: " . $mesajEkle->error);
    }

    // Konuşmanın güncellenme tarihini güncelle
    $guncelle = $baglan->prepare("
        UPDATE t_konusmalar 
        SET konusmaGuncellenmeTarihi = NOW() 
        WHERE konusmaID = ?
    ");
    
    $guncelle->bind_param("i", $konusmaID);
    if (!$guncelle->execute()) {
        throw new Exception("Konuşma güncellenemedi: " . $guncelle->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Mesaj başarıyla gönderildi',
        'mesajID' => $baglan->insert_id,
        'debug' => [
            'konusmaID' => $konusmaID,
            'gonderenID' => $gonderenID,
            'aliciID' => $konusma['aliciID'],
            'ilanID' => $konusma['konusmaIlanID'],
            'mesajText' => $mesajText
        ]
    ]);

} catch (Exception $e) {
    error_log("Mesaj gönderme hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>