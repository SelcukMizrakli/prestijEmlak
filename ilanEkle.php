<?php
session_start();
if (!isset($_SESSION['giris'])) {
    header("Location: girisYap.php");
    exit();
}

include("ayar.php");

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adres bilgilerini al
    $adresBaslik = $_POST['adresBaslik'] ?? null;
    $adresMahalle = $_POST['adresMahalle'] ?? null;
    $adresIlce = $_POST['adresIlce'] ?? null;
    $adresSehir = $_POST['adresSehir'] ?? null;
    $adresUlke = $_POST['adresUlke'] ?? null;
    $adresPostaKodu = $_POST['adresPostaKodu'] ?? null;

    // İlan bilgilerini al
    $ilanUyeID = $_POST['ilanUyeID'] ?? null; // Kullanıcı ID'si
    $ilanDurum = $_POST['ilanDurum'] ?? null;
    $ilanFiyat = $_POST['ilanFiyat'] ?? null;
    $ilanMetrekareBrut = $_POST['ilanMetrekareBrut'] ?? null;
    $ilanMetrekareNet = $_POST['ilanMetrekareNet'] ?? null;
    $ilanOdaSayisi = $_POST['ilanOdaSayisi'] ?? null;
    $ilanBinaYasi = $_POST['ilanBinaYasi'] ?? null;
    $ilanSiteIcerisindeMi = $_POST['ilanSiteIcerisindeMi'] ?? null;
    $ilanMulkTuru = $_POST['ilanMulkTuru'] ?? null;
    $ilanKonum = $_POST['ilanKonum'] ?? null;
    $ilanIsitmaTipi = $_POST['ilanIsitmaTipi'] ?? null;
    $ilanBulunduguKat = $_POST['ilanBulunduguKat'] ?? null;
    $ilanBinaKatSayisi = $_POST['ilanBinaKatSayisi'] ?? null;

    // Gerekli alanların dolu olup olmadığını kontrol et
    if (is_null($adresBaslik) || is_null($ilanUyeID) || is_null($ilanDurum) || is_null($ilanFiyat) || 
        is_null($ilanMetrekareBrut) || is_null($ilanMetrekareNet) || is_null($ilanOdaSayisi) || 
        is_null($ilanBinaYasi) || is_null($ilanSiteIcerisindeMi) || is_null($ilanMulkTuru) || 
        is_null($ilanKonum) || is_null($ilanIsitmaTipi) || is_null($ilanBulunduguKat) || 
        is_null($ilanBinaKatSayisi)) {
        echo "Lütfen tüm alanları doldurun.";
        exit();
    }

    // Adres bilgilerini veritabanına kaydet
    $adresSorgu = $baglan->prepare("INSERT INTO t_adresler (adresBaslik, adresMahalle, adresIlce, adresSehir, adresUlke, adresPostaKodu, adresEklenmeTarihi) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $adresSorgu->bind_param("sssssi", $adresBaslik, $adresMahalle, $adresIlce, $adresSehir, $adresUlke, $adresPostaKodu);
    $adresSorgu->execute();
    $adresID = $baglan->insert_id; // Eklenen adres ID'sini al

    // İlan bilgilerini veritabanına kaydet
    $ilanSorgu = $baglan->prepare("INSERT INTO t_ilanlar (ilanUyeID, ilanAdresID, ilanDurum, ilanYayinTarihi, ilanGuncellenmeTarihi) VALUES (?, ?, ?, NOW(), NOW())");
    $ilanSorgu->bind_param("ii", $ilanUyeID, $adresID, $ilanDurum);
    $ilanSorgu->execute();
    $ilanID = $baglan->insert_id; // Eklenen ilan ID'sini al

    // İlan detaylarını veritabanına kaydet
    $sorgu = $baglan->prepare("INSERT INTO t_ilandetay (ilanDFiyat, ilanDMetrekareBrut, ilanDMetrekareNet, ilanDOdaSayisi, ilanDBinaYasi, ilanDSiteIcerisindeMi, ilanDMulkTuru, ilanDKonumBilgisi, ilanDIsıtmaTipi, ilanDBulunduguKatSayisi, ilanDBinaKatSayisi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sorgu->bind_param("iiiiissssii", $ilanFiyat, $ilanMetrekareBrut, $ilanMetrekareNet, $ilanOdaSayisi, $ilanBinaYasi, $ilanSiteIcerisindeMi, $ilanMulkTuru, $ilanKonum, $ilanIsitmaTipi, $ilanBulunduguKat, $ilanBinaKatSayisi);
    $detaySorgu->execute();

    echo "İlan başarıyla eklendi.";
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
</head>
<body>
    <div class="container mt-5">
        <h2>İlan Ekle</h2>
        <form action="" method="POST" enctype="multipart/form-data">
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
            <div class="mb-3">
                <label for="ilanUyeID" class="form -label">Kullanıcı ID</label>
                <input type="number" class="form-control" id="ilanUyeID" name="ilanUyeID" required>
            </div>
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
            <button type="submit" class="btn btn-primary">İlan Ekle</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>