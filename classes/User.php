<?php
class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login(string $email, string $password): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_subject'] = $user['subject_id'];
            return true;
        }
        return false;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) return null;
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch();
    }

    public function getAllProfs(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, s.name as subject_name 
            FROM users u 
            LEFT JOIN subjects s ON u.subject_id = s.id 
            WHERE u.role = 'prof'
            ORDER BY u.full_name
        ");
        return $stmt->fetchAll();
    }

    public function addProf(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, email, password, role, subject_id) 
            VALUES (:name, :email, :password, 'prof', :subject_id)
        ");
        return $stmt->execute([
            'name' => $data['full_name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'subject_id' => $data['subject_id'] ?: null,
        ]);
    }

    public function deleteProf(int $id): bool
    {
        // Vérifier que c'est bien un prof
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() !== 'prof') return false;
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}