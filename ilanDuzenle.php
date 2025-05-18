<?php
session_start();
include("ayar.php");
include("header.php");

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
    $_SESSION['hata'] = "Bu sayfayı görüntülemek için giriş yapmanız gerekiyor.";
    header("Location: girisYap.php");
    exit;
}

if (!isset($_GET['ilanID']) && !isset($_GET['id']) && !isset($_POST['ilanID'])) {
    error_log("İlanID eksik: " . print_r($_GET, true) . " " . print_r($_POST, true));
    $_SESSION['hata'] = "Geçersiz veya eksik ilan ID.";
    header("Location: profil.php");
    exit;
}

$ilanID = 0;
if (isset($_GET['ilanID']) && is_numeric($_GET['ilanID']) && intval($_GET['ilanID']) > 0) {
    $ilanID = intval($_GET['ilanID']);
} elseif (isset($_GET['id']) && is_numeric($_GET['id']) && intval($_GET['id']) > 0) {
    $ilanID = intval($_GET['id']);
} elseif (isset($_POST['ilanID']) && is_numeric($_POST['ilanID']) && intval($_POST['ilanID']) > 0) {
    $ilanID = intval($_POST['ilanID']);
} else {
    error_log("Geçersiz ilanID: " . print_r($_GET, true) . " " . print_r($_POST, true));
    $_SESSION['hata'] = "Geçersiz veya eksik ilan ID.";
    header("Location: profil.php");
    exit;
}

error_log("Alınan ilanID: " . $ilanID);

// Veritabanı bağlantısını kontrol edin
if ($baglan->connect_error) {
    error_log("Veritabanı bağlantı hatası: " . $baglan->connect_error);
    $_SESSION['hata'] = "Veritabanı bağlantı hatası.";
    header("Location: profil.php");
    exit;
}

