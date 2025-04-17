<?php
session_start();
if (!isset($_SESSION['giris'])) {
    header("Location: girisYap.php");
    exit();
}

include("ayar.php");

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    // Formdan gelen veriler
    $uyeID = $_SESSION['uyeID']; // Kullanıcının oturumdaki ID'si
    $mulkTipi = htmlspecialchars($_POST['ilanMulkTuru'], ENT_QUOTES, 'UTF-8'); // Örn: "Daire"
    $ilanTur = htmlspecialchars($_POST['ilanTur'], ENT_QUOTES, 'UTF-8'); // Örn: "Satılık"
    $adresBaslik = htmlspecialchars($_POST['adresBaslik'], ENT_QUOTES, 'UTF-8');
    $adresMahalle = htmlspecialchars($_POST['adresMahalle'], ENT_QUOTES, 'UTF-8');
    $adresIlce = htmlspecialchars($_POST['adresIlce'], ENT_QUOTES, 'UTF-8');
    $adresSehir = htmlspecialchars($_POST['adresSehir'], ENT_QUOTES, 'UTF-8');
    $adresUlke = htmlspecialchars($_POST['adresUlke'], ENT_QUOTES, 'UTF-8');
    $adresPostaKodu = htmlspecialchars($_POST['adresPostaKodu'], ENT_QUOTES, 'UTF-8');
    $ilanDurum = intval($_POST['ilanDurum']); // Varsayılan olarak aktif
    $ilanAciklama = htmlspecialchars($_POST['ilanDAciklama'], ENT_QUOTES, 'UTF-8');
    $ilanFiyat = floatval($_POST['ilanFiyat']);
    $metreKareBrut = floatval($_POST['ilanMetrekareBrut']);
    $metreKareNet = floatval($_POST['ilanMetrekareNet']);
    $odaSayisi = htmlspecialchars($_POST['ilanOdaSayisi'], ENT_QUOTES, 'UTF-8');
    $binaYasi = intval($_POST['ilanBinaYasi']);
    $siteIcerisindeMi = intval($_POST['ilanSiteIcerisindeMi']); // 0: Hayır, 1: Evet
    $mulkTuru = htmlspecialchars($_POST['ilanMulkTuru'], ENT_QUOTES, 'UTF-8'); // Örn: "Ev"
    $konumBilgisi = htmlspecialchars($_POST['ilanKonum'], ENT_QUOTES, 'UTF-8');
    $isitmaTipi = htmlspecialchars($_POST['ilanIsitmaTipi'], ENT_QUOTES, 'UTF-8');
    $bulunduguKat = intval($_POST['ilanBulunduguKat']);
    $binaKatSayisi = intval($_POST['ilanBinaKatSayisi']);

    // Veritabanı işlemleri
    try {
        $baglan->begin_transaction();

        // 1. t_mulktipi tablosuna ekleme
        $stmt = $baglan->prepare("INSERT INTO t_mulktipi (mulkTipiBaslik) VALUES (?)");
        $stmt->bind_param("s", $mulkTipi);
        $stmt->execute();
        $mulkTipiID = $baglan->insert_id;

        // 2. t_ilantur tablosuna ekleme
        $stmt = $baglan->prepare("INSERT INTO t_ilantur (ilanTurAdi) VALUES (?)");
        $stmt->bind_param("s", $ilanTur);
        $stmt->execute();
        $ilanTurID = $baglan->insert_id;

        // 3. t_adresler tablosuna ekleme
        $stmt = $baglan->prepare("INSERT INTO t_adresler (adresBaslik, adresMahalle, adresIlce, adresSehir, adresUlke, adresPostaKodu, adresEklenmeTarihi, adresGuncellenmeTarihi, adresSilinmeTarihi) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL)");
        $stmt->bind_param("ssssss", $adresBaslik, $adresMahalle, $adresIlce, $adresSehir, $adresUlke, $adresPostaKodu);
        $stmt->execute();
        $adresID = $baglan->insert_id;

        // 4. t_ilanlar tablosuna ekleme
        $stmt = $baglan->prepare("INSERT INTO t_ilanlar (ilanUyeID, ilanAdresID, ilanDurum, ilanYayinTarihi, ilanGuncellenmeTarihi, ilanSilinmeTarihi) VALUES (?, ?, ?, NOW(), NOW(), NULL)");
        $stmt->bind_param("iii", $uyeID, $adresID, $ilanDurum);
        $stmt->execute();
        $ilanID = $baglan->insert_id;

        // 5. t_ilandetay tablosuna ekleme
        $stmt = $baglan->prepare("INSERT INTO t_ilandetay (ilanDilanID, ilanDAciklama, ilanDFiyat, ilanDmetreKareBrut, ilanDmetreKareNet, ilanDOdaSayisi, ilanDBinaYasi, ilanDSiteIcerisindeMi, ilanDMulkTipiID, ilanDMulkTuru, ilanDKonumBilgisi, ilanDIsitmaTipi, ilanDBulunduguKatSayisi, ilanDBinaKatSayisi, ilanDIlanTurID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiiiiiisssiii", $ilanID, $ilanAciklama, $ilanFiyat, $metreKareBrut, $metreKareNet, $odaSayisi, $binaYasi, $siteIcerisindeMi, $mulkTipiID, $mulkTuru, $konumBilgisi, $isitmaTipi, $bulunduguKat, $binaKatSayisi, $ilanTurID);
        $stmt->execute();

        // Resimleri yükle ve t_resimler tablosuna kaydet
        if (isset($_FILES['ilanResimler']) && count($_FILES['ilanResimler']['tmp_name']) > 0) {
            $uploads_dir = "uploads";
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true); // Klasör yoksa oluştur
            }

            foreach ($_FILES['ilanResimler']['tmp_name'] as $key => $tmp_name) {
                if (is_uploaded_file($tmp_name)) {
                    $name = basename($_FILES['ilanResimler']['name'][$key]);
                    $upload_path = "$uploads_dir/$name";

                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $resimYolu = "../$upload_path";
                        $resimDurum = 1; // Varsayılan olarak aktif
                        $resimBaslik = $ilanAciklama; // Resim başlığı olarak ilan açıklaması kullanılıyor

                        $stmt = $baglan->prepare("INSERT INTO t_resimler (resimIlanID, resimBaslik, resimUrl, resimDurum, resimEklenmeTarihi, resimGuncellenmeTarihi, resimSilinmeTarihi) VALUES (?, ?, ?, ?, NOW(), NOW(), NULL)");
                        $stmt->bind_param("issi", $ilanID, $resimBaslik, $resimYolu, $resimDurum);
                        $stmt->execute();
                    }
                }
            }
        }

        // İşlemleri tamamla
        $baglan->commit();
        $_SESSION['basarili'] = "İlan başarıyla eklendi.";
        header("Location: ilanDetay.php?id=" . $ilanID);
        exit();
    } catch (Exception $e) {
        $baglan->rollback();
        die("Hata: " . $e->getMessage());
    }

    // Bağlantıyı kapat
    $stmt->close();
    $baglan->close();
}
?>

