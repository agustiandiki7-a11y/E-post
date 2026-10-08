<?php
// PETUGAS = master USER: admin / kasir / manajer  (tabel: users)
require_once 'database/connection.php';

class Petugas
{
    private $conn;
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
             WHERE (? = '' OR u.role = ?) AND (u.nama LIKE ? OR u.email LIKE ?)
             ORDER BY u.id ASC"
        );
        $stmt->bind_param('ssss', $role, $role, $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cari($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function cabangUntuk($role, $branch_id)
    {
        // admin tidak terikat cabang
        if ($role === 'admin' || !$branch_id) {
            return null;
        }
        return (int) $branch_id;
    }

    public function tambah($nama, $email, $password, $role, $branch_id)
    {
        $branch_id = $this->cabangUntuk($role, $branch_id);
        if ($role !== 'admin' && $branch_id === null) {
            $this->error = 'Kasir dan manajer wajib memilih cabang.';
            return false;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO users (nama, email, password, role, branch_id) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('ssssi', $nama, $email, $hash, $role, $branch_id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            $this->error = 'Email sudah dipakai user lain.';
            return false;
        }
    }

    // $password kosong = password lama tidak diubah
    public function ubah($id, $nama, $email, $password, $role, $branch_id)
    {
        $branch_id = $this->cabangUntuk($role, $branch_id);
        if ($role !== 'admin' && $branch_id === null) {
            $this->error = 'Kasir dan manajer wajib memilih cabang.';
            return false;
        }
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare(
                    "UPDATE users SET nama = ?, email = ?, password = ?, role = ?, branch_id = ? WHERE id = ?"
                );
                $stmt->bind_param('ssssii', $nama, $email, $hash, $role, $branch_id, $id);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE users SET nama = ?, email = ?, role = ?, branch_id = ? WHERE id = ?"
                );
                $stmt->bind_param('sssii', $nama, $email, $role, $branch_id, $id);
            }
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            $this->error = 'Email sudah dipakai user lain.';
            return false;
        }
    }

    public function hapus($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            // gagal karena user sudah punya transaksi
            return false;
        }
    }
}
