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

    // Adres bilgilerini al ve sanitize et
    $adresBaslik = htmlspecialchars($_POST['adresBaslik'], ENT_QUOTES, 'UTF-8');
    $adresMahalle = htmlspecialchars($_POST['adresMahalle'], ENT_QUOTES, 'UTF-8');
    $adresIlce = htmlspecialchars($_POST['adresIlce'], ENT_QUOTES, 'UTF-8');
    $adresSehir = htmlspecialchars($_POST['adresSehir'], ENT_QUOTES, 'UTF-8');
    $adresUlke = htmlspecialchars($_POST['adresUlke'], ENT_QUOTES, 'UTF-8');
    $adresPostaKodu = htmlspecialchars($_POST['adresPostaKodu'], ENT_QUOTES, 'UTF-8');

    // Mülk tipi bilgisi
    $mulkTipiBaslik = htmlspecialchars($_POST['ilanMulkTuru'], ENT_QUOTES, 'UTF-8');

    // İlan türü bilgisi
    $ilanTurAdi = htmlspecialchars($_POST['ilanTur'], ENT_QUOTES, 'UTF-8');

    // İlan bilgilerini al
    $ilanDurum = $_POST['ilanDurum'];
    $ilanUyeID = $_SESSION['uyeID'];

    // İlan detay bilgilerini al
    $ilanDAciklama = htmlspecialchars($_POST['ilanDAciklama'], ENT_QUOTES, 'UTF-8');
    $ilanDBinaKatSayisi = intval($_POST['ilanBinaKatSayisi']);
    $ilanDBinaYasi = intval($_POST['ilanBinaYasi']);
    $ilanDBulunduguKatSayisi = intval($_POST['ilanBulunduguKat']);
    $ilanDFiyat = floatval($_POST['ilanFiyat']);
    $ilanDIsıtmaTipi = htmlspecialchars($_POST['ilanIsitmaTipi'], ENT_QUOTES, 'UTF-8');
    $ilanDKonumBilgisi = htmlspecialchars($_POST['ilanKonum'], ENT_QUOTES, 'UTF-8');
    $ilanDmetreKareBrut = floatval($_POST['ilanMetrekareBrut']);
    $ilanDmetreKareNet = floatval($_POST['ilanMetrekareNet']);
    $ilanDOdaSayisi = htmlspecialchars($_POST['ilanOdaSayisi'], ENT_QUOTES, 'UTF-8');
    $ilanDSiteIcerisindeMi = intval($_POST['ilanSiteIcerisindeMi']);

    // Adres bilgilerini t_adresler tablosuna kaydet
    $adresSorgu = $baglan->prepare("INSERT INTO t_adresler (adresBaslik, adresMahalle, adresIlce, adresSehir, adresUlke, adresPostaKodu, adresEklenmeTarihi, adresGuncellenmeTarihi, adresSilinmeTarihi) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL)");
    $adresSorgu->bind_param("ssssss", $adresBaslik, $adresMahalle, $adresIlce, $adresSehir, $adresUlke, $adresPostaKodu);
    $adresSorgu->execute();
    $adresID = $baglan->insert_id;

    // Mülk tipi bilgilerini t_mulktipi tablosuna kaydet
    $mulkTipiSorgu = $baglan->prepare("INSERT INTO t_mulktipi (mulkTipiBaslik) VALUES (?)");
    $mulkTipiSorgu->bind_param("s", $mulkTipiBaslik);
    $mulkTipiSorgu->execute();
    $mulkTipiID = $baglan->insert_id;

    // İlan türü bilgilerini t_ilantur tablosuna kaydet
    $ilanTurSorgu = $baglan->prepare("INSERT INTO t_ilantur (ilanTurAdi) VALUES (?)");
    $ilanTurSorgu->bind_param("s", $ilanTurAdi);
    $ilanTurSorgu->execute();
    $ilanTurID = $baglan->insert_id;

    // İlan bilgilerini t_ilanlar tablosuna kaydet
    $ilanSorgu = $baglan->prepare("INSERT INTO t_ilanlar (ilanAdresID, ilanDurum, ilanYayinTarihi, ilanGuncellenmeTarihi, ilanSilinmeTarihi, ilanUyeID) VALUES (?, ?, NOW(), NOW(), NULL, ?)");
    $ilanSorgu->bind_param("iii", $adresID, $ilanDurum, $ilanUyeID);
    $ilanSorgu->execute();
    $ilanID = $baglan->insert_id;

    // İlan detaylarını t_ilandetay tablosuna kaydet
    $detaySorgu = $baglan->prepare("INSERT INTO t_ilandetay (ilanDilanID, ilanDFiyat, ilanDmetreKareBrut, ilanDmetreKareNet, ilanDOdaSayisi, ilanDBinaYasi, ilanDSiteIcerisindeMi, ilanDMulkTipiID, ilanDMulkTuru, ilanDKonumBilgisi, ilanDIsıtmaTipi, ilanDBulunduguKatSayisi, ilanDBinaKatSayisi, ilanDIlanTurID, ilanDAciklama) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $detaySorgu->bind_param("idddiisisssiiis", $ilanID, $ilanDFiyat, $ilanDmetreKareBrut, $ilanDmetreKareNet, $ilanDOdaSayisi, $ilanDBinaYasi, $ilanDSiteIcerisindeMi, $mulkTipiID, $mulkTipiBaslik, $ilanDKonumBilgisi, $ilanDIsıtmaTipi, $ilanDBulunduguKatSayisi, $ilanDBinaKatSayisi, $ilanTurID, $ilanDAciklama);
    $detaySorgu->execute();

    // Resimleri yükle ve t_resimler tablosuna kaydet
    if (isset($_FILES['ilanResimler']) && count($_FILES['ilanResimler']['tmp_name']) > 0) {
        $uploads_dir = "uploads";
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true); // Klasör yoksa oluştur
        }

        $resimler = array();
        foreach ($_FILES['ilanResimler']['tmp_name'] as $key => $tmp_name) {
            if (is_uploaded_file($tmp_name)) {
                $name = basename($_FILES['ilanResimler']['name'][$key]);
                $upload_path = "$uploads_dir/$name";

                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $resimYolu = "../$upload_path";

                    // Resmin daha önce eklenip eklenmediğini kontrol et
                    $sorgu = $baglan->prepare("SELECT resimID FROM t_resimler WHERE resimUrl = ?");
                    $sorgu->bind_param("s", $resimYolu);
                    $sorgu->execute();
                    $sorgu->store_result();

                    if ($sorgu->num_rows > 0) {
                        $sorgu->bind_result($resimID);
                        $sorgu->fetch();
                    } else {
                        // Resim ekleniyor
                        $resimDurum = 1; // Varsayılan olarak aktif
                        $resimBaslik = $ilanDAciklama; // Resim başlığı olarak ilan açıklaması kullanılıyor
                        $resimSorgu = $baglan->prepare("INSERT INTO t_resimler (resimBaslik, resimDurum, resimEklenmeTarihi, resimGuncellenmeTarihi, resimIlanID, resimSilinmeTarihi, resimUrl) VALUES (?, ?, NOW(), NOW(), ?, NULL, ?)");
                        $resimSorgu->bind_param("siis", $resimBaslik, $resimDurum, $ilanID, $resimYolu);
                        $resimSorgu->execute();
                        $resimID = $baglan->insert_id;
                    }

                    $resimler[] = $resimID;
                }
            }
        }

        if (!empty($resimler)) {
            // İlk resmi kullanabilir veya diğer işlemler için resim ID'lerini kullanabilirsiniz
            echo "Resimler başarıyla yüklendi ve kaydedildi.";
        } else {
            echo "Resim yüklenirken bir hata oluştu.";
        }
    }

    // Başarılı mesajı ve yönlendirme
    $_SESSION['basarili'] = "İlan başarıyla eklendi.";
    header("Location: ilanDetay.php?id=" . $ilanID);
    exit();
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
                    <input type="text" class="form-control" placeholder="Isıtma Tipi" id="ilanIsitmaTipi" name="ilanIsitmaTipi" required>
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