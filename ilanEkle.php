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
    <div class="container mt-5">
        <h2>İlan Ekle</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <h4>Adres Bilgileri</h4>
            <div class="mb-3">
                <label for="adresBaslik" class="form-label">Adres Başlık</label>
                <input type="text" class="form-control" id="adresBaslik" name="adresBaslik" required>
            </div>
            <div class="mb-3">
                <label for="adresMahalle" class="form-label">Mahalle</label>
                <input type="text" class="form-control" id="adresMahalle" name="adresMahalle" required>
            </div>
            <div class="mb-3">
                <label for="adresIlce" class="form-label">İlçe</label>
                <input type="text" class="form-control" id="adresIlce" name="adresIlce" required>
            </div>
            <div class="mb-3">
                <label for="adresSehir" class="form-label">Şehir</label>
                <input type="text" class="form-control" id="adresSehir" name="adresSehir" required>
            </div>
            <div class="mb-3">
                <label for="adresUlke" class="form-label">Ülke</label>
                <input type="text" class="form-control" id="adresUlke" name="adresUlke" required>
            </div>
            <div class="mb-3">
                <label for="adresPostaKodu" class="form-label">Posta Kodu</label>
                <input type="text" class="form-control" id="adresPostaKodu" name="adresPostaKodu" required>
            </div>

            <h4>İlan Bilgileri</h4>
            <input type="hidden" name="ilanUyeID" value="<?php echo $_SESSION['giris']['uyeID'] ?? ''; ?>">
            <div class="mb-3">
                <label for="ilanDurum" class="form-label">İlan Durumu</label>
                <select class="form-select" id="ilanDurum" name="ilanDurum" required>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                    <option value="2">Satıldı</option>
                    <option value="3">Kiralandı</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="ilanFiyat" class="form-label">Fiyat</label>
                <input type="number" class="form-control" id="ilanFiyat" name="ilanFiyat" required>
            </div>
            <div class="mb-3">
                <label for="ilanMetrekareBrut" class="form-label">Brüt Metrekare</label>
                <input type="number" class="form-control" id="ilanMetrekareBrut" name="ilanMetrekareBrut" required>
            </div>
            <div class="mb-3">
                <label for="ilanMetrekareNet" class="form-label">Net Metrekare</label>
                <input type="number" class="form-control" id="ilanMetrekareNet" name="ilanMetrekareNet" required>
            </div>
            <div class="mb-3">
                <label for="ilanOdaSayisi" class="form-label">Oda Sayısı</label>
                <select class="form-select" id="ilanOdaSayisi" name="ilanOdaSayisi" required>
                    <option value="">Seçiniz</option>
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
            <div class="mb-3">
                <label for="ilanBinaYasi" class="form-label">Bina Yaşı</label>
                <input type="number" class="form-control" id="ilanBinaYasi" name="ilanBinaYasi" required>
            </div>
            <div class="mb-3">
                <label for="ilanSiteIcerisindeMi" class="form-label">Site İçerisinde Mi?</label>
                <select class="form-select" id="ilanSiteIcerisindeMi" name="ilanSiteIcerisindeMi" required>
                    <option value="1">Evet</option>
                    <option value="0">Hayır</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="ilanMulkTuru" class="form-label">Mülk Türü</label>
                <input type="text" class="form-control" id="ilanMulkTuru" name="ilanMulkTuru" required>
            </div>
            <div class="mb-3">
                <label for="ilanKonum" class="form-label">Konum</label>
                <input type="text" class="form-control" id="ilanKonum" name="ilanKonum" required>
            </div>
            <div class="mb-3">
                <label for="ilanIsitmaTipi" class="form-label">Isıtma Tipi</label>
                <input type="text" class="form-control" id="ilanIsitmaTipi" name="ilanIsitmaTipi" required>
            </div>
            <div class="mb-3">
                <label for="ilanBulunduguKat" class="form-label">Bulunduğu Kat</label>
                <input type="number" class="form-control" id="ilanBulunduguKat" name="ilanBulunduguKat" required>
            </div>
            <div class="mb-3">
                <label for="ilanBinaKatSayisi" class="form-label">Bina Kat Sayısı</label>
                <input type="number" class="form-control" id="ilanBinaKatSayisi" name="ilanBinaKatSayisi" required>
            </div>
            <div class="mb-3">
                <label for="ilanTur" class="form-label">İlan Türü</label>
                <input type="text" class="form-control" id="ilanTur" name="ilanTur" required>
            </div>
            <div class="mb-3">
                <label for="ilanDAciklama" class="form-label">Açıklama</label>
                <textarea class="form-control" id="ilanDAciklama" name="ilanDAciklama" required></textarea>
            </div>
            <div class="mb-3">
                <label for="ilanResimler" class="form-label">İlan Resimleri (En fazla 25 adet)</label>
                <input type="file" class="form-control" id="ilanResimler" name="ilanResimler[]" multiple accept="image/*">
                <small class="form-text text-muted">Birden fazla resim seçmek için Ctrl veya Shift tuşunu kullanabilirsiniz.</small>
            </div>
            <button type="submit" class="btn btn-primary">İlan Ekle</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>