<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Models/CourseMapping.php';

$enrollLink = BASE_URL . "register.php";
if (isset($_SESSION['user_id'])) {
    if (in_array($_SESSION['role_id'], [5, 6])) {
        $enrollLink = BASE_URL . "student/enroll.php";
    } else {
        $enrollLink = BASE_URL . "login.php"; 
    }
}
$courseModel = new CourseMapping();
// Custom method we need to add to CourseMapping or do a raw query here.
// Let's do a raw query for the display since it's a specific read-only view.
require_once __DIR__ . '/config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Fetch Global Skills
$globalStmt = $conn->query("SELECT DISTINCT c.name FROM courses c JOIN mapping_rules m ON c.id = m.course_id WHERE m.priority_level = 2 ORDER BY c.id ASC");
$globalSkills = $globalStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch Faculty -> Department -> Specific Skills
$matrixData = [];
$query = "SELECT d.faculty, d.name AS department, c.name AS skill 
          FROM departments d
          JOIN mapping_rules m ON d.id = m.department_id
          JOIN courses c ON m.course_id = c.id
          WHERE m.priority_level = 5
          ORDER BY d.faculty, d.name, c.name";
$stmt = $conn->query($query);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $matrixData[$row['faculty']][$row['department']][] = $row['skill'];
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5 mt-5 bg-light" style="min-height: 80vh;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary-light text-primary-custom px-3 py-2 rounded-pill mb-3">Taraba State University</span>
            <h2 class="text-dark fw-bold display-6 mb-3">Metaserve ICT Skills Alignment Matrix</h2>
            <p class="text-muted mx-auto" style="max-width: 800px; font-size: 1.1rem;">
                This matrix aligns academic programmes with industry-relevant ICT skills, software, applications, and technologies under the Metaserve Students' ICT Skills Liberation Programme.
            </p>
        </div>

        <div class="row gy-5">
            <div class="col-lg-8">
                <div class="accordion shadow-sm rounded-4 border-0 overflow-hidden" id="matrixAccordion">
                    <?php 
                    $index = 0;
                    foreach ($matrixData as $faculty => $departments): 
                        $collapseId = 'collapse' . $index;
                    ?>
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold fs-5 <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" style="background-color: white; color: var(--primary-color);">
                                <i class="fa-solid fa-building-columns me-3 opacity-50"></i> <?= htmlspecialchars($faculty) ?>
                            </button>
                        </h2>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#matrixAccordion">
                            <div class="accordion-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="w-50 px-4 py-3 border-0">Programme</th>
                                            <th class="px-4 py-3 border-0">ICT Skills / Software</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departments as $dept => $skills): ?>
                                        <tr>
                                            <td class="px-4 py-3 fw-bold text-dark border-light"><?= htmlspecialchars($dept) ?></td>
                                            <td class="px-4 py-3 border-light">
                                                <?php foreach ($skills as $skill): ?>
                                                    <span class="badge bg-light text-secondary-custom border me-1 mb-1 shadow-sm"><?= htmlspecialchars($skill) ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $index++;
                    endforeach; 
                    ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="clean-card p-4 shadow-lg border-0 rounded-4" style="background: linear-gradient(135deg, var(--primary-color) 0%, #111827 100%); position: sticky; top: 100px;">
                    <h4 class="text-white fw-bold mb-4 d-flex align-items-center"><i class="fa-solid fa-globe text-warning me-3"></i> Recommended Core Certifications</h4>
                    <p class="text-light opacity-75 small mb-4">Regardless of discipline, every student enrolled in the programme will receive comprehensive training in these global foundation skills:</p>
                    
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($globalSkills as $gSkill): ?>
                        <li class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-check text-warning small"></i>
                            </div>
                            <span class="text-white fw-medium" style="font-size: 0.95rem;"><?= htmlspecialchars($gSkill) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="mt-5 text-center">
                        <a href="<?= $enrollLink ?>" class="btn btn-warning fw-bold w-100 py-3 shadow">Enroll Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
