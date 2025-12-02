<?php
require 'koneksi.php';

// Ambil semua users untuk dropdown
$res = $mysqli->query("SELECT id, nim, nama, foto FROM users ORDER BY nama");
$users = $res->fetch_all(MYSQLI_ASSOC);

// pilih user id dari GET
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;
if (!$uid && count($users) > 0) {
    $uid = $users[0]['id'];
}

// tab yang aktif
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'biodata';
$allowedTabs = ['biodata', 'pendidikan', 'pengalaman', 'keahlian', 'publikasi'];
if (!in_array($tab, $allowedTabs)) $tab = 'biodata';

// fungsi bantu
function esc($s){ return htmlspecialchars($s); }
$profile = null;
if ($uid) {
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i',$uid);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // ambil biodata
    $biodata = [];
    $stmt = $mysqli->prepare("SELECT * FROM biodata WHERE nim = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $profile['nim']);
    $stmt->execute();
    $biodata = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // pendidikan
    $pendidikan = [];
    $stmt = $mysqli->prepare("SELECT * FROM pendidikan WHERE nim = ? ORDER BY tahun DESC");
    $stmt->bind_param('s', $profile['nim']);
    $stmt->execute();
    $pendidikan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // pengalaman
    $pengalaman = [];
    $stmt = $mysqli->prepare("SELECT * FROM pengalaman WHERE nim = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $profile['nim']);
    $stmt->execute();
    $pengalaman = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // keahlian
    $keahlian = [];
    $stmt = $mysqli->prepare("SELECT * FROM keahlian WHERE nim = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $profile['nim']);
    $stmt->execute();
    $keahlian = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // hobi (untuk aside)
    $hobi = [];
    $stmt = $mysqli->prepare("SELECT * FROM hobi WHERE nim = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $profile['nim']);
    $stmt->execute();
    $hobi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // footer
    $footer = null;
    $stmt = $mysqli->prepare("SELECT * FROM footer WHERE nim = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('s', $profile['nim']);
    $stmt->execute();
    $footer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Profil Mahasiswa</title>
  <style>
  /* ===== RESET & DASAR ===== */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  }
  
  body {
    background: linear-gradient(135deg, #16213e, #0f3460, #1a1a2e);
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  
  .container {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  
  /* ===== HEADER DENGAN FOTO ===== */
  .header {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 10px;
    margin: 10px;
  }
  
  .profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  .profile-photo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #3cb371;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }
  
  .header h1 {
    font-size: 1.8rem;
    font-weight: bold;
    color: #fff;
  }
  
  .header p {
    font-size: 0.95rem;
    opacity: 0.9;
    color: #fff;
  }
  
  /* ===== USER SELECTOR ===== */
  .user-selector {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin: 10px;
    border-radius: 10px;
  }
  
  .user-selector label {
    font-weight: 600;
    color: #fff;
  }
  
  .user-selector select {
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    font-size: 0.95rem;
    flex: 1;
    min-width: 200px;
    color: #fff;
  }
  
  .user-selector select option {
    background: #1a1a2e;
    color: #fff;
  }
  
  .user-selector a {
    padding: 8px 16px;
    background: #3cb371;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    margin-left: auto;
    transition: 0.3s;
  }
  
  .user-selector a:hover {
    background: #2e8b57;
  }
  
  /* ===== MAIN LAYOUT ===== */
  main {
    display: flex;
    flex: 1;
    padding: 20px;
    gap: 20px;
  }
  
  .main-layout {
    display: flex;
    flex: 1;
    gap: 20px;
    padding: 10px;
  }
  
  /* ===== NAV (Left Side) ===== */
  nav,
  .nav {
    flex: 1;
    background: rgba(255, 255, 255, 0.08);
    padding: 15px;
    border-radius: 10px;
    overflow-y: auto;
  }
  
  .profile-card {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  }
  
  .profile-photo-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #3cb371;
    margin: 0 auto 10px;
    display: block;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }
  
  .profile-card h3 {
    font-size: 1.1rem;
    color: #fff;
    margin-bottom: 5px;
  }
  
  .profile-card p {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
  }
  
  nav h2,
  .nav h2 {
    color: #3cb371;
    margin-bottom: 15px;
    text-align: center;
    font-size: 1.3rem;
  }
  
  nav ul,
  .nav-menu {
    list-style: none;
  }
  
  nav li,
  .nav-menu li {
    margin: 10px 0;
  }
  
  nav a,
  .nav-menu a {
    display: block;
    text-decoration: none;
    color: #fff;
    padding: 10px 12px;
    border-radius: 5px;
    transition: 0.3s;
    background: rgba(255, 255, 255, 0.05);
  }
  
  nav a:hover,
  .nav-menu a:hover {
    background: #3cb371;
  }
  
  nav a.active,
  .nav-menu a.active {
    background: #3cb371;
    font-weight: 600;
  }
  
  /* SECTION - 50% center */
  .section {
    grid-column: 2;
    grid-row: 2;
    padding: 30px;
    overflow-y: auto;
    border-left: 2px solid #e0e0e0;
    border-right: 2px solid #e0e0e0;
  }
  
  .section h2 {
    font-size: 1.6rem;
    color: #667eea;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #667eea;
  }
  
  .info-group {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #667eea;
  }
  
  .info-group strong {
    display: inline-block;
    width: 140px;
    color: #555;
    font-weight: 600;
  }
  
  .info-group span {
    color: #333;
  }
  
  .content-card {
    background: #f9f9f9;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
  }
  
  .content-card h4 {
    color: #667eea;
    font-size: 1.2rem;
    margin-bottom: 10px;
  }
  
  .content-card p {
    color: #555;
    line-height: 1.6;
  }
  
  .list-simple {
    list-style: none;
  }
  
  .list-simple li {
    background: #f9f9f9;
    padding: 15px;
    margin-bottom: 12px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
  }
  
  .list-simple li strong {
    color: #667eea;
    display: block;
    margin-bottom: 5px;
  }
  
  .no-data {
    text-align: center;
    padding: 50px 20px;
    color: #999;
    font-style: italic;
  }
  
  /* ASIDE - 25% right */
  .aside {
    grid-column: 3;
    grid-row: 2;
    background: #f9f9f9;
    border-left: 2px solid #e0e0e0;
    padding: 20px 15px;
    overflow-y: auto;
  }
  
  .aside h2 {
    font-size: 1.3rem;
    color: #667eea;
    margin-bottom: 15px;
    text-align: center;
  }
  
  .aside-content {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
  }
  
  .hobby-list {
    list-style: none;
  }
  
  .hobby-list li {
    padding: 8px 12px;
    margin-bottom: 8px;
    background: #f0f0f0;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #555;
  }
  
  /* FOOTER - 100% width */
  .footer {
    grid-column: 1 / -1;
    grid-row: 3;
    background: #2c3e50;
    color: white;
    padding: 25px 20px;
    text-align: center;
  }
  
  .footer-links {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
  }
  
  .footer-links a {
    color: #3498db;
    text-decoration: none;
    padding: 6px 12px;
    background: rgba(255,255,255,0.1);
    border-radius: 6px;
    font-size: 0.9rem;
  }
  
  .footer-links a:hover {
    background: rgba(255,255,255,0.2);
  }
  
  .footer-copyright {
    font-size: 0.85rem;
    opacity: 0.8;
  }
  
  /* ==============================
     MEDIA QUERIES - RESPONSIVE
     ============================== */
  
  /* SMARTPHONE (≤480px) */
  @media (max-width: 480px) {
    .profile-header {
      flex-direction: column;
      text-align: center;
    }
    
    .profile-photo {
      width: 70px;
      height: 70px;
    }
    
    .user-selector {
      flex-direction: column;
      align-items: stretch;
    }
    
    .user-selector select,
    .user-selector a {
      width: 100%;
      margin-left: 0;
    }
    
    main,
    .main-layout {
      flex-direction: column;
      padding: 10px;
      gap: 15px;
    }
    
    nav, section, aside,
    .nav, .section, .aside {
      width: 100%;
      flex: none;
    }
    
    nav, .nav {
      order: 1;
    }
    
    section, .section {
      order: 2;
    }
    
    aside, .aside {
      order: 3;
    }
    
    .profile-card {
      display: flex;
      align-items: center;
      text-align: left;
      gap: 15px;
    }
    
    .profile-photo-large {
      width: 60px;
      height: 60px;
      margin: 0;
    }
    
    .biodata-content p,
    .info-group {
      flex-direction: column;
      gap: 5px;
    }
    
    .biodata-content .label,
    .info-group strong {
      min-width: auto;
    }
    
    footer,
    .footer {
      padding: 1rem;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }
    
    .footer .center-copy {
      order: 1;
    }
    
    .footer .left {
      order: 2;
      text-align: center;
    }
    
    .footer .right {
      order: 3;
      text-align: center;
    }
    
    .footer-links {
      flex-direction: column;
      gap: 10px;
    }
  }
  
  /* TABLET PORTRAIT (481px–768px) */
  @media (min-width: 481px) and (max-width: 768px) {
    .profile-header {
      flex-direction: row;
      text-align: left;
    }
    
    main,
    .main-layout {
      flex-direction: column;
      gap: 15px;
    }
    
    /* NAV full width dengan menu horizontal */
    nav, .nav {
      width: 100%;
      order: 1;
      flex: none;
      display: flex;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.08);
    }
    
    nav ul,
    .nav-menu {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }
    
    nav li,
    .nav-menu li {
      margin: 0;
      flex: 1;
      min-width: 150px;
    }
    
    section, .section {
      width: 100%;
      order: 2;
      flex: none;
    }
    
    aside, .aside {
      width: 100%;
      order: 3;
      flex: none;
    }
  }
  
  /* TABLET LANDSCAPE (769px–1024px) */
  @media (min-width: 769px) and (max-width: 1024px) {
    main,
    .main-layout {
      flex-direction: row;
      flex-wrap: wrap;
    }
    
    /* NAV di kiri - 25% */
    nav, .nav {
      flex: 0 0 25%;
      width: 25%;
      order: 1;
    }
    
    /* SECTION di kanan NAV - 75% */
    section, .section {
      flex: 0 0 calc(75% - 20px);
      width: calc(75% - 20px);
      order: 2;
    }
    
    /* ASIDE di bawah - 100% */
    aside, .aside {
      flex: 0 0 100%;
      width: 100%;
      order: 3;
    }
  }
  
  /* DESKTOP (≥1025px) */
  @media (min-width: 1025px) {
    main,
    .main-layout {
      flex-direction: row;
    }
    
    nav, .nav {
      flex-basis: 25%;
    }
    
    section, .section {
      flex-basis: 50%;
    }
    
    aside, .aside {
      flex-basis: 25%;
    }
  }
  </style>
</head>
<body>

<div class="container">
  <!-- HEADER -->
  <div class="header">
    <div class="profile-header">
      <?php if ($profile && !empty($profile['foto']) && file_exists(__DIR__.'/uploads/'.$profile['foto'])): ?>
        <img src="<?php echo 'uploads/'.esc($profile['foto']); ?>" alt="Foto Profil" class="profile-photo">
      <?php else: ?>
        <img src="fallback-profile.png" alt="Foto Profil" class="profile-photo">
      <?php endif; ?>
      <div>
        <h1>Profil Mahasiswa</h1>
        <p>Sistem Informasi Profil dan Biodata</p>
      </div>
    </div>
  </div>
  
  <!-- User Selector -->
  <div class="user-selector">
    <label for="uid">Pilih Profil:</label>
    <select name="uid" id="uid" onchange="window.location.href='?uid='+this.value+'&tab=<?php echo $tab; ?>'">
      <?php foreach($users as $u): ?>
        <option value="<?php echo $u['id']; ?>" <?php echo ($u['id']==$uid)?'selected':''; ?>>
          <?php echo esc($u['nim'].' — '.$u['nama']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <a href="admin_personil.php">Admin Panel</a>
  </div>

  <!-- MAIN LAYOUT -->
  <main class="main-layout">
    <!-- NAV (Left 25%) -->
    <nav class="nav">
      <?php if ($profile): ?>
        <div class="profile-card">
          <?php if ($profile && !empty($profile['foto']) && file_exists(__DIR__.'/uploads/'.$profile['foto'])): ?>
            <img src="<?php echo 'uploads/'.esc($profile['foto']); ?>" alt="Foto" class="profile-photo-large">
          <?php else: ?>
            <img src="fallback-profile.png" alt="Foto" class="profile-photo-large">
          <?php endif; ?>
          <div>
            <h3><?php echo esc($profile['nama']); ?></h3>
            <p><?php echo esc($profile['nim']); ?></p>
          </div>
        </div>
      <?php endif; ?>
      
      <h2>NAV</h2>
      <ul class="nav-menu">
        <?php 
        $tabNames = [
          'biodata' => 'Biodata',
          'pendidikan' => 'Pendidikan',
          'pengalaman' => 'Pengalaman',
          'keahlian' => 'Keahlian',
          'publikasi' => 'Publikasi'
        ];
        foreach($allowedTabs as $t): 
        ?>
          <li>
            <a href="?uid=<?php echo $uid; ?>&tab=<?php echo $t; ?>" class="<?php echo ($t===$tab)?'active':''; ?>">
              <?php echo $tabNames[$t]; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>
    
    <!-- SECTION (Center 50%) -->
    <section class="section">
      <?php if (!$profile): ?>
        <div class="no-data">
          <h2>Tidak ada profil</h2>
          <p>Silakan pilih profil dari dropdown di atas.</p>
        </div>
      <?php else: ?>
        
        <!-- BIODATA TAB -->
        <?php if ($tab === 'biodata'): ?>
          <h2>Biodata</h2>
          
          <?php if (!empty($biodata)): ?>
            <div class="biodata-content">
              <?php 
              // Cari biodata dengan field standar
              $mainBio = null;
              foreach($biodata as $b) {
                if (stripos($b['judul'], 'biodata') !== false || stripos($b['judul'], 'data diri') !== false) {
                  $mainBio = $b;
                  break;
                }
              }
              
              if ($mainBio):
                // Parse biodata jika formatnya terstruktur
                $lines = explode("\n", $mainBio['isi']);
              ?>
                <div class="info-group">
                  <strong class="label">NIM:</strong> <span class="value"><?php echo esc($profile['nim']); ?></span>
                </div>
                <div class="info-group">
                  <strong class="label">Nama:</strong> <span class="value"><?php echo esc($profile['nama']); ?></span>
                </div>
                <?php foreach($lines as $line): 
                  $parts = explode(':', $line, 2);
                  if (count($parts) == 2):
                ?>
                  <div class="info-group">
                    <strong class="label"><?php echo esc(trim($parts[0])); ?>:</strong> 
                    <span class="value"><?php echo esc(trim($parts[1])); ?></span>
                  </div>
                <?php endif; endforeach; ?>
              <?php else: ?>
                <?php foreach($biodata as $b): ?>
                  <div class="content-card">
                    <h4><?php echo esc($b['judul']); ?></h4>
                    <p><?php echo nl2br(esc($b['isi'])); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="no-data">
              <p>Belum ada data biodata</p>
            </div>
          <?php endif; ?>
        
        <!-- PENDIDIKAN TAB -->
        <?php elseif ($tab === 'pendidikan'): ?>
          <h2>Pendidikan</h2>
          
          <?php if (!empty($pendidikan)): ?>
            <ul class="list-simple">
              <?php foreach($pendidikan as $p): ?>
                <li>
                  <strong><?php echo esc($p['institusi']); ?></strong>
                  <div><?php echo esc($p['jurusan']); ?></div>
                  <div style="font-size: 0.9rem; color: #666; margin-top: 5px;">
                    Tahun: <?php echo esc($p['tahun']); ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="no-data">
              <p>Belum ada data pendidikan</p>
            </div>
          <?php endif; ?>
        
        <!-- PENGALAMAN TAB -->
        <?php elseif ($tab === 'pengalaman'): ?>
          <h2>Pengalaman</h2>
          
          <?php if (!empty($pengalaman)): ?>
            <?php foreach($pengalaman as $pg): ?>
              <div class="content-card">
                <h4><?php echo esc($pg['judul']); ?></h4>
                <p><?php echo nl2br(esc($pg['isi'])); ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-data">
              <p>Belum ada data pengalaman</p>
            </div>
          <?php endif; ?>
        
        <!-- KEAHLIAN TAB -->
        <?php elseif ($tab === 'keahlian'): ?>
          <h2>Keahlian</h2>
          
          <?php if (!empty($keahlian)): ?>
            <ul class="list-simple">
              <?php foreach($keahlian as $k): ?>
                <li>
                  <strong><?php echo esc($k['judul']); ?></strong>
                  <div><?php echo esc($k['isi']); ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="no-data">
              <p>Belum ada data keahlian</p>
            </div>
          <?php endif; ?>
        
        <!-- PUBLIKASI TAB -->
        <?php elseif ($tab === 'publikasi'): ?>
          <h2>Publikasi</h2>
          <div class="no-data">
            <p>Fitur publikasi akan segera tersedia</p>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
    
    <!-- ASIDE (Right 25%) -->
    <aside class="aside">
      <h2>Hobi</h2>
      <div class="aside-content">
        <?php if (!empty($hobi)): ?>
          <ul class="hobby-list">
            <?php 
            $allHobbies = [];
            foreach($hobi as $h) {
              $items = preg_split('/[;|,]+/', $h['hobi']);
              foreach($items as $it) {
                if(trim($it) != '') {
                  $allHobbies[] = trim($it);
                }
              }
            }
            $allHobbies = array_unique($allHobbies);
            foreach($allHobbies as $hobby): 
            ?>
              <li><?php echo esc($hobby); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p style="text-align: center; color: rgba(255, 255, 255, 0.5); padding: 20px;">Belum ada hobi</p>
        <?php endif; ?>
      </div>
    </aside>
  </main>
  
  <!-- FOOTER (Full width) -->
  <footer class="footer">
    <?php if ($footer): ?>
      <div class="left">
        <?php if (!empty($footer['instagram'])): ?>
          Twitter: @akun
        <?php endif; ?>
      </div>
      
      <div class="center-copy">
        <p>© <?php echo !empty($footer['copyright_text']) ? esc($footer['copyright_text']) : 'Copyright 2020. All Rights Reserved'; ?></p>
      </div>
      
      <div class="right">
        <p>NAMA WEB</p>
        <p>Slogan/Keterangan</p>
      </div>
    <?php else: ?>
      <div class="center-copy" style="width: 100%;">
        <p>© <?php echo date('Y'); ?> Web Profil Mahasiswa</p>
      </div>
    <?php endif; ?>
  </footer>
</div>

</body>
</html>