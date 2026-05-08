<?php
class Student
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(?int $class_id = null): array
    {
        $sql = "SELECT s.*, c.name as class_name 
                FROM students s 
                JOIN classes c ON s.class_id = c.id";
        $params = [];
        if ($class_id) {
            $sql .= " WHERE s.class_id = :cid";
            $params['cid'] = $class_id;
        }
        $sql .= " ORDER BY s.full_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, c.name as class_name 
            FROM students s 
            JOIN classes c ON s.class_id = c.id 
            WHERE s.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function add(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO students (full_name, date_naissance, photo, class_id) 
            VALUES (:name, :birth, :photo, :class)
        ");
        return $stmt->execute([
            'name' => $data['full_name'],
            'birth' => $data['date_naissance'],
            'photo' => $data['photo'],
            'class' => $data['class_id'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE students 
            SET full_name = :name, date_naissance = :birth, photo = :photo, class_id = :class 
            WHERE id = :id
        ");
        return $stmt->execute([
            'name' => $data['full_name'],
            'birth' => $data['date_naissance'],
            'photo' => $data['photo'],
            'class' => $data['class_id'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        // Récupérer la photo pour suppression
        $student = $this->getById($id);
        if ($student && $student['photo'] !== 'default.png') {
            $path = __DIR__ . '/../uploads/students/' . $student['photo'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getByClass(int $class_id): array
    {
        return $this->getAll($class_id); // alias
    }
}