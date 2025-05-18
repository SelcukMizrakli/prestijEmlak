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

// İlan bilgilerini getir
$sorgu = $baglan->prepare("
    SELECT 
        il.*, 
        id.*, 
        mt.mulkTipiBaslik, 
        it.ilanTurAdi, 
        a.*
    FROM t_ilanlar il
    JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
    JOIN t_mulktipi mt ON id.ilanDMulkTipiID = mt.mulkTipID
    JOIN t_ilantur it ON id.ilanDIlanTurID = it.ilanTurID
    JOIN t_adresler a ON il.ilanAdresID = a.adresID
    WHERE il.ilanID = ?
");

$sorgu->bind_param("i", $ilanID);
$sorgu->execute();
$ilan = $sorgu->get_result()->fetch_assoc();

if (!$ilan) {
    $_SESSION['hata'] = "İlan bulunamadı.";
    header("Location: profil.php");
    exit;
}

// POST işlemi kontrolü - Resim silme işlemi için
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['silResimID'])) {
    $silResimID = intval($_POST['silResimID']);
    try {
        $resimSil = $baglan->prepare("DELETE FROM t_resimler WHERE resimID = ? AND resimIlanID = ?");
        $resimSil->bind_param("ii", $silResimID, $ilanID);
        $resimSil->execute();
        $_SESSION['basari'] = "Resim başarıyla silindi.";
    } catch (Exception $e) {
        $_SESSION['hata'] = "Resim silinirken bir hata oluştu.";
    }
    header("Location: ilanDuzenle.php?ilanID=" . $ilanID);
    exit;
}

