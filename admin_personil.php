<?php
require 'koneksi.php';

// pastikan folder uploads ada
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755);

function esc($s){ return htmlspecialchars($s); }

// tab yang aktif
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
$allowedTabs = ['users','biodata','pendidikan','pengalaman','keahlian','hobi','footer'];
if (!in_array($tab, $allowedTabs)) $tab = 'users';

// action (add/edit/delete)
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

// handle POST (add / edit) per tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // determine which tab submit relates to
    $t = $_POST['tab'] ?? 'users';

    // common fields parsing
    if ($t === 'users') {
        $nim = trim($_POST['nim'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($nim === '' || $nama === '') $errors[] = "NIM dan Nama wajib diisi.";

        // file upload
        $foto_name = '';
        if (!empty($_FILES['foto']['name'])) {
            $orig = basename($_FILES['foto']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext,$allowed)) $errors[] = 'Format foto tidak diperbolehkan.';
            else {
                $foto_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','', $orig);
                move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto_name);
            }
        }

        if (empty($errors)) {
            if ($edit_id) {
                if ($foto_name) {
                    // hapus foto lama
                    $stmtF = $mysqli->prepare("SELECT foto FROM users WHERE id=?");
                    $stmtF->bind_param('i', $edit_id);
                    $stmtF->execute();
                    $old = $stmtF->get_result()->fetch_assoc();
                    $stmtF->close();
                    if ($old && !empty($old['foto']) && file_exists($uploadDir . $old['foto'])) @unlink($uploadDir . $old['foto']);

                    $stmt = $mysqli->prepare("UPDATE users SET nim=?, nama=?, foto=? WHERE id=?");
                    $stmt->bind_param('sssi', $nim, $nama, $foto_name, $edit_id);
                } else {
                    $stmt = $mysqli->prepare("UPDATE users SET nim=?, nama=? WHERE id=?");
                    $stmt->bind_param('ssi', $nim, $nama, $edit_id);
                }
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=users&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO users (nim,nama,foto) VALUES (?,?,?)");
                $stmt->bind_param('sss', $nim, $nama, $foto_name);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=users&msg=added");
                exit;
            }
        } else {
            $tab = 'users';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }

    // === BIODATA ===
    if ($t === 'biodata') {
        $nim = trim($_POST['nim'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($nim === '') $errors[] = "NIM wajib diisi.";
        if (empty($errors)) {
            if ($edit_id) {
                $stmt = $mysqli->prepare("UPDATE biodata SET nim=?, judul=?, isi=? WHERE id=?");
                $stmt->bind_param('sssi', $nim, $judul, $isi, $edit_id);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=biodata&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO biodata (nim,judul,isi) VALUES (?,?,?)");
                $stmt->bind_param('sss', $nim, $judul, $isi);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=biodata&msg=added");
                exit;
            }
        } else {
            $tab = 'biodata';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }

    // === PENDIDIKAN ===
    if ($t === 'pendidikan') {
        $nim = trim($_POST['nim'] ?? '');
        $institusi = trim($_POST['institusi'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $tahun = trim($_POST['tahun'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($nim === '') $errors[] = "NIM wajib diisi.";
        if (empty($errors)) {
            if ($edit_id) {
                $stmt = $mysqli->prepare("UPDATE pendidikan SET nim=?, institusi=?, jurusan=?, tahun=? WHERE id=?");
                $stmt->bind_param('ssssi', $nim, $institusi, $jurusan, $tahun, $edit_id);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=pendidikan&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO pendidikan (nim,institusi,jurusan,tahun) VALUES (?,?,?,?)");
                $stmt->bind_param('ssss', $nim, $institusi, $jurusan, $tahun);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=pendidikan&msg=added");
                exit;
            }
        } else {
            $tab = 'pendidikan';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }

    // === PENGALAMAN ===
    if ($t === 'pengalaman') {
        $nim = trim($_POST['nim'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($nim === '') $errors[] = "NIM wajib diisi.";
        if (empty($errors)) {
            if ($edit_id) {
                $stmt = $mysqli->prepare("UPDATE pengalaman SET nim=?, judul=?, isi=? WHERE id=?");
                $stmt->bind_param('sssi', $nim, $judul, $isi, $edit_id);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=pengalaman&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO pengalaman (nim,judul,isi) VALUES (?,?,?)");
                $stmt->bind_param('sss', $nim, $judul, $isi);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=pengalaman&msg=added");
                exit;
            }
        } else {
            $tab = 'pengalaman';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }

    // === KEAHLIAN ===
    if ($t === 'keahlian') {
        $nim = trim($_POST['nim'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($nim === '') $errors[] = "NIM wajib diisi.";
        if (empty($errors)) {
            if ($edit_id) {
                $stmt = $mysqli->prepare("UPDATE keahlian SET nim=?, judul=?, isi=? WHERE id=?");
                $stmt->bind_param('sssi', $nim, $judul, $isi, $edit_id);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=keahlian&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO keahlian (nim,judul,isi) VALUES (?,?,?)");
                $stmt->bind_param('sss', $nim, $judul, $isi);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=keahlian&msg=added");
                exit;
            }
        } else {
            $tab = 'keahlian';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }

    // === HOBI ===
    if ($t === 'hobi') {
        $nim = trim($_POST['nim'] ?? '');
        $h = trim($_POST['hobi'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($nim === '' || $h === '') $errors[] = "NIM dan Hobi wajib diisi.";
        if (empty($errors)) {
            if ($edit_id) {
                $stmt = $mysqli->prepare("UPDATE hobi SET nim=?, hobi=? WHERE id=?");
                $stmt->bind_param('ssi', $nim, $h, $edit_id);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=hobi&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO hobi (nim,hobi) VALUES (?,?)");
                $stmt->bind_param('ss', $nim, $h);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=hobi&msg=added");
                exit;
            }
        } else {
            $tab = 'hobi';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }

    // === FOOTER ===
    if ($t === 'footer') {
        $nim = trim($_POST['nim'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        $youtube = trim($_POST['youtube'] ?? '');
        $copyright_text = trim($_POST['copyright_text'] ?? '');
        $quotes = trim($_POST['quotes'] ?? '');
        $edit_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($nim === '') $errors[] = "NIM wajib diisi.";
        if (empty($errors)) {
            if ($edit_id) {
                $stmt = $mysqli->prepare("UPDATE footer SET nim=?, instagram=?, youtube=?, copyright_text=?, quotes=? WHERE id=?");
                $stmt->bind_param('sssssi', $nim, $instagram, $youtube, $copyright_text, $quotes, $edit_id);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=footer&msg=updated");
                exit;
            } else {
                $stmt = $mysqli->prepare("INSERT INTO footer (nim,instagram,youtube,copyright_text,quotes) VALUES (?,?,?,?,?)");
                $stmt->bind_param('sssss', $nim, $instagram, $youtube, $copyright_text, $quotes);
                $stmt->execute();
                $stmt->close();
                header("Location: admin_personil.php?tab=footer&msg=added");
                exit;
            }
        } else {
            $tab = 'footer';
            $action = ($edit_id ? 'edit' : 'add');
            $id = $edit_id;
        }
    }
}

// HANDLE DELETE actions (GET)
if ($action === 'delete' && $id) {
    // which table based on current tab
    if ($tab === 'users') {
        // hapus foto
        $stmtF = $mysqli->prepare("SELECT foto FROM users WHERE id=?");
        $stmtF->bind_param('i',$id);
        $stmtF->execute();
        $old = $stmtF->get_result()->fetch_assoc();
        $stmtF->close();
        if ($old && !empty($old['foto']) && file_exists($uploadDir . $old['foto'])) @unlink($uploadDir . $old['foto']);
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    } elseif ($tab === 'biodata') {
        $stmt = $mysqli->prepare("DELETE FROM biodata WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    } elseif ($tab === 'pendidikan') {
        $stmt = $mysqli->prepare("DELETE FROM pendidikan WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    } elseif ($tab === 'pengalaman') {
        $stmt = $mysqli->prepare("DELETE FROM pengalaman WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    } elseif ($tab === 'keahlian') {
        $stmt = $mysqli->prepare("DELETE FROM keahlian WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    } elseif ($tab === 'hobi') {
        $stmt = $mysqli->prepare("DELETE FROM hobi WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    } elseif ($tab === 'footer') {
        $stmt = $mysqli->prepare("DELETE FROM footer WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_personil.php?tab={$tab}&msg=deleted");
    exit;
}

// ambil data masing-masing tabel untuk listing
$all = [];
foreach ($allowedTabs as $t) {
    $q = $mysqli->query("SELECT * FROM {$t} ORDER BY created_at DESC");
    $all[$t] = $q ? $q->fetch_all(MYSQLI_ASSOC) : [];
}

// ambil satu row untuk edit jika action=edit
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $mysqli->prepare("SELECT * FROM {$tab} WHERE id = ?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Panel - Personil (CRUD Semua)</title>
<link rel="stylesheet" href="style.css" />
<style>
/* CSS khusus untuk admin panel */
.admin-container {
  max-width: 1300px;
  margin: 30px auto;
  padding: 25px;
  background: rgba(25, 27, 60, 0.85);
  border-radius: 16px;
  border: 1px solid rgba(170, 100, 255, 0.3);
  box-shadow: 0 0 35px rgba(90, 60, 200, 0.2);
  backdrop-filter: blur(15px);
}

.tab-nav {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(180, 120, 255, 0.25);
}

.tab-nav a {
  padding: 10px 18px;
  border-radius: 10px;
  text-decoration: none;
  background: rgba(255, 255, 255, 0.05);
  color: #dcd7ff;
  border: 1px solid rgba(170, 100, 255, 0.2);
  transition: all 0.3s ease;
  font-weight: 500;
}

.tab-nav a:hover {
  background: rgba(150, 90, 255, 0.3);
  color: #fff;
}

.tab-nav a.active {
  background: rgba(150, 60, 255, 0.5);
  color: #fff;
  font-weight: 600;
  box-shadow: 0 0 15px rgba(150, 60, 255, 0.5);
  border: 1px solid rgba(200, 150, 255, 0.4);
}

.tab-nav a.back-to-site {
  background: #0ea5a4;
  color: white;
  margin-left: auto;
  border: none;
}

.tab-nav a.back-to-site:hover {
  background: #0d9488;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 15px;
  background: rgba(30, 32, 70, 0.6);
  border-radius: 12px;
}

.toolbar h2 {
  color: #dfcaff;
  font-size: 1.5rem;
  margin: 0;
}

.btn-add {
  background: rgba(135, 67, 255, 0.8);
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s;
  border: 1px solid rgba(200, 150, 255, 0.3);
}

.btn-add:hover {
  background: rgba(150, 80, 255, 1);
  box-shadow: 0 0 15px rgba(135, 67, 255, 0.5);
}

.msg-box {
  padding: 15px;
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.4);
  border-radius: 10px;
  color: #a7f3d0;
  margin-bottom: 20px;
}

.err-box {
  padding: 15px;
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.4);
  border-radius: 10px;
  color: #fecaca;
  margin-bottom: 20px;
}

.admin-card {
  background: rgba(30, 32, 70, 0.6);
  padding: 25px;
  border-radius: 14px;
  border: 1px solid rgba(170, 100, 255, 0.2);
  margin-bottom: 25px;
}

.admin-card h3 {
  color: #dfcaff;
  margin-bottom: 20px;
  font-size: 1.4rem;
  border-bottom: 1px solid rgba(180, 120, 255, 0.2);
  padding-bottom: 10px;
}

.admin-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 900px) {
  .admin-form {
    grid-template-columns: 1fr;
  }
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #dcd7ff;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="file"],
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px 15px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(170, 100, 255, 0.3);
  border-radius: 8px;
  color: #fff;
  font-family: "Poppins", sans-serif;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input[type="text"]:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #8743ff;
  box-shadow: 0 0 10px rgba(135, 67, 255, 0.3);
}

.form-group textarea {
  min-height: 120px;
  resize: vertical;
}

.current-photo {
  margin-top: 10px;
  text-align: center;
}

.current-photo img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid rgba(135, 67, 255, 0.5);
  margin-top: 10px;
}

.btn-submit {
  background: rgba(135, 67, 255, 0.8);
  color: white;
  padding: 12px 30px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s;
  grid-column: span 2;
}

.btn-submit:hover {
  background: rgba(150, 80, 255, 1);
  box-shadow: 0 0 15px rgba(135, 67, 255, 0.5);
}

.btn-cancel {
  display: inline-block;
  margin-left: 15px;
  padding: 12px 25px;
  background: rgba(255, 255, 255, 0.05);
  color: #dcd7ff;
  border-radius: 8px;
  text-decoration: none;
  border: 1px solid rgba(170, 100, 255, 0.2);
  transition: all 0.3s;
}

.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.1);
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 10px;
  overflow: hidden;
}

.admin-table th {
  background: rgba(135, 67, 255, 0.3);
  color: #dfcaff;
  padding: 15px;
  text-align: left;
  font-weight: 600;
}

.admin-table td {
  padding: 15px;
  border-bottom: 1px solid rgba(170, 100, 255, 0.1);
  color: #dcdcdc;
}

.admin-table tr:hover {
  background: rgba(255, 255, 255, 0.03);
}

.table-photo {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid rgba(170, 100, 255, 0.3);
}

.table-actions {
  display: flex;
  gap: 10px;
}

.btn-edit {
  padding: 6px 12px;
  background: rgba(59, 130, 246, 0.3);
  color: #93c5fd;
  border-radius: 6px;
  text-decoration: none;
  border: 1px solid rgba(59, 130, 246, 0.4);
  font-size: 0.9rem;
  transition: all 0.3s;
}

.btn-edit:hover {
  background: rgba(59, 130, 246, 0.5);
  color: #fff;
}

.btn-delete {
  padding: 6px 12px;
  background: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  border-radius: 6px;
  text-decoration: none;
  border: 1px solid rgba(239, 68, 68, 0.4);
  font-size: 0.9rem;
  transition: all 0.3s;
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.5);
  color: #fff;
}

.no-data {
  text-align: center;
  padding: 30px;
  color: #9ca3af;
  font-style: italic;
}
</style>
</head>
<body>
<header class="site-header">
  <div class="header-inner">
    <img src="admin.jpeg" alt="Admin Logo" class="WIN2025">
    <div>
      <h1 class="site-title">Admin Panel</h1>
      <p class="site-sub">KELOLA DATA PROFIL</p>
    </div>
  </div>
</header>

<div class="admin-container">
  <div class="tab-nav">
    <?php foreach($allowedTabs as $t): ?>
      <a href="?tab=<?php echo $t; ?>" class="<?php echo ($t===$tab)?'active':''; ?>"><?php echo ucfirst($t); ?></a>
    <?php endforeach; ?>
    <a href="index.php" class="back-to-site">Lihat Frontend</a>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="msg-box">Data berhasil <?php echo esc($_GET['msg']); ?>.</div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="err-box">
      <ul>
        <?php foreach($errors as $e): ?>
          <li><?php echo esc($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- FORM & LIST berdasarkan tab -->
  <?php if ($tab === 'users'): ?>
    <div class="toolbar">
      <h2>Users</h2>
      <a href="?tab=users&action=add" class="btn-add">+ Tambah User</a>
    </div>

    <?php if ($action === 'add' || $action === 'edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah User' : 'Tambah User'; ?></h3>
        <form method="post" enctype="multipart/form-data" class="admin-form">
          <input type="hidden" name="tab" value="users">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama" value="<?php echo esc($editData['nama'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Foto Profile (jpg/png/webp)</label>
            <input type="file" name="foto" accept="image/*">
            <?php if (!empty($editData['foto'])): ?>
              <div class="current-photo">
                <p>Foto saat ini: <?php echo esc($editData['foto']); ?></p>
                <img src="<?php echo 'uploads/'.esc($editData['foto']); ?>" alt="foto">
              </div>
            <?php endif; ?>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit"><?php echo ($action==='edit') ? 'Simpan Perubahan' : 'Simpan'; ?></button>
            <a href="admin_personil.php?tab=users" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Users</h3>
      <?php if (!empty($all['users'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Nama</th>
              <th>Foto</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['users'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['nama']); ?></td>
                <td>
                  <?php if(!empty($r['foto']) && file_exists($uploadDir.$r['foto'])): ?>
                    <img src="<?php echo 'uploads/'.esc($r['foto']); ?>" alt="foto" class="table-photo">
                  <?php else: ?>
                    <span class="no-photo">-</span>
                  <?php endif; ?>
                </td>
                <td class="table-actions">
                  <a href="?tab=users&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=users&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus user ini?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data users.</p>
      <?php endif; ?>
    </div>

  <?php elseif ($tab === 'biodata'): ?>
    <div class="toolbar">
      <h2>Biodata</h2>
      <a href="?tab=biodata&action=add" class="btn-add">+ Tambah Biodata</a>
    </div>

    <?php if ($action==='add' || $action==='edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah Biodata' : 'Tambah Biodata'; ?></h3>
        <form method="post" class="admin-form">
          <input type="hidden" name="tab" value="biodata">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" value="<?php echo esc($editData['judul'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Isi</label>
            <textarea name="isi" rows="5"><?php echo esc($editData['isi'] ?? ''); ?></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="admin_personil.php?tab=biodata" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Biodata</h3>
      <?php if (!empty($all['biodata'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Judul</th>
              <th>Isi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['biodata'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['judul']); ?></td>
                <td><?php echo nl2br(esc($r['isi'])); ?></td>
                <td class="table-actions">
                  <a href="?tab=biodata&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=biodata&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data biodata.</p>
      <?php endif; ?>
    </div>

  <?php elseif ($tab === 'pendidikan'): ?>
    <div class="toolbar">
      <h2>Pendidikan</h2>
      <a href="?tab=pendidikan&action=add" class="btn-add">+ Tambah Pendidikan</a>
    </div>

    <?php if ($action==='add' || $action==='edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah Pendidikan' : 'Tambah Pendidikan'; ?></h3>
        <form method="post" class="admin-form">
          <input type="hidden" name="tab" value="pendidikan">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Institusi</label>
            <input type="text" name="institusi" value="<?php echo esc($editData['institusi'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Jurusan</label>
            <input type="text" name="jurusan" value="<?php echo esc($editData['jurusan'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Tahun</label>
            <input type="text" name="tahun" value="<?php echo esc($editData['tahun'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="admin_personil.php?tab=pendidikan" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Pendidikan</h3>
      <?php if (!empty($all['pendidikan'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Institusi</th>
              <th>Jurusan</th>
              <th>Tahun</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['pendidikan'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['institusi']); ?></td>
                <td><?php echo esc($r['jurusan']); ?></td>
                <td><?php echo esc($r['tahun']); ?></td>
                <td class="table-actions">
                  <a href="?tab=pendidikan&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=pendidikan&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data pendidikan.</p>
      <?php endif; ?>
    </div>

  <?php elseif ($tab === 'pengalaman'): ?>
    <div class="toolbar">
      <h2>Pengalaman</h2>
      <a href="?tab=pengalaman&action=add" class="btn-add">+ Tambah Pengalaman</a>
    </div>

    <?php if ($action==='add' || $action==='edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah Pengalaman' : 'Tambah Pengalaman'; ?></h3>
        <form method="post" class="admin-form">
          <input type="hidden" name="tab" value="pengalaman">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" value="<?php echo esc($editData['judul'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Isi</label>
            <textarea name="isi" rows="4"><?php echo esc($editData['isi'] ?? ''); ?></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="admin_personil.php?tab=pengalaman" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Pengalaman</h3>
      <?php if (!empty($all['pengalaman'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Judul</th>
              <th>Isi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['pengalaman'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['judul']); ?></td>
                <td><?php echo nl2br(esc($r['isi'])); ?></td>
                <td class="table-actions">
                  <a href="?tab=pengalaman&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=pengalaman&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data pengalaman.</p>
      <?php endif; ?>
    </div>

  <?php elseif ($tab === 'keahlian'): ?>
    <div class="toolbar">
      <h2>Keahlian</h2>
      <a href="?tab=keahlian&action=add" class="btn-add">+ Tambah Keahlian</a>
    </div>

    <?php if ($action==='add' || $action==='edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah Keahlian' : 'Tambah Keahlian'; ?></h3>
        <form method="post" class="admin-form">
          <input type="hidden" name="tab" value="keahlian">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" value="<?php echo esc($editData['judul'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Isi</label>
            <textarea name="isi" rows="3"><?php echo esc($editData['isi'] ?? ''); ?></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="admin_personil.php?tab=keahlian" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Keahlian</h3>
      <?php if (!empty($all['keahlian'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Judul</th>
              <th>Isi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['keahlian'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['judul']); ?></td>
                <td><?php echo esc($r['isi']); ?></td>
                <td class="table-actions">
                  <a href="?tab=keahlian&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=keahlian&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data keahlian.</p>
      <?php endif; ?>
    </div>

  <?php elseif ($tab === 'hobi'): ?>
    <div class="toolbar">
      <h2>Hobi</h2>
      <a href="?tab=hobi&action=add" class="btn-add">+ Tambah Hobi</a>
    </div>

    <?php if ($action==='add' || $action==='edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah Hobi' : 'Tambah Hobi'; ?></h3>
        <form method="post" class="admin-form">
          <input type="hidden" name="tab" value="hobi">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Hobi (pisah dengan ; ) *</label>
            <input type="text" name="hobi" value="<?php echo esc($editData['hobi'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="admin_personil.php?tab=hobi" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Hobi</h3>
      <?php if (!empty($all['hobi'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Hobi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['hobi'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['hobi']); ?></td>
                <td class="table-actions">
                  <a href="?tab=hobi&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=hobi&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data hobi.</p>
      <?php endif; ?>
    </div>

  <?php elseif ($tab === 'footer'): ?>
    <div class="toolbar">
      <h2>Footer</h2>
      <a href="?tab=footer&action=add" class="btn-add">+ Tambah Footer</a>
    </div>

    <?php if ($action==='add' || $action==='edit'): ?>
      <div class="admin-card">
        <h3><?php echo ($action==='edit') ? 'Ubah Footer' : 'Tambah Footer'; ?></h3>
        <form method="post" class="admin-form">
          <input type="hidden" name="tab" value="footer">
          <input type="hidden" name="id" value="<?php echo esc($editData['id'] ?? $id); ?>">
          
          <div class="form-group">
            <label>NIM *</label>
            <input type="text" name="nim" value="<?php echo esc($editData['nim'] ?? ''); ?>" required>
          </div>
          
          <div class="form-group">
            <label>Link Instagram</label>
            <input type="text" name="instagram" value="<?php echo esc($editData['instagram'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Link YouTube</label>
            <input type="text" name="youtube" value="<?php echo esc($editData['youtube'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Copyright Text</label>
            <input type="text" name="copyright_text" value="<?php echo esc($editData['copyright_text'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Quotes</label>
            <textarea name="quotes" rows="3"><?php echo esc($editData['quotes'] ?? ''); ?></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" class="btn-submit">Simpan</button>
            <a href="admin_personil.php?tab=footer" class="btn-cancel">Batal</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h3>Daftar Footer</h3>
      <?php if (!empty($all['footer'])): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Instagram</th>
              <th>Youtube</th>
              <th>Copyright</th>
              <th>Quotes</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($all['footer'] as $r): ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo esc($r['nim']); ?></td>
                <td><?php echo esc($r['instagram']); ?></td>
                <td><?php echo esc($r['youtube']); ?></td>
                <td><?php echo esc($r['copyright_text']); ?></td>
                <td><?php echo nl2br(esc($r['quotes'])); ?></td>
                <td class="table-actions">
                  <a href="?tab=footer&action=edit&id=<?php echo $r['id']; ?>" class="btn-edit">Edit</a>
                  <a href="?tab=footer&action=delete&id=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="no-data">Belum ada data footer.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<footer class="site-footer">
  <div class="footer-center">
    <p>Admin CRUD — <?php echo date('Y'); ?></p>
  </div>
</footer>

</body>
</html>