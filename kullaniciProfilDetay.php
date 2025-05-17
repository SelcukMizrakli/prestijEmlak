<?php
session_start();
include("ayar.php");

// Debug için session değerlerini kontrol et (geliştirme aşamasında kullanın)
// var_dump($_SESSION);

// Temel giriş kontrolü
if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
    header("Location: girisYap.php");
    exit;
}

// Admin yetkisi kontrolü
if (!isset($_SESSION['uyeYetkiID']) || $_SESSION['uyeYetkiID'] !== 1) {
    header("Location: index.php?error=yetkisiz");
    exit;
}

// URL'den üye ID'sini al
$uyeID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Üye bilgilerini çek
$uyeQuery = $baglan->prepare("
    SELECT u.*, y.yetkiAdi 
    FROM t_uyeler u 
    JOIN t_yetki y ON u.uyeYetkiID = y.yetkiID 
    WHERE u.uyeID = ?
");
$uyeQuery->bind_param("i", $uyeID);
$uyeQuery->execute();
$uye = $uyeQuery->get_result()->fetch_assoc();

if (!$uye) {
    header("Location: profil.php?tab=yonetim");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Üye Profil Detay - Prestij Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .profile-header {
            background-color: #004080;
            color: white;
            padding: 20px;
            border-radius: 8px;
        }

        .card {
            margin-bottom: 20px;
        }

        .btn-danger {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>

    <div class="container mt-5">
        <div class="profile-header">
            <div class="row">
                <div class="col-md-8">
                    <h2><?php echo htmlspecialchars($uye['uyeAd'] . ' ' . $uye['uyeSoyad']); ?></h2>
                    <p>E-posta: <?php echo htmlspecialchars($uye['uyeMail']); ?></p>
                    <p>Telefon: <?php echo htmlspecialchars($uye['uyeTelNo']); ?></p>
                    <p>Yetki: <?php echo htmlspecialchars($uye['yetkiAdi']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn <?php echo $uye['uyeAktiflikDurumu'] ? 'btn-danger' : 'btn-success'; ?>"
                        onclick="hesapDurumDegistir(<?php echo $uyeID; ?>, <?php echo $uye['uyeAktiflikDurumu']; ?>)">
                        <?php echo $uye['uyeAktiflikDurumu'] ? 'Hesabı Pasifleştir' : 'Hesabı Aktifleştir'; ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h3>Üyenin İlanları</h3>
            <div class="row">
                <?php
                $ilanlarQuery = $baglan->prepare("
                    SELECT i.*, id.*, 
                           (SELECT resimUrl FROM t_resimler WHERE resimIlanID = i.ilanID AND resimDurum = 1 LIMIT 1) as resimUrl
                    FROM t_ilanlar i
                    JOIN t_ilandetay id ON i.ilanID = id.ilanDilanID
                    WHERE i.ilanUyeID = ?
                ");
                $ilanlarQuery->bind_param("i", $uyeID);
                $ilanlarQuery->execute();
                $ilanlar = $ilanlarQuery->get_result();

                if ($ilanlar->num_rows > 0) {
                    while ($ilan = $ilanlar->fetch_assoc()) {
                        $resim = $ilan['resimUrl'] ?: 'default.jpg';
                        echo '
                        <div class="col-md-4">
                            <div class="card">
                                <img src="' . htmlspecialchars($resim) . '" class="card-img-top" alt="İlan Resmi" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($ilan['ilanDMulkTuru']) . '</h5>
                                    <p class="card-text">
                                        <strong>Fiyat:</strong> ' . number_format($ilan['ilanDFiyat'], 2) . ' TL<br>
                                        <strong>Konum:</strong> ' . htmlspecialchars($ilan['ilanDKonumBilgisi']) . '
                                    </p>
                                    <button class="btn btn-danger btn-sm" onclick="ilanKaldir(' . $ilan['ilanID'] . ')">İlanı Kaldır</button>
                                    <a href="ilanDetay.php?id=' . $ilan['ilanID'] . '" class="btn btn-primary btn-sm">İlanı Görüntüle</a>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="col-12"><p>Bu üyeye ait ilan bulunmamaktadır.</p></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function hesapDurumDegistir(uyeID, mevcutDurum) {
            if (!confirm(mevcutDurum ? 'Hesabı pasifleştirmek istediğinize emin misiniz?' : 'Hesabı aktifleştirmek istediğinize emin misiniz?')) {
                return;
            }

            fetch('uyeDurumGuncelle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `uyeID=${uyeID}&durum=${mevcutDurum ? 0 : 1}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu!');
                });
        }

        function ilanKaldir(ilanID) {
            if (!confirm('İlanı kaldırmak istediğinize emin misiniz?')) {
                return;
            }

            fetch('ilanKaldir.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ilanID=${ilanID}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu!');
                });
        }
    </script>

    <footer>
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
        <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>