// Sorguyu kontrol edin
$sorgu = $baglan->prepare("
    SELECT 
        il.*, 
        id.*, 
        mt.mulkTipiBaslik, 
        it.ilanTurAdi, 
        a.adresBaslik, 
        a.adresMahalle, 
        a.adresIlce, 
        a.adresSehir, 
        a.adresUlke, 
        a.adresPostaKodu
    FROM t_ilanlar il
    JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
    JOIN t_mulktipi mt ON id.ilanDMulkTipiID = mt.mulkTipID
    JOIN t_ilantur it ON id.ilanDIlanTurID = it.ilanTurID
    JOIN t_adresler a ON il.ilanAdresID = a.adresID
    WHERE il.ilanID = ?
");

if (!$sorgu) {
    error_log("Sorgu hazırlama hatası: " . $baglan->error);
    $_SESSION['hata'] = "Bir hata oluştu.";
    header("Location: profil.php");
    exit;
}

$sorgu->bind_param("i", $ilanID);
$sorgu->execute();
$result = $sorgu->get_result();

if (!$result) {
    error_log("Sorgu çalıştırma hatası: " . $baglan->error);
    $_SESSION['hata'] = "Bir hata oluştu.";
    header("Location: profil.php");
    exit;
}

if ($result->num_rows === 0) {
    error_log("İlan bulunamadı: ID = " . $ilanID);
    $_SESSION['hata'] = "İlan bulunamadı.";
    header("Location: profil.php");
    exit;
}

$ilan = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form gönderildi.");
    // Get ilanID from POST if available, else fallback to GET
    $ilanID = isset($_POST['ilanID']) ? intval($_POST['ilanID']) : $ilanID;

    $ilanBaslik = $_POST['ilanBaslik'];
    $ilanAciklama = $_POST['ilanAciklama'];
    $ilanFiyat = floatval($_POST['ilanFiyat']);
    $ilanMetrekareBrut = intval($_POST['ilanMetrekareBrut']);
    $ilanMetrekareNet = intval($_POST['ilanMetrekareNet']);
    $ilanOdaSayisi = intval($_POST['ilanOdaSayisi']);
    $ilanBinaYasi = intval($_POST['ilanBinaYasi']);
    $ilanSiteIcerisindeMi = isset($_POST['ilanSiteIcerisindeMi']) ? 1 : 0;
    $ilanMulkTipiID = intval($_POST['ilanMulkTipiID']);
    $ilanIsitmaTipi = $_POST['ilanIsitmaTipi'];
    $ilanBulunduguKat = intval($_POST['ilanBulunduguKat']);
    $ilanBinaKatSayisi = intval($_POST['ilanBinaKatSayisi']);
    $adresBaslik = $_POST['adresBaslik'];
    $adresMahalle = $_POST['adresMahalle'];
    $adresIlce = $_POST['adresIlce'];
    $adresSehir = $_POST['adresSehir'];
    $adresUlke = $_POST['adresUlke'];
    $adresPostaKodu = $_POST['adresPostaKodu'];

    // Güncelleme sorguları
    $adresGuncelle = $baglan->prepare("
        UPDATE t_adresler 
        SET adresBaslik = ?, adresMahalle = ?, adresIlce = ?, adresSehir = ?, adresUlke = ?, adresPostaKodu = ?, adresGuncellenmeTarihi = NOW()
        WHERE adresID = ?
    ");
    $adresGuncelle->bind_param("ssssssi", $adresBaslik, $adresMahalle, $adresIlce, $adresSehir, $adresUlke, $adresPostaKodu, $ilan['ilanAdresID']);
    if (!$adresGuncelle->execute()) {
        error_log("Adres güncelleme hatası: " . $adresGuncelle->error);
    }

    $ilanDetayGuncelle = $baglan->prepare("
        UPDATE t_ilandetay 
        SET ilanDAciklama = ?, ilanDFiyat = ?, ilanDmetreKareBrut = ?, ilanDmetreKareNet = ?, ilanDOdaSayisi = ?, ilanDBinaYasi = ?, ilanDSiteIcerisindeMi = ?, ilanDMulkTipiID = ?, ilanDIsitmaTipi = ?, ilanDBulunduguKatSayisi = ?, ilanDBinaKatSayisi = ?
        WHERE ilanDilanID = ?
    ");
    $ilanDetayGuncelle->bind_param("sdiisiisiii", $ilanAciklama, $ilanFiyat, $ilanMetrekareBrut, $ilanMetrekareNet, $ilanOdaSayisi, $ilanBinaYasi, $ilanSiteIcerisindeMi, $ilanMulkTipiID, $ilanIsitmaTipi, $ilanBulunduguKat, $ilanBinaKatSayisi, $ilanID);
    if (!$ilanDetayGuncelle->execute()) {
        error_log("İlan detay güncelleme hatası: " . $ilanDetayGuncelle->error);
    }

    $_SESSION['basari'] = "İlan başarıyla güncellendi.";
    header("Location: ilanDuzenle.php?ilanID=$ilanID");
    exit;
}

// Resim ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['yeniResim'])) {
    $resim = $_FILES['yeniResim'];
    if ($resim['error'] === UPLOAD_ERR_OK) {
        $dosyaAdi = basename($resim['name']);
        $hedefYol = "uploads/" . $dosyaAdi;

        // Resmi sunucuya yükle
        if (move_uploaded_file($resim['tmp_name'], $hedefYol)) {
            $resimEkle = $baglan->prepare("
                INSERT INTO t_resimler (resimIlanID, resimBaslik, resimUrl, resimDurum, resimEklenmeTarihi) 
                VALUES (?, ?, ?, 1, NOW())
            ");
            $resimEkle->bind_param("iss", $ilanID, $dosyaAdi, $hedefYol);
            $resimEkle->execute();
        }
    }
}

// Resim silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['silResimID'])) {
    $silResimID = intval($_POST['silResimID']);
    $resimSil = $baglan->prepare("DELETE FROM t_resimler WHERE resimID = ? AND resimIlanID = ?");
    $resimSil->bind_param("ii", $silResimID, $ilanID);
    $resimSil->execute();
}

// Resimleri getir
$resimSorgu = $baglan->prepare("SELECT * FROM t_resimler WHERE resimIlanID = ?");
$resimSorgu->bind_param("i", $ilanID);
$resimSorgu->execute();
$resimler = $resimSorgu->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>İlan Düzenle</h1>
    <?php if (isset($_SESSION['hata'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['hata']; unset($_SESSION['hata']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['basari'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['basari']; unset($_SESSION['basari']); ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" action="ilanDuzenle.php?ilanID=<?php echo $ilanID; ?>">
        <input type="hidden" name="ilanID" value="<?php echo $ilanID; ?>">

        <!-- Diğer form alanları -->
        <div class="mb-3">
            <label for="ilanAciklama" class="form-label">Açıklama</label>
            <textarea class="form-control" id="ilanAciklama" name="ilanAciklama" rows="4" required><?php echo htmlspecialchars($ilan['ilanDAciklama']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="ilanFiyat" class="form-label">Fiyat</label>
            <input type="number" class="form-control" id="ilanFiyat" name="ilanFiyat" value="<?php echo htmlspecialchars($ilan['ilanDFiyat']); ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="ilanMetrekareBrut" class="form-label">Metrekare (Brüt)</label>
                <input type="number" class="form-control" id="ilanMetrekareBrut" name="ilanMetrekareBrut" value="<?php echo htmlspecialchars($ilan['ilanDmetreKareBrut']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="ilanMetrekareNet" class="form-label">Metrekare (Net)</label>
                <input type="number" class="form-control" id="ilanMetrekareNet" name="ilanMetrekareNet" value="<?php echo htmlspecialchars($ilan['ilanDmetreKareNet']); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="ilanOdaSayisi" class="form-label">Oda Sayısı</label>
                <input type="number" class="form-control" id="ilanOdaSayisi" name="ilanOdaSayisi" value="<?php echo htmlspecialchars($ilan['ilanDOdaSayisi']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="ilanBinaYasi" class="form-label">Bina Yaşı</label>
                <input type="number" class="form-control" id="ilanBinaYasi" name="ilanBinaYasi" value="<?php echo htmlspecialchars($ilan['ilanDBinaYasi']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="adresBaslik" class="form-label">Adres Başlığı</label>
            <input type="text" class="form-control" id="adresBaslik" name="adresBaslik" value="<?php echo htmlspecialchars($ilan['adresBaslik']); ?>" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="adresMahalle" class="form-label">Mahalle</label>
                <input type="text" class="form-control" id="adresMahalle" name="adresMahalle" value="<?php echo htmlspecialchars($ilan['adresMahalle']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="adresIlce" class="form-label">İlçe</label>
                <input type="text" class="form-control" id="adresIlce" name="adresIlce" value="<?php echo htmlspecialchars($ilan['adresIlce']); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="adresSehir" class="form-label">Şehir</label>
                <input type="text" class="form-control" id="adresSehir" name="adresSehir" value="<?php echo htmlspecialchars($ilan['adresSehir']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="adresUlke" class="form-label">Ülke</label>
                <input type="text" class="form-control" id="adresUlke" name="adresUlke" value="<?php echo htmlspecialchars($ilan['adresUlke']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="adresPostaKodu" class="form-label">Posta Kodu</label>
            <input type="text" class="form-control" id="adresPostaKodu" name="adresPostaKodu" value="<?php echo htmlspecialchars($ilan['adresPostaKodu']); ?>" required>
        </div>

        <!-- Resim ekleme alanı -->
        <div class="mb-3">
            <label for="yeniResim" class="form-label">Yeni Resim Ekle</label>
            <input type="file" class="form-control" id="yeniResim" name="yeniResim">
        </div>

        <!-- Mevcut resimler -->
        <div class="mb-3">
            <label class="form-label">Mevcut Resimler</label>
            <div class="row">
                <?php while ($resim = $resimler->fetch_assoc()): ?>
                    <div class="col-md-3 text-center">
                        <img src="<?php echo $resim['resimUrl']; ?>" alt="Resim" class="img-thumbnail mb-2" style="max-height: 150px;">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="silResimID" value="<?php echo $resim['resimID']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Güncelle</button>
    </form>
</div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST verileri: " . print_r($_POST, true));
    error_log("FILES verileri: " . print_r($_FILES, true));
}
?>