<?php
// PETUGAS = master USER kasir & manajer  (tabel: users)
// Akun admin/owner TIDAK ditampilkan di sini dan tidak bisa diubah/dihapus dari halaman ini.
require_once 'database/connection.php';

class Petugas
{
    private $conn;
    public $bolehKosong = false;
    public $error = '';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function semua($role = '', $cari = '')
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT u.*, b.nama_cabang
             FROM users u
             LEFT JOIN branches b ON b.id = u.branch_id
             WHERE u.role <> 'admin'
               AND (? = '' OR u.role = ?)
               AND (u.nama LIKE ? OR u.email LIKE ?)
             ORDER BY u.id ASC"
        );
        $stmt->bind_param('ssss', $role, $role, $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cari($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ? AND role <> 'admin'");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Validasi umum: role hanya kasir/manajer, cabang wajib dipilih
    private function valid($role, $branch_id, $nama = '', $email = '', $password = null)
    {
        if (trim($nama) === '' || mb_strlen($nama) > 100) {
            $this->error = 'Nama wajib diisi (maksimal 100 karakter).';
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            $this->error = 'Format email tidak valid.';
            return false;
        }
        // password kosong saat ubah = tidak diganti; saat tambah wajib
        if (!($password === '' && $this->bolehKosong)) {
            if (strlen($password) < 8 || strlen($password) > 72) {
                $this->error = 'Password harus 8 sampai 72 karakter.';
                return false;
            }
        }
        if (!in_array($role, ['kasir', 'manajer'], true)) {
            $this->error = 'Role harus kasir atau manajer.';
            return false;
        }
        if ((int) $branch_id < 1) {
            $this->error = 'Kasir dan manajer wajib memilih cabang.';
            return false;
        }
        return true;
    }

    private function pesanDb(mysqli_sql_exception $ex)
    {
        if ($ex->getCode() === 1062) {
            return 'Email sudah dipakai user lain.';
        }
        if ($ex->getCode() === 1452) {
            return 'Cabang yang dipilih tidak valid.';
        }
        return 'Terjadi kesalahan database.';
    }

    public function tambah($nama, $email, $password, $role, $branch_id)
    {
        if (!$this->valid($role, $branch_id, $nama, $email, (string) $password)) {
            return false;
        }
        $branch_id = (int) $branch_id;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO users (nama, email, password, role, branch_id) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('ssssi', $nama, $email, $hash, $role, $branch_id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            $this->error = $this->pesanDb($ex);
            return false;
        }
    }

    // $password kosong = password lama tidak diubah
    public function ubah($id, $nama, $email, $password, $role, $branch_id)
    {
        $this->bolehKosong = true;
        if (!$this->valid($role, $branch_id, $nama, $email, (string) $password)) {
            return false;
        }
        $branch_id = (int) $branch_id;
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare(
                    "UPDATE users SET nama = ?, email = ?, password = ?, role = ?, branch_id = ?
                     WHERE id = ? AND role <> 'admin'"
                );
                $stmt->bind_param('ssssii', $nama, $email, $hash, $role, $branch_id, $id);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE users SET nama = ?, email = ?, role = ?, branch_id = ?
                     WHERE id = ? AND role <> 'admin'"
                );
                $stmt->bind_param('sssii', $nama, $email, $role, $branch_id, $id);
            }
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            $this->error = $this->pesanDb($ex);
            return false;
        }
    }

    public function hapus($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ? AND role <> 'admin'");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } catch (mysqli_sql_exception $ex) {
            // gagal karena petugas sudah punya transaksi
            return false;
        }
    }
}
