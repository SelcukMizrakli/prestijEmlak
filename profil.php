<?php
/* Üye giriş kontrolü yapılacak*/
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
            <h2>Kullanıcı Adı</h2>
            <p>E-posta: kullanici@example.com</p>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">Profili Düzenle</button>
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
                    <div class="col-md-4">
                        <div class="card">
                            <img src="https://via.placeholder.com/300x150" class="card-img-top" alt="İlan Resmi">
                            <div class="card-body">
                                <h5 class="card-title">İlan Başlığı</h5>
                                <p class="card-text">Açıklama: Kısa açıklama burada yer alır.</p>
                                <p class="card-text"><strong>Fiyat:</strong> 500,000 TL</p>
                                <a href="ilanDetay.php?id=1" class="btn btn-primary btn-sm">Detaylar</a>
                                <!-- İlan Düzenle Butonu -->
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editIlanModal" onclick="loadIlanData(1, 'İlan Başlığı', 'Kısa açıklama burada yer alır.', 500000)">Düzenle</button>
                            </div>
                        </div>
                    </div>
                    <!-- Diğer ilanlar burada listelenebilir -->
                </div>
            </div>

            <!-- Favorilerim -->
            <div class="tab-pane fade" id="favoriler" role="tabpanel">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <img src="https://via.placeholder.com/300x150" class="card-img-top" alt="Favori İlan Resmi">
                            <div class="card-body">
                                <h5 class="card-title">Favori İlan Başlığı</h5>
                                <p class="card-text">Açıklama: Kısa açıklama burada yer alır.</p>
                                <p class="card-text"><strong>Fiyat:</strong> 750,000 TL</p>
                                <a href="ilanDetay.php?id=2" class="btn btn-primary btn-sm">Detaylar</a>
                            </div>
                        </div>
                    </div>
                    <!-- Diğer favori ilanlar burada listelenebilir -->
                </div>
            </div>

            <!-- Mesajlar -->
            <div class="tab-pane fade" id="mesajlar" role="tabpanel">
                <div class="message-list">
                    <!-- Her bir konuşma için bir div -->
                    <div class="message-summary border p-3 mb-2" onclick="toggleMessageBox('sohbet1')">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Kullanıcı Adı 1</strong>
                            <small>12-04-2025 14:30</small>
                        </div>
                        <p>En son mesaj: Merhaba, ilan hakkında bilgi almak istiyorum.</p>
                    </div>
                    <div class="message-summary border p-3 mb-2" onclick="toggleMessageBox('sohbet2')">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Kullanıcı Adı 2</strong>
                            <small>11-04-2025 16:45</small>
                        </div>
                        <p>En son mesaj: İlan hala geçerli mi?</p>
                    </div>
                    <!-- Diğer konuşmalar burada listelenebilir -->
                </div>

                <!-- Siyah Opak Arka Plan -->
                <div id="overlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1040;"></div>

                <!-- Mesaj Kutuları -->
                <div id="sohbet1" class="message-box d-none border p-3 mt-3" style="height: 50vh; width: 60%; overflow-y: auto; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; z-index: 1050;">
                    <button class="btn-close" style="position: absolute; top: 15px; right: 15px; z-index: 1060;" onclick="closeMessageBox()"></button>
                    <div class="message-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Kullanıcı Adı 1</strong>
                            <small>12-04-2025 14:30</small>
                        </div>
                        <p>Merhaba, ilan hakkında bilgi almak istiyorum.</p>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Ben</strong>
                            <small>12-04-2025 14:35</small>
                        </div>
                        <p>Merhaba, ilan hala geçerli.</p>
                        <hr>
                        <!-- Diğer mesajlar burada listelenebilir -->
                    </div>
                    <form class="mt-3" style="position: absolute; bottom: 10px; width: 98%;">
                        <textarea class="form-control" rows="2" placeholder="Mesajınızı yazın..."></textarea>
                        <button type="submit" class="btn btn-primary mt-2">Gönder</button>
                    </form>
                </div>

                <div id="sohbet2" class="message-box d-none border p-3 mt-3" style="height: 50vh; width: 60%; overflow-y: auto; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; z-index: 1050;">
                    <button class="btn-close" style="position: absolute; top: 15px; right: 15px; z-index: 1060;" onclick="closeMessageBox()"></button>
                    <div class="message-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Kullanıcı Adı 2</strong>
                            <small>11-04-2025 16:45</small>
                        </div>
                        <p>İlan hala geçerli mi?</p>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Ben</strong>
                            <small>11-04-2025 16:50</small>
                        </div>
                        <p>Evet, ilan hala geçerli.</p>
                        <hr>
                        <!-- Diğer mesajlar burada listelenebilir -->
                    </div>
                    <form class="mt-3" style="position: absolute; bottom: 10px; width: 98%;">
                        <textarea class="form-control" rows="2" placeholder="Mesajınızı yazın..."></textarea>
                        <button type="submit" class="btn btn-primary mt-2">Gönder</button>
                    </form>
                </div>
            </div>

            <!-- Yönetim Paneli -->
            <div class="tab-pane fade" id="yonetim" role="tabpanel">
                <div class="container mt-3">
                    <h4>Üye Listesi</h4>
                    <div class="message-list">
                        <!-- Üye Bilgileri -->
                        <div class="message-summary border p-3 mb-2" onclick="loadUyeIlanlari(1, 'Kullanıcı Adı 1', 'kullanici1@example.com', 'Admin')">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Kullanıcı Adı 1</strong>
                                <small>kullanici1@example.com</small>
                            </div>
                            <p>Üye Yetkisi: Admin</p>
                        </div>
                        <div class="message-summary border p-3 mb-2" onclick="loadUyeIlanlari(2, 'Kullanıcı Adı 2', 'kullanici2@example.com', 'Kullanıcı')">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Kullanıcı Adı 2</strong>
                                <small>kullanici2@example.com</small>
                            </div>
                            <p>Üye Yetkisi: Kullanıcı</p>
                        </div>
                        <!-- Diğer üyeler burada listelenebilir -->
                    </div>
                </div>

                <!-- Üye İlanları ve Yetki Düzenleme Modal -->
                <div class="modal fade" id="uyeIlanlariModal" tabindex="-1" aria-labelledby="uyeIlanlariModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uyeIlanlariModalLabel">Üye Bilgileri ve İlanları</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Üye Bilgileri -->
                                <h6 id="uyeAdi"></h6>
                                <p id="uyeEmail"></p>
                                <div class="mb-3">
                                    <label for="uyeYetki" class="form-label">Yetki</label>
                                    <select class="form-select" id="uyeYetki" name="uyeYetki" required>
                                        <option value="Admin">Admin</option>
                                        <option value="Kullanıcı">Kullanıcı</option>
                                    </select>
                                    <button class="btn btn-primary btn-sm mt-2" onclick="guncelleYetki()">Yetki Güncelle</button>
                                </div>

                                <!-- Üye İlanları -->
                                <h6>Üyenin İlanları</h6>
                                <div id="uyeIlanlari" class="row">
                                    <!-- Üyenin ilanları dinamik olarak buraya yüklenecek -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profil Düzenleme Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Profili Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" value="Kullanıcı Adı">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" value="kullanici@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Tel. No</label>
                            <input type="tel" class="form-control" id="phone" value="+90 ">
                        </div>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- İlan Düzenleme Modal -->
    <div class="modal fade" id="editIlanModal" tabindex="-1" aria-labelledby="editIlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIlanModalLabel">İlanı Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <form id="editIlanForm" enctype="multipart/form-data">
                        <input type="hidden" id="ilanId" name="ilanId">

                        <!-- Başlık -->
                        <div class="mb-3">
                            <label for="ilanBaslik" class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="ilanBaslik" name="ilanBaslik" required>
                        </div>

                        <!-- Açıklama -->
                        <div class="mb-3">
                            <label for="ilanAciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="ilanAciklama" name="ilanAciklama" rows="3" required></textarea>
                        </div>

                        <!-- Fiyat -->
                        <div class="mb-3">
                            <label for="ilanFiyat" class="form-label">Fiyat</label>
                            <input type="number" class="form-control" id="ilanFiyat" name="ilanFiyat" required>
                        </div>

                        <!-- Metrekare -->
                        <div class="mb-3">
                            <label for="ilanMetrekare" class="form-label">Metrekare</label>
                            <input type="number" class="form-control" id="ilanMetrekare" name="ilanMetrekare" required>
                        </div>

                        <!-- Oda Sayısı -->
                        <div class="mb-3">
                            <label for="ilanOdaSayisi" class="form-label">Oda Sayısı</label>
                            <input type="number" class="form-control" id="ilanOdaSayisi" name="ilanOdaSayisi" required>
                        </div>

                        <!-- Resim Ekleme -->
                        <div class="mb-3">
                            <label for="ilanResimler" class="form-label">Resimler</label>
                            <input type="file" class="form-control" id="ilanResimler" name="ilanResimler[]" multiple accept="image/*">
                            <small class="form-text text-muted">Birden fazla resim seçmek için Ctrl veya Shift tuşunu kullanabilirsiniz.</small>
                        </div>

                        <!-- Mevcut Resimler -->
                        <div class="mb-3">
                            <label class="form-label">Mevcut Resimler</label>
                            <div id="mevcutResimler" class="d-flex flex-wrap">
                                <!-- Resimler dinamik olarak buraya yüklenecek -->
                            </div>
                        </div>

                        <!-- Konum -->
                        <div class="mb-3">
                            <label for="ilanKonum" class="form-label">Konum</label>
                            <input type="text" class="form-control" id="ilanKonum" name="ilanKonum" required>
                        </div>

                        <!-- Isıtma Tipi -->
                        <div class="mb-3">
                            <label for="ilanIsitma" class="form-label">Isıtma Tipi</label>
                            <select class="form-select" id="ilanIsitma" name="ilanIsitma" required>
                                <option value="Merkezi">Merkezi</option>
                                <option value="Doğalgaz">Doğalgaz</option>
                                <option value="Soba">Soba</option>
                                <option value="Yok">Yok</option>
                            </select>
                        </div>

                        <!-- Submit Butonu -->
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>
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
</body>

</html>