<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prestij Emlak - İlan Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <?php include("header.php"); ?>
    <div class="container mt-5" style="width: 30%;">
        <h2>İlan Ekle</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <h4>Adres Bilgileri</h4>
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Adres Başlık" id="adresBaslik" name="adresBaslik" required>
            </div>
            <div class="d-flex gap-3"><!-- Flexbox ile yan yana dizme -->
                <div class="mb-3" style="flex: 1;">
                    <input type="text" class="form-control" placeholder="Mahalle" id="adresMahalle" name="adresMahalle" required>
                </div>
                <div class="mb-3" style="flex: 1;">
                    <input type="text" class="form-control" placeholder="İlçe" id="adresIlce" name="adresIlce" required>
                </div>
                <div class="mb-3" style="flex: 1;">
                    <input type="text" class="form-control" placeholder="Şehir" id="adresSehir" name="adresSehir" required>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="mb-3" style="flex: 1;">
                    <input type="text" class="form-control" placeholder="Ülke" id="adresUlke" name="adresUlke" required>
                </div>
                <div class="mb-3" style="flex: 1;">
                    <input type="text" class="form-control" placeholder="Posta Kodu" id="adresPostaKodu" name="adresPostaKodu" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Konum Bilgisi" id="ilanKonum" name="ilanKonum" required>
                </div>
            </div>
            <div class="mb-3">
                <textarea class="form-control" id="ilanDAciklama" placeholder="Adresin detaylı açıklaması" name="ilanDAciklama" required></textarea>
            </div>

            <h4>İlan Bilgileri</h4>
            <input type="hidden" name="ilanUyeID" value="<?php echo $_SESSION['uyeID'] ?? ''; ?>">
            <div class="d-flex gap-3">
                <div class="mb-3" style="width: 40%;">
                    <select class="form-select" id="ilanDurum" name="ilanDurum" required>
                        <option value="" disabled selected>İlan Durumu</option>
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                        <option value="2">Satıldı</option>
                        <option value="3">Kiralandı</option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="İlan Türü" id="ilanTur" name="ilanTur" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Mülk Tipi" id="ilanMulkTipi" name="ilanMulkTipi" required>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="mb-3">
                    <input type="number" class="form-control" placeholder="Fiyat" id="ilanFiyat" name="ilanFiyat" required>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" placeholder="Brüt Metrekare" id="ilanMetrekareBrut" name="ilanMetrekareBrut" required>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" placeholder="Net Metrekare" id="ilanMetrekareNet" name="ilanMetrekareNet" required>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="mb-3" style="width: 33%;">
                    <select class="form-select" id="ilanOdaSayisi" name="ilanOdaSayisi" required>
                        <option value="" disabled selected>Oda Sayısı</option>
                        <option value="1+1">1+1</option>
                        <option value="2+1">2+1</option>
                        <option value="3+1">3+1</option>
                        <option value="4+1">4+1</option>
                        <option value="5+1">5+1</option>
                        <option value="1+0">1+0</option>
                        <option value="2+0">2+0</option>
                        <option value="3+0">3+0</option>
                        <!-- Diğer seçenekleri ekleyebilirsiniz -->
                    </select>
                </div>
                <div class="mb-3" style="width: 33%;">
                    <input type="number" class="form-control" placeholder="Bina Yaşı" id="ilanBinaYasi" name="ilanBinaYasi" required>
                </div>
                <div class="mb-3" style="width: 33%;">
                    <select class="form-select" id="ilanSiteIcerisindeMi" name="ilanSiteIcerisindeMi" required>
                        <option value="" disabled selected>Site İçerisinde mi</option>
                        <option value="1">Evet</option>
                        <option value="0">Hayır</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Isitma Tipi" id="ilanIsitmaTipi" name="ilanIsitmaTipi" required>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" placeholder="Bulunduğu Kat" id="ilanBulunduguKat" name="ilanBulunduguKat" required>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" placeholder="Bina Kat Sayısı" id="ilanBinaKatSayisi" name="ilanBinaKatSayisi" required>
                </div>
            </div>
            <div class="mb-3" style="width: 60%; margin: auto;">
                <label for="ilanResimler" class="form-label">İlan Resimleri (En fazla 25 adet)</label>
                <input type="file" class="form-control" id="ilanResimler" name="ilanResimler[]" multiple accept="image/*">
                <small class="form-text text-muted">Birden fazla resim seçmek için Ctrl veya Shift tuşunu kullanabilirsiniz.</small><br><br>
                <button type="submit" class="btn btn-primary" style="margin-left: 33%;">İlan Ekle</button><br><br>
            </div>

        </form>
    </div>
    <footer>
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
        <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>