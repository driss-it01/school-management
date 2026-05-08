<?php
require_once '../../config.php';
require '../../vendor/autoload.php';

$userObj = new User();
if (!$userObj->isLoggedIn() || $_SESSION['user_role'] !== 'idara') redirect('../../login.php');

$id = $_GET['id'] ?? null;
if (!$id) redirect('index.php');

$studentObj = new Student();
$student = $studentObj->getById($id);
if (!$student) redirect('index.php');

$gradeObj = new Grade();
$grades = $gradeObj->getByStudent($id);
$moyenne = $gradeObj->calculateAverage($id);
$gradeLetter = Grade::getGradeLetter($moyenne);
$status = $moyenne >= 10 ? 'Admis' : 'Non Admis';

$absenceObj = new Absence();
$counts = $absenceObj->countByStudent($id);

// Construction du HTML pour DOMPDF
$html = '
<style>
    body { font-family: Arial; font-size: 12px; }
    table { width: 100%%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 6px; text-align: center; }
    th { background: #1e40af; color: white; }
    .footer { background: #f1f5f9; }
</style>
<div style="text-align:center">
    <h2>Lycée Technique – Maroc</h2>
    <h3>Bulletin Scolaire 2024/2025</h3>
</div>
<p><strong>Nom :</strong> '.htmlspecialchars($student['full_name']).'</p>
<p><strong>Classe :</strong> '.htmlspecialchars($student['class_name']).'</p>
<p><strong>Date de naissance :</strong> '.htmlspecialchars($student['date_naissance']).'</p>
<hr>
<table>
    <thead>
        <tr><th>Matière</th><th>Coefficient</th><th>Note /20</th><th>Mention</th></tr>
    </thead>
    <tbody>';
foreach ($grades as $g) {
    $html .= '<tr>
        <td>'.htmlspecialchars($g['subject_name']).'</td>
        <td>'.$g['coefficient'].'</td>
        <td>'.number_format($g['note'],2).'</td>
        <td>'.Grade::getGradeLetter($g['note']).'</td>
    </tr>';
}
$html .= '</tbody>
    <tfoot class="footer">
        <tr>
            <td colspan="2"><strong>Moyenne Générale</strong></td>
            <td><strong>'.number_format($moyenne,2).'/20</strong></td>
            <td><strong>'.$gradeLetter.'</strong></td>
        </tr>
    </tfoot>
</table>
<p><strong>Résultat :</strong> '.$status.'</p>
<hr>
<h4>Absences</h4>
<p>Total : '.$counts['total'].' | Justifiées : '.$counts['justified'].' | Non justifiées : '.$counts['non_justified'].'</p>';

$dompdf = new \Dompdf\Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('bulletin_'.htmlspecialchars($student['full_name']).'.pdf', ['Attachment' => true]);