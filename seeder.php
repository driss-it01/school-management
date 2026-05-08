<?php
require_once 'config.php';

$db = Database::getInstance()->getConnection();

// ========== Vider les tables ==========
$db->exec("SET FOREIGN_KEY_CHECKS=0;");
$db->exec("TRUNCATE TABLE absences;");
$db->exec("TRUNCATE TABLE grades;");
$db->exec("TRUNCATE TABLE students;");
$db->exec("TRUNCATE TABLE users;");
$db->exec("TRUNCATE TABLE classes;");
$db->exec("TRUNCATE TABLE subjects;");
$db->exec("SET FOREIGN_KEY_CHECKS=1;");

// ========== Matières ==========
$db->exec("INSERT INTO subjects (name, coefficient, type) VALUES
('Physique', 7, 'scientifique'),
('Math', 7, 'scientifique'),
('SVT', 7, 'scientifique'),
('Arabe', 4, 'litteraire'),
('Français', 4, 'litteraire'),
('Anglais', 4, 'litteraire'),
('Philosophie', 4, 'litteraire')");

// ========== Classes ==========
$db->exec("INSERT INTO classes (name) VALUES
('1BAC-SP-A'),('1BAC-SP-B'),('1BAC-SP-C'),('1BAC-SP-D')");

// ========== Idara — FIX: prepare() au lieu de exec() ==========
$stmt = $db->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:name, :email, :password, 'idara')");
$stmt->execute([
    'name'     => 'Administrateur',
    'email'    => 'admin@school.ma',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
]);

// ========== Profs — 1 par matière ==========
$profData = [
    ['Ahmed El Amrani',   'physique@school.ma',    1],
    ['Fatima Benali',     'math@school.ma',         2],
    ['Youssef Ouazzani',  'svt@school.ma',          3],
    ['Khadija Doukkali',  'arabe@school.ma',        4],
    ['Mohammed Fassi',    'francais@school.ma',     5],
    ['Samira Tazi',       'anglais@school.ma',      6],
    ['Hassan Berrada',    'philosophie@school.ma',  7],
];

$passwordProf = password_hash('prof2026', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT INTO users (full_name, email, password, role, subject_id) VALUES (:name, :email, :password, 'prof', :subject_id)");

foreach ($profData as $p) {
    $stmt->execute([
        'name'       => $p[0],
        'email'      => $p[1],
        'password'   => $passwordProf,
        'subject_id' => $p[2],
    ]);
}

// ========== Students — 5 par classe ==========
$firstNames = ['Youssef','Nour','Imane','Rachid','Amina','Mehdi','Sara','Khalid','Houda','Omar',
               'Soukaina','Adil','Meryem','Hamza','Najat','Anas','Hajar','Tariq','Salma','Walid'];
$lastNames  = ['Alaoui','Benjelloun','El Idrissi','Daoudi','Tazi','Ouazzani','Berrada','Amrani','Fassi','Doukkali',
               'Chraibi','Lahlou','Guessous','Slaoui','Cherkaoui','El Amrani','Bennis','Senhaji','Lamrani','Benchekroun'];

$stmt = $db->prepare("INSERT INTO students (full_name, date_naissance, photo, class_id) VALUES (:name, :birth, 'default.png', :class_id)");
$i = 0;
foreach ([1,2,3,4] as $classId) {
    for ($j = 0; $j < 5; $j++) {
        $year  = rand(2005, 2007);
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day   = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $stmt->execute([
            'name'     => $firstNames[$i] . ' ' . $lastNames[$i],
            'birth'    => "$year-$month-$day",
            'class_id' => $classId,
        ]);
        $i++;
    }
}

// ========== Notes — chaque étudiant × chaque matière ==========
$students = $db->query("SELECT id FROM students")->fetchAll(PDO::FETCH_COLUMN);
$profs    = $db->query("SELECT id, subject_id FROM users WHERE role='prof'")->fetchAll();

$stmt = $db->prepare("INSERT INTO grades (student_id, subject_id, prof_id, note, date) VALUES (?, ?, ?, ?, CURDATE())");
foreach ($students as $sid) {
    foreach ($profs as $prof) {
        $note = round(rand(80, 180) / 10, 1); // 8.0 à 18.0
        $stmt->execute([$sid, $prof['subject_id'], $prof['id'], $note]);
    }
}

// ========== Absences — aléatoires ==========
$subjects = $db->query("SELECT id FROM subjects")->fetchAll(PDO::FETCH_COLUMN);
$stmt = $db->prepare("INSERT INTO absences (student_id, subject_id, date, justified) VALUES (?, ?, DATE_SUB(CURDATE(), INTERVAL ? DAY), ?)");
foreach ($students as $sid) {
    if (rand(0, 100) < 40) {
        $stmt->execute([
            $sid,
            $subjects[array_rand($subjects)],
            rand(1, 30),
            rand(0, 1) ? 'oui' : 'non',
        ]);
    }
}

echo "<h2>✅ Base de données remplie avec succès !</h2>";
echo "<p>👤 <strong>Idara :</strong> admin@school.ma / admin123</p>";
echo "<p>👩‍🏫 <strong>Profs :</strong></p><ul>";
foreach ($profData as $p) {
    echo "<li>{$p[1]} / prof2026</li>";
}
echo "</ul>";
echo "<p style='color:red'><strong>⚠️ Supprimez ce fichier après usage !</strong></p>";