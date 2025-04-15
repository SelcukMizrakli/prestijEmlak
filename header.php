<!-- filepath: c:\xampp\htdocs\prestijemlak\header.php -->
<header>
    <div class="container" style="margin-bottom: -0.8%;">
        <div class="logo">Prestij Emlak</div>
        <nav>
            <ul>
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="ilanlar.php">İlanlar</a></li>
                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                <li><a href="iletisim.php">İletişim</a></li>
                <?php if (isset($_SESSION['giris']) && $_SESSION['giris']) { ?>
                    <li>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" style="color: white; background-color: #004080; border: none;" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['uyeAd']); ?> (<?php echo htmlspecialchars($_SESSION['uyeMail']); ?>)
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                                <li><a class="dropdown-item" href="favoriler.php">Favoriler</a></li>
                                <li><a class="dropdown-item" href="mesajlar.php">Mesajlar</a></li>
                                <li><a class="dropdown-item text-danger" href="cikisYap.php">Çıkış Yap</a></li>
                            </ul>
                        </div>
                    </li>
                <?php } else { ?>
                    <li><a href="girisYap.php">Giriş Yap</a></li>
                    <li><a href="girisYap.php">Kayıt Ol</a></li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</header>