<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<h1>Aspirasi Warga</h1>

<section class="panel">
  <h2>Daftar Aspirasi</h2>
  <table>
    <thead>
      <tr><th>Tanggal</th><th>Nama</th><th>Kontak</th><th>Kategori</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><?= rw_esc(date('d/m/Y H:i', strtotime($row['created_at']))) ?></td>
        <td><?= rw_esc($row['nama']) ?><br><small>RT <?= rw_esc($row['rt']) ?></small></td>
        <td><?= rw_esc($row['no_hp']) ?></td>
        <td><?= rw_esc($row['kategori']) ?></td>
        <td><?= nl2br(rw_esc($row['pesan'])) ?></td>
        <td>
          <form method="post" action="<?= site_url('admin/aspirasi') ?>" class="inline-form">
            <input type="hidden" name="id" value="<?= rw_esc($row['id']) ?>">
            <select name="status">
              <option value="baru" <?= is_selected($row['status'], 'baru') ?>>Baru</option>
              <option value="diproses" <?= is_selected($row['status'], 'diproses') ?>>Diproses</option>
              <option value="selesai" <?= is_selected($row['status'], 'selesai') ?>>Selesai</option>
            </select>
            <textarea name="catatan_admin" rows="2" placeholder="Catatan admin"><?= rw_esc($row['catatan_admin']) ?></textarea>
            <button type="submit">Update</button>
          </form>
        </td>
        <td><a href="<?= site_url('admin/aspirasi?action=delete&id=' . $row['id']) ?>" onclick="return confirm('Hapus aspirasi ini?')">Hapus</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?>
      <tr><td colspan="7" class="table-empty">Belum ada aspirasi masuk.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</section>
<?= $this->endSection() ?>
