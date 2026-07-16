<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "Starting Matrix Seeding...\n";

// Disable foreign key checks for truncation
$conn->exec("SET FOREIGN_KEY_CHECKS = 0;");
$conn->exec("TRUNCATE TABLE mapping_rules;");
$conn->exec("TRUNCATE TABLE courses;");
$conn->exec("TRUNCATE TABLE departments;");
$conn->exec("TRUNCATE TABLE programmes;");
$conn->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "Tables truncated.\n";

// Insert Main Programme
$stmt = $conn->prepare("INSERT INTO programmes (name, description, cost, duration_weeks) VALUES (?, ?, ?, ?)");
$stmt->execute(["Metaserve Students' ICT Skills Liberation Programme", "Mandatory Computer Literacy and Skill Alignment", 15000.00, 12]);
$programme_id = $conn->lastInsertId();

$matrix = [
    "FACULTY OF ENGINEERING" => [
        "B.Eng. Electrical/Electronic Engineering" => ["AutoCAD Electrical", "MATLAB", "Proteus", "Multisim", "PLC Programming"],
        "B.Eng. Mechanical Engineering" => ["AutoCAD", "SolidWorks", "Fusion 360", "ANSYS"],
        "B.Eng. Civil Engineering" => ["AutoCAD Civil 3D", "Revit", "STAAD Pro", "ArcGIS"],
        "B.Eng. Agricultural & Bio-Resources Engineering" => ["AutoCAD", "GIS", "Precision Agriculture Tools", "IoT"]
    ],
    "FACULTY OF AGRICULTURE" => [
        "B.Sc. Soil Science" => ["GIS", "SPSS", "R", "Remote Sensing"],
        "B.Sc. Crop Production" => ["Farm Management Software", "GIS", "Drone Technology"],
        "B.Sc. Animal Science" => ["Livestock Management Systems", "Excel", "Data Analytics"],
        "B.Sc. Agricultural Economics & Extension" => ["SPSS", "STATA", "Excel", "Power BI"],
        "B.Sc. Home Economics" => ["Canva", "Digital Marketing", "E-commerce Tools"],
        "B.Sc. Agricultural Economics" => ["SPSS", "STATA", "Excel", "Power BI"]
    ],
    "FACULTY OF ARTS" => [
        "B.A. Theatre Arts" => ["Adobe Premiere Pro", "CapCut", "Audition", "OBS Studio"],
        "B.A. Languages & Linguistics" => ["AI Translation Tools", "NVivo", "Microsoft Office"],
        "B.A. French" => ["Translation Software", "Duolingo for Educators", "AI Language Tools"],
        "B.A. Christian Religious Studies" => ["Digital Publishing", "Research Databases", "Microsoft Office"],
        "B.A. Islamic Studies" => ["Digital Publishing", "Research Databases", "Microsoft Office"],
        "B.A. Arabic" => ["Translation Software", "AI Language Tools"],
        "B.A. English & Literary Studies" => ["Grammarly", "AI Writing Tools", "Digital Publishing"],
        "B.A. History & Diplomatic Studies" => ["Zotero", "Mendeley", "Digital Archives"],
        "B.A. Sharia" => ["Legal Research Tools", "Westlaw", "LexisNexis", "Microsoft Office"],
        "B.A. Christian Theology" => ["Digital Research Tools", "Reference Managers"],
        "B.A. Philosophy" => ["NVivo", "Zotero", "AI Research Tools"],
        "B.A. Religious Studies" => ["Research Databases", "Digital Publishing"],
        "B.A. African Traditional Religion" => ["Digital Documentation Tools", "Research Software"],
        "B.A. Classics" => ["Digital Humanities Tools", "Research Databases"]
    ],
    "FACULTY OF COMMUNICATION AND MEDIA STUDIES" => [
        "B.Sc. Advertisement" => ["Canva", "Adobe Photoshop", "Illustrator", "Meta Ads Manager"],
        "B.Sc. Broadcasting" => ["OBS Studio", "Adobe Premiere Pro", "Adobe Audition"],
        "B.Sc. Journalism & Media Studies" => ["WordPress", "Adobe InDesign", "Canva", "Blogging Platforms"],
        "B.Sc. Public Relations" => ["Social Media Management", "Canva", "Hootsuite", "CRM Tools"]
    ],
    "FACULTY OF COMPUTING" => [
        "B.Sc. Data Science" => ["Python", "R", "Power BI", "Tableau", "SQL"],
        "B.Sc. Information and Communication Technology" => ["Networking", "Cloud Computing", "Cybersecurity"],
        "B.Sc. Software Engineering" => ["Java", "Python", "GitHub", "DevOps", "Cloud Platforms"]
    ],
    "FACULTY OF HEALTH SCIENCES" => [
        "B.Sc. Human Anatomy" => ["3D Anatomy Software", "SPSS", "Medical Imaging Tools"],
        "B.Sc. Human Physiology" => ["SPSS", "Medical Research Software", "Data Analysis"],
        "B.RAD. Radiology" => ["PACS", "Radiology Information Systems (RIS)", "DICOM Software"],
        "B.Sc. Environmental Health" => ["Health Information Systems (HIS)", "GIS", "SPSS", "DHIS2"],
        "B.NSc. Nursing Science" => ["Electronic Medical Records (EMR)", "Telemedicine Tools", "Hospital Information Systems"],
        "B.Sc. Public Health" => ["SPSS", "DHIS2", "Epi Info", "GIS", "Power BI"],
        "B.MLS. Medical Laboratory Science" => ["Laboratory Information Management Systems (LIMS)", "SPSS", "Medical Diagnostics Software"]
    ],
    "FACULTY OF MANAGEMENT AND SOCIAL SCIENCES" => [
        "B.Sc. Banking and Finance" => ["Excel", "QuickBooks", "Sage", "Power BI"],
        "B.Sc. Economics" => ["STATA", "EViews", "SPSS", "Power BI"],
        "B.Sc. Accounting" => ["Excel", "QuickBooks", "Sage", "Tally ERP"],
        "B.Sc. Business Administration" => ["ERP Systems", "CRM Software", "Microsoft 365"],
        "B.Sc. Political Science" => ["SPSS", "NVivo", "GIS"],
        "B.Sc. Public Administration" => ["Excel", "e-Governance Systems", "Project Management Tools"],
        "B.Sc. Sociology" => ["SPSS", "NVivo", "SurveyCTO", "KoboToolbox"],
        "B.Sc. Geography" => ["ArcGIS", "QGIS", "Remote Sensing"]
    ],
    "FACULTY OF SCIENCE" => [
        "B.Sc. Meteorology & Climate Studies" => ["ArcGIS", "Python", "Climate Data Analysis Tools"],
        "B.Sc. Survey & Geo-Informatics" => ["ArcGIS", "QGIS", "AutoCAD", "GPS Technologies"],
        "B.Sc. Computer Science" => ["Programming", "Artificial Intelligence", "Cybersecurity"],
        "B.Sc. Statistics" => ["SPSS", "R", "Python", "Power BI"],
        "B.Sc. Mathematics" => ["MATLAB", "Python", "LaTeX"],
        "B.Sc. Physics" => ["MATLAB", "Python", "Simulation Software"],
        "B.Sc. Chemistry" => ["ChemDraw", "Origin", "SPSS"],
        "B.Sc. Biochemistry" => ["Bioinformatics Tools", "SPSS", "Molecular Analysis Software"],
        "B.Sc. Microbiology" => ["Laboratory Information Systems", "SPSS", "Bioinformatics Tools"]
    ],
    "FACULTY OF EDUCATION" => [
        "B.Ed. Special Needs Education" => ["Assistive Technologies", "Learning Management Systems"],
        "B.Ed. Entrepreneurship" => ["E-commerce", "Digital Marketing", "Business Analytics"],
        "B.Ed. Sustainable Development Studies" => ["GIS", "Data Visualization", "Project Management Software"],
        "B.Ed. Human Kinetics" => ["Sports Analytics Software", "Health Monitoring Apps"],
        "B.Ed. Health Education" => ["Health Information Systems", "SPSS"],
        "B.Ed. Educational Administration" => ["Microsoft Project", "Excel", "ERP Systems"],
        "B.Ed. Guidance and Counselling" => ["SPSS", "Survey Tools", "Data Management Systems"],
        "B.LIS. Library and Information Science" => ["Koha", "DSpace", "Digital Library Systems"]
    ],
    "FACULTY OF LAW" => [
        "LL.B. Law" => ["Legal Research Tools (Westlaw, LexisNexis)", "Legal Drafting Software", "Case Management Systems", "Microsoft Office 365", "AI Legal Research Tools", "E-Litigation Platforms"]
    ]
];

