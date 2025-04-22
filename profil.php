<?php
session_start();
include("ayar.php"); // Veritabanı bağlantısını dahil et

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['giris']) || !$_SESSION['giris']) {
    header("Location: girisYap.php"); // Giriş yapılmamışsa giriş sayfasına yönlendir
    exit;
}

// Kullanıcı bilgilerini session'dan al
$kullaniciID = $_SESSION['uyeID'];
$kullaniciAdi = $_SESSION['uyeAd'];
$kullaniciMail = $_SESSION['uyeMail'];
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil - Prestij Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .profile-header {
            background-color: #004080;
            color: white;
            padding: 20px;
            border-radius: 8px;
        }

        .profile-header h2 {
            margin-bottom: 10px;
        }

        .nav-tabs .nav-link.active {
            background-color: #004080;
            color: white;
        }

        .card img {
            height: 150px;
            object-fit: cover;
        }

        .message-container {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <div class="container mt-5">
        <!-- Kullanıcı Bilgileri -->
        <div class="profile-header text-center">
            <h2><?php echo htmlspecialchars($kullaniciAdi); ?></h2>
            <p>E-posta: <?php echo htmlspecialchars($kullaniciMail); ?></p>
        </div>

        <!-- Sekmeler -->
        <ul class="nav nav-tabs mt-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ilanlar-tab" data-bs-toggle="tab" data-bs-target="#ilanlar" type="button" role="tab">İlanlarım</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="favoriler-tab" data-bs-toggle="tab" data-bs-target="#favoriler" type="button" role="tab">Favorilerim</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mesajlar-tab" data-bs-toggle="tab" data-bs-target="#mesajlar" type="button" role="tab">Mesajlar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="yonetim-tab" data-bs-toggle="tab" data-bs-target="#yonetim" type="button" role="tab">Yönetim Paneli</button>
            </li>
        </ul>

        <!-- Sekme İçerikleri -->
        <div class="tab-content mt-3" id="profileTabsContent">
            <!-- İlanlarım -->
            <div class="tab-pane fade show active" id="ilanlar" role="tabpanel">
                <div class="row">
                    <?php
                    $query = $baglan->prepare("
                        SELECT il.ilanID, id.ilanDFiyat, id.ilanDMulkTuru, id.ilanDKonumBilgisi
                        FROM t_ilanlar il
                        JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
                        WHERE il.ilanUyeID = ?
                    ");
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">' . htmlspecialchars($row['ilanDMulkTuru']) . '</h5>
                                            <p class="card-text"><strong>Fiyat:</strong> ' . number_format($row['ilanDFiyat'], 2) . ' TL</p>
                                            <p class="card-text"><strong>Konum:</strong> ' . htmlspecialchars($row['ilanDKonumBilgisi']) . '</p>
                                            <a href="ilanDuzenle.php?id=' . $row['ilanID'] . '" class="btn btn-warning btn-sm">Düzenle</a>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    } else {
                        echo "<p>Henüz ilanınız bulunmamaktadır.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Favorilerim -->
            <div class="tab-pane fade" id="favoriler" role="tabpanel">
                <div class="row">
                    <?php
                    $query = $baglan->prepare("
                        SELECT id.ilanDFiyat, id.ilanDMulkTuru, id.ilanDKonumBilgisi
                        FROM t_favoriler f
                        JOIN t_ilandetay id ON f.favoriIlanID = id.ilanDilanID
                        WHERE f.favoriUyeID = ?
                    ");
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">' . htmlspecialchars($row['ilanDMulkTuru']) . '</h5>
                                            <p class="card-text"><strong>Fiyat:</strong> ' . number_format($row['ilanDFiyat'], 2) . ' TL</p>
                                            <p class="card-text"><strong>Konum:</strong> ' . htmlspecialchars($row['ilanDKonumBilgisi']) . '</p>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    } else {
                        echo "<p>Henüz favori ilanınız bulunmamaktadır.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Mesajlar -->
            <div class="tab-pane fade" id="mesajlar" role="tabpanel">
                <div class="message-list">
                    <?php
                    $query = $baglan->prepare("
                        SELECT m.mesajText, m.mesajOkunduDurumu, u.uyeMail
                        FROM t_mesajlar m
                        JOIN t_uyeler u ON m.mesajIletenID = u.uyeID
                        WHERE m.mesajAlanID = ?
                    ");
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $okunduDurumu = $row['mesajOkunduDurumu'] == 1 ? "Görüldü" : "Görülmedi";
                            echo '
                                <div class="message-summary border p-3 mb-2">
                                    <strong>' . htmlspecialchars($row['uyeMail']) . '</strong>
                                    <p>' . htmlspecialchars($row['mesajText']) . '</p>
                                    <small>' . $okunduDurumu . '</small>
                                </div>
                            ';
                        }
                    } else {
                        echo "<p>Henüz mesajınız bulunmamaktadır.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Yönetim Paneli -->
            <div class="tab-pane fade" id="yonetim" role="tabpanel">
                <div class="container mt-3">
                    <h4>Üye Listesi</h4>
                    <div class="message-list">
                        <?php
                        $query = $baglan->query("
                            SELECT u.uyeID, u.uyeMail, u.uyeTelNo, u.uyeYetkiID, y.yetkiAdi
                            FROM t_uyeler u
                            JOIN t_yetki y ON u.uyeYetkiID = y.yetkiID
                            WHERE u.uyeAktiflikDurumu = 1
                        ");

                        while ($row = $query->fetch_assoc()) {
                            echo '
                                <div class="message-summary border p-3 mb-2">
                                    <strong>' . htmlspecialchars($row['uyeMail']) . '</strong>
                                    <p>Telefon: ' . htmlspecialchars($row['uyeTelNo']) . '</p>
                                    <p>Yetki: ' . htmlspecialchars($row['yetkiAdi']) . '</p>
                                    <a href="uyeSil.php?id=' . $row['uyeID'] . '" class="btn btn-danger btn-sm">Sil</a>
                                    <a href="uyeYetkiDuzenle.php?id=' . $row['uyeID'] . '" class="btn btn-warning btn-sm">Yetki Düzenle</a>
                                </div>
                            ';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
        <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMessageBox(sohbetId) {
            // Tüm mesaj kutularını gizle
            document.querySelectorAll('.message-box').forEach(box => box.classList.add('d-none'));

            // Tıklanan mesajın kutusunu göster
            const messageBox = document.getElementById(sohbetId);
            messageBox.classList.remove('d-none');

            // Siyah opak arka planı göster
            document.getElementById('overlay').classList.remove('d-none');
        }

        function closeMessageBox() {
            // Tüm mesaj kutularını gizle
            document.querySelectorAll('.message-box').forEach(box => box.classList.add('d-none'));

            // Siyah opak arka planı gizle
            document.getElementById('overlay').classList.add('d-none');
        }

        // İlan verilerini modal içine yükleme
        function loadIlanData(id, baslik, aciklama, fiyat, metrekare, odaSayisi, konum, isitma, resimler) {
            document.getElementById('ilanId').value = id;
            document.getElementById('ilanBaslik').value = baslik;
            document.getElementById('ilanAciklama').value = aciklama;
            document.getElementById('ilanFiyat').value = fiyat;
            document.getElementById('ilanMetrekare').value = metrekare;
            document.getElementById('ilanOdaSayisi').value = odaSayisi;
            document.getElementById('ilanKonum').value = konum;
            document.getElementById('ilanIsitma').value = isitma;

            // Mevcut resimleri yükle
            const mevcutResimlerDiv = document.getElementById('mevcutResimler');
            mevcutResimlerDiv.innerHTML = ''; // Önceki resimleri temizle
            resimler.forEach(resim => {
                const img = document.createElement('img');
                img.src = resim.url;
                img.alt = 'İlan Resmi';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.marginRight = '10px';
                mevcutResimlerDiv.appendChild(img);
            });
        }

        // Form gönderme işlemi
        document.getElementById('editIlanForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Form verilerini al
            const formData = new FormData(this);

            // AJAX ile düzenleme işlemini gönder
            fetch('ilanDuzenle.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('İlan başarıyla güncellendi!');
                        location.reload(); // Sayfayı yenile
                    } else {
                        alert('Bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu.');
                });
        });

        // Üye ilanlarını yükleme
        function loadUyeIlanlari(uyeId, uyeAdi, uyeEmail, mevcutYetki) {
            // Üye bilgilerini modal içine yükle
            document.getElementById('uyeAdi').innerText = `Üye: ${uyeAdi}`;
            document.getElementById('uyeEmail').innerText = `E-posta: ${uyeEmail}`;
            document.getElementById('uyeYetki').value = mevcutYetki;

            // Üyenin ilanlarını yükle
            const ilanlarDiv = document.getElementById('uyeIlanlari');
            ilanlarDiv.innerHTML = ''; // Önceki ilanları temizle

            // Örnek ilanlar (AJAX ile sunucudan çekilebilir)
            const ilanlar = [{
                    id: 1,
                    baslik: 'İlan Başlığı 1',
                    fiyat: 500000
                },
                {
                    id: 2,
                    baslik: 'İlan Başlığı 2',
                    fiyat: 750000
                }
            ];

            ilanlar.forEach(ilan => {
                const ilanDiv = document.createElement('div');
                ilanDiv.className = 'col-md-4';
                ilanDiv.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${ilan.baslik}</h5>
                            <p class="card-text"><strong>Fiyat:</strong> ${ilan.fiyat} TL</p>
                            <button class="btn btn-danger btn-sm" onclick="kaldirIlan(${ilan.id})">Kaldır</button>
                        </div>
                    </div>
                `;
                ilanlarDiv.appendChild(ilanDiv);
            });

            // Modalı aç
            const modal = new bootstrap.Modal(document.getElementById('uyeIlanlariModal'));
            modal.show();
        }

        // İlan kaldırma
        function kaldirIlan(ilanId) {
            if (confirm('Bu ilanı kaldırmak istediğinize emin misiniz?')) {
                // AJAX ile ilan kaldırma işlemi yapılabilir
                alert(`İlan ${ilanId} kaldırıldı.`);
            }
        }

        // Üye yetkisini güncelleme
        function guncelleYetki() {
            const uyeYetki = document.getElementById('uyeYetki').value;

            // AJAX ile yetki güncelleme işlemi yapılabilir
            alert(`Üyenin yetkisi ${uyeYetki} olarak güncellendi.`);
        }

        // Üye yetkisini düzenleme
        document.getElementById('uyeYetkiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const uyeId = document.getElementById('uyeId').value;
            const uyeYetki = document.getElementById('uyeYetki').value;

            // AJAX ile yetki düzenleme işlemi yapılabilir
            alert(`Üye ${uyeId} yetkisi ${uyeYetki} olarak güncellendi.`);
        });

        // Üye yetkisini düzenleme modalını açma
        function openYetkiModal(uyeId, mevcutYetki) {
            document.getElementById('uyeId').value = uyeId;
            document.getElementById('uyeYetki').value = mevcutYetki;

            // Modalı aç
            const modal = new bootstrap.Modal(document.getElementById('uyeYetkiModal'));
            modal.show();
        }

        // Üye yetkisini düzenleme formunu gönderme
        document.getElementById('uyeYetkiForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const uyeId = document.getElementById('uyeId').value;
            const uyeYetki = document.getElementById('uyeYetki').value;

            // AJAX ile yetki düzenleme işlemi yapılabilir
            fetch('uyeYetkiDuzenle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `uyeId=${uyeId}&uyeYetki=${uyeYetki}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Üye yetkisi başarıyla güncellendi!');
                        location.reload(); // Sayfayı yenile
                    } else {
                        alert('Bir hata oluştu: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu.');
                });
        });
    </script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // URL'den parametreleri al
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');

            // Eğer "mesajlar" parametresi varsa, o sekmeyi aç
            if (tab === "mesajlar") {
                const tabButton = document.querySelector('button[data-bs-target="#mesajlar"]');
                if (tabButton) {
                    tabButton.click();
                }
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            // URL'den parametreleri al
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');

            // Eğer "mesajlar" parametresi varsa, o sekmeyi aç
            if (tab === "favoriler") {
                const tabButton = document.querySelector('button[data-bs-target="#favoriler"]');
                if (tabButton) {
                    tabButton.click();
                }
            }
        });
    </script>
</body>

</html>