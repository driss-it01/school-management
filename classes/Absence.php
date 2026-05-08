<?php
class Absence
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByStudent(int $student_id): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, s.name as subject_name 
            FROM absences a 
            JOIN subjects s ON a.subject_id = s.id 
            WHERE a.student_id = :sid 
            ORDER BY a.date DESC
        ");
        $stmt->execute(['sid' => $student_id]);
        return $stmt->fetchAll();
    }

    public function getByClassAndDate(int $class_id, string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT st.id as student_id, st.full_name, 
                   a.subject_id, a.justified 
            FROM students st 
            LEFT JOIN absences a ON a.student_id = st.id AND a.date = :date 
            WHERE st.class_id = :cid
            ORDER BY st.full_name
        ");
        $stmt->execute(['cid' => $class_id, 'date' => $date]);
        return $stmt->fetchAll();
    }

    public function save(int $student_id, int $subject_id, string $date, string $justified): bool
    {
        // Empêcher les doublons : si déjà présent à cette date+matière, on ignore
        $stmt = $this->db->prepare("
            SELECT id FROM absences 
            WHERE student_id = :sid AND subject_id = :subid AND date = :date
        ");
        $stmt->execute(['sid' => $student_id, 'subid' => $subject_id, 'date' => $date]);
        if ($stmt->fetch()) return false;

        $stmt = $this->db->prepare("
            INSERT INTO absences (student_id, subject_id, date, justified) 
            VALUES (:sid, :subid, :date, :justified)
        ");
        return $stmt->execute([
            'sid' => $student_id,
            'subid' => $subject_id,
            'date' => $date,
            'justified' => $justified,
        ]);
    }

    public function countByStudent(int $student_id): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN justified = 'oui' THEN 1 ELSE 0 END) as justified,
                SUM(CASE WHEN justified = 'non' THEN 1 ELSE 0 END) as non_justified
            FROM absences 
            WHERE student_id = :sid
        ");
        $stmt->execute(['sid' => $student_id]);
        return $stmt->fetch();
    }
}