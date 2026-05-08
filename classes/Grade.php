<?php
class Grade
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByStudent(int $student_id): array
    {
        $stmt = $this->db->prepare("
            SELECT g.*, s.name as subject_name, s.coefficient, s.type 
            FROM grades g 
            JOIN subjects s ON g.subject_id = s.id 
            WHERE g.student_id = :sid
            ORDER BY s.name
        ");
        $stmt->execute(['sid' => $student_id]);
        return $stmt->fetchAll();
    }

    public function getByClassAndSubject(int $class_id, int $subject_id): array
    {
        $stmt = $this->db->prepare("
            SELECT st.id as student_id, st.full_name, g.note 
            FROM students st 
            LEFT JOIN grades g ON g.student_id = st.id AND g.subject_id = :subject_id 
            WHERE st.class_id = :class_id
            ORDER BY st.full_name
        ");
        $stmt->execute([
            'class_id' => $class_id,
            'subject_id' => $subject_id,
        ]);
        return $stmt->fetchAll();
    }

    public function saveOrUpdate(int $student_id, int $subject_id, int $prof_id, float $note): bool
    {
        // Vérifier si une note existe déjà
        $stmt = $this->db->prepare("
            SELECT id FROM grades 
            WHERE student_id = :sid AND subject_id = :subid
        ");
        $stmt->execute(['sid' => $student_id, 'subid' => $subject_id]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE grades SET note = :note, prof_id = :prof, date = CURDATE() 
                WHERE id = :id
            ");
            return $stmt->execute([
                'note' => $note,
                'prof' => $prof_id,
                'id' => $existing,
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO grades (student_id, subject_id, prof_id, note, date) 
                VALUES (:sid, :subid, :prof, :note, CURDATE())
            ");
            return $stmt->execute([
                'sid' => $student_id,
                'subid' => $subject_id,
                'prof' => $prof_id,
                'note' => $note,
            ]);
        }
    }

    public function calculateAverage(int $student_id): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(g.note * s.coefficient) / SUM(s.coefficient) as moyenne 
            FROM grades g 
            JOIN subjects s ON g.subject_id = s.id 
            WHERE g.student_id = :sid
        ");
        $stmt->execute(['sid' => $student_id]);
        $result = $stmt->fetch();
        return $result['moyenne'] ? round((float) $result['moyenne'], 2) : 0.0;
    }

    public static function getGradeLetter(float $moyenne): string
    {
        if ($moyenne >= 16) return 'Très Bien (A)';
        if ($moyenne >= 14) return 'Bien (B)';
        if ($moyenne >= 12) return 'Assez Bien (C)';
        if ($moyenne >= 10) return 'Passable (D)';
        return 'Insuffisant (F)';
    }
}