$globalSkills = [
    "Digital Literacy & Computer Appreciation",
    "Microsoft Office Suite (Word, Excel, PowerPoint)",
    "Internet Research & Information Management",
    "Artificial Intelligence Tools for Productivity",
    "Digital Communication & Collaboration Tools",
    "Cybersecurity Awareness",
    "Cloud Storage & Cloud Computing Fundamentals",
    "Professional CV and LinkedIn Development",
    "Entrepreneurship and Freelancing Platforms",
    "Digital Ethics and Data Privacy",
    "Hardware Repairs"
];

$courseCache = []; // name => id
$courseCounter = 1;

// Insert Courses (Skills) helper
function getOrCreateCourse($conn, $name, $programme_id, &$courseCache, &$courseCounter) {
    if (isset($courseCache[$name])) {
        return $courseCache[$name];
    }
    $code = "SKL-" . str_pad($courseCounter++, 3, "0", STR_PAD_LEFT);
    $stmt = $conn->prepare("INSERT INTO courses (programme_id, course_code, name, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$programme_id, $code, $name, "Practical application of $name"]);
    $id = $conn->lastInsertId();
    $courseCache[$name] = $id;
    return $id;
}

// 1. Map Global Skills (Priority 2)
$globalCourseIds = [];
foreach ($globalSkills as $gSkill) {
    $globalCourseIds[] = getOrCreateCourse($conn, $gSkill, $programme_id, $courseCache, $courseCounter);
}

// 2. Insert Departments & Mappings
$deptStmt = $conn->prepare("INSERT INTO departments (name, faculty) VALUES (?, ?)");
$mapStmt = $conn->prepare("INSERT INTO mapping_rules (department_id, course_id, priority_level) VALUES (?, ?, ?)");

$totalDepts = 0;
$totalMappings = 0;

foreach ($matrix as $faculty => $programmes) {
    foreach ($programmes as $deptName => $skills) {
        $deptStmt->execute([$deptName, $faculty]);
        $deptId = $conn->lastInsertId();
        $totalDepts++;
        
        // Map Global Skills to this Department (Priority 2)
        foreach ($globalCourseIds as $cid) {
            $mapStmt->execute([$deptId, $cid, 2]);
            $totalMappings++;
        }
        
        // Map Specific Skills to this Department (Priority 5 - Top Match)
        foreach ($skills as $skill) {
            $cid = getOrCreateCourse($conn, $skill, $programme_id, $courseCache, $courseCounter);
            // Check if mapping exists just in case
            try {
                $mapStmt->execute([$deptId, $cid, 5]);
                $totalMappings++;
            } catch (Exception $e) {} // ignore duplicate mappings if any skill repeats in the same dept
        }
    }
}

echo "Seeded 1 Programme.\n";
echo "Seeded $totalDepts Departments (Academic Programmes).\n";
echo "Seeded " . count($courseCache) . " Unique ICT Skills (Courses).\n";
echo "Created $totalMappings Alignment Mappings.\n";
echo "Done.\n";