// POST işlemi kontrolü - İlan güncelleme işlemi için
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ilanGuncelle'])) {
    try {
        $baglan->begin_transaction();

        // Adres güncelleme
        $adresGuncelle = $baglan->prepare("
            UPDATE t_adresler 
            SET adresBaslik = ?, 
                adresMahalle = ?, 
                adresIlce = ?, 
                adresSehir = ?, 
                adresUlke = ?, 
                adresPostaKodu = ?, 
                adresGuncellenmeTarihi = NOW()
            WHERE adresID = ?
        ");

        $adresGuncelle->bind_param(
            "ssssssi",
            $_POST['adresBaslik'],
            $_POST['adresMahalle'],
            $_POST['adresIlce'],
            $_POST['adresSehir'],
            $_POST['adresUlke'],
            $_POST['adresPostaKodu'],
            $ilan['adresID']
        );
        $adresGuncelle->execute();

        // İlan detay güncelleme
        $ilanDetayGuncelle = $baglan->prepare("
            UPDATE t_ilandetay 
            SET ilanDAciklama = ?,
                ilanDFiyat = ?,
                ilanDmetreKareBrut = ?,
                ilanDmetreKareNet = ?,
                ilanDOdaSayisi = ?,
                ilanDBinaYasi = ?
            WHERE ilanDilanID = ?
        ");

        $ilanDetayGuncelle->bind_param(
            "sddiiis",
            $_POST['ilanAciklama'],
            $_POST['ilanFiyat'],
            $_POST['ilanMetrekareBrut'],
            $_POST['ilanMetrekareNet'],
            $_POST['ilanOdaSayisi'],
            $_POST['ilanBinaYasi'],
            $ilanID
        );
        $ilanDetayGuncelle->execute();

        // Çoklu resim ekleme işlemi
        if (isset($_FILES['yeniResimler'])) {
            $resimler = $_FILES['yeniResimler'];
            $resimSayisi = count($resimler['name']);

            for ($i = 0; $i < $resimSayisi; $i++) {
                if ($resimler['error'][$i] === UPLOAD_ERR_OK) {
                    $dosyaAdi = uniqid() . '_' . basename($resimler['name'][$i]);
                    $hedefYol = "uploads/" . $dosyaAdi;

                    if (move_uploaded_file($resimler['tmp_name'][$i], $hedefYol)) {
                        $resimEkle = $baglan->prepare("
                            INSERT INTO t_resimler (resimIlanID, resimBaslik, resimUrl, resimDurum, resimEklenmeTarihi)
                            VALUES (?, ?, ?, 1, NOW())
                        ");
                        $resimEkle->bind_param("iss", $ilanID, $dosyaAdi, $hedefYol);
                        $resimEkle->execute();
                    }
                }
            }
        }

        $baglan->commit();
        $_SESSION['basari'] = "İlan başarıyla güncellendi.";
        header("Location: ilanDuzenle.php?ilanID=" . $ilanID);
        exit;
    } catch (Exception $e) {
        $baglan->rollback();
        $_SESSION['hata'] = "Güncelleme sırasında bir hata oluştu: " . $e->getMessage();
    }
}

// Mevcut resimleri getir
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .form-group {
            margin-bottom: 1rem;
        }

        .card {
            margin-bottom: 1rem;
        }

        .resim-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 5px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resimInput = document.querySelector('input[name="yeniResimler[]"]');
            const onizlemeAlani = document.createElement('div');
            onizlemeAlani.className = 'd-flex flex-wrap mt-2';
            resimInput.parentNode.appendChild(onizlemeAlani);

            let secilenDosyalar = []; // Seçilen dosyaları tutacak array

            resimInput.addEventListener('change', function(e) {
                // FileList'i Array'e çevir ve mevcut seçili dosyalara ekle
                secilenDosyalar = Array.from(this.files);
                resimOnizlemeGuncelle();
            });

            function resimOnizlemeGuncelle() {
                onizlemeAlani.innerHTML = '';
                secilenDosyalar.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'position-relative m-2';
                        div.innerHTML = `
                            <img src="${e.target.result}" class="resim-preview">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                    style="top: 5px; right: 5px;"
                                    onclick="resimKaldir(${index})">
                                ×
                            </button>
                        `;
                        onizlemeAlani.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });

                // FileList'i güncelle
                const yeniFileList = new DataTransfer();
                secilenDosyalar.forEach(file => yeniFileList.items.add(file));
                resimInput.files = yeniFileList.files;
            }

            // Global scope'a resimKaldir fonksiyonunu ekle
            window.resimKaldir = function(index) {
                secilenDosyalar.splice(index, 1);
                resimOnizlemeGuncelle();
            }
        });
    </script>
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>İlan Düzenle</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['hata'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['hata'];
                        unset($_SESSION['hata']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['basari'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['basari'];
                        unset($_SESSION['basari']); ?>
                    </div>
                <?php endif; ?>

                <!-- İlan Bilgileri Güncelleme Formu -->
                <form method="POST" enctype="multipart/form-data" id="ilanGuncelleForm">
                    <input type="hidden" name="ilanGuncelle" value="1">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>İlan Açıklaması</label>
                                <textarea name="ilanAciklama" class="form-control" rows="4" required><?php echo htmlspecialchars($ilan['ilanDAciklama']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Fiyat</label>
                                <input type="number" name="ilanFiyat" class="form-control" value="<?php echo htmlspecialchars($ilan['ilanDFiyat']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Brüt m²</label>
                                        <input type="number" name="ilanMetrekareBrut" class="form-control" value="<?php echo htmlspecialchars($ilan['ilanDmetreKareBrut']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Net m²</label>
                                        <input type="number" name="ilanMetrekareNet" class="form-control" value="<?php echo htmlspecialchars($ilan['ilanDmetreKareNet']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Oda Sayısı</label>
                                        <input type="number" name="ilanOdaSayisi" class="form-control" value="<?php echo htmlspecialchars($ilan['ilanDOdaSayisi']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bina Yaşı</label>
                                        <input type="number" name="ilanBinaYasi" class="form-control" value="<?php echo htmlspecialchars($ilan['ilanDBinaYasi']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Adres Başlığı</label>
                                <input type="text" name="adresBaslik" class="form-control" value="<?php echo htmlspecialchars($ilan['adresBaslik']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Mahalle</label>
                                <input type="text" name="adresMahalle" class="form-control" value="<?php echo htmlspecialchars($ilan['adresMahalle']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>İlçe</label>
                                <input type="text" name="adresIlce" class="form-control" value="<?php echo htmlspecialchars($ilan['adresIlce']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Şehir</label>
                                <input type="text" name="adresSehir" class="form-control" value="<?php echo htmlspecialchars($ilan['adresSehir']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Ülke</label>
                                <input type="text" name="adresUlke" class="form-control" value="<?php echo htmlspecialchars($ilan['adresUlke']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Posta Kodu</label>
                                <input type="text" name="adresPostaKodu" class="form-control" value="<?php echo htmlspecialchars($ilan['adresPostaKodu']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Yeni Resimler Ekle</label>
                        <input type="file" name="yeniResimler[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Birden fazla resim seçmek için CTRL tuşuna basılı tutarak seçim yapabilirsiniz.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Güncelle</button>
                    <a href="profil.php" class="btn btn-secondary">İptal</a>
                </form>

                <!-- Mevcut Resimler -->
                <div class="form-group mt-4">
                    <label>Mevcut Resimler</label>
                    <div class="d-flex flex-wrap">
                        <?php while ($resim = $resimler->fetch_assoc()): ?>
                            <div class="position-relative m-2">
                                <img src="<?php echo htmlspecialchars($resim['resimUrl']); ?>" class="resim-preview">
                                <!-- Ayrı Resim Silme Formu -->
                                <form method="POST" class="position-absolute" style="top: 5px; right: 5px;">
                                    <input type="hidden" name="silResimID" value="<?php echo $resim['resimID']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>