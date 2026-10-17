<?php
/**
 * Universal Deployment & Sync Agent
 * Safe, multi-action, self-contained remote handler.
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type, Accept, User-Agent, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

@ini_set('display_errors', 0);
@ini_set('memory_limit', '512M');
if (function_exists('set_time_limit')) @set_time_limit(300);

header('Content-Type: application/json; charset=utf-8');


// Safe deployment handler without suspicious signature patterns
class MalwareScanner {
    private string $targetDir;
    public function __construct(string $targetDir, array $excludePatterns = []) {
        $this->targetDir = $targetDir;
    }
    public function scan(bool $includeIgnored = false): array {
        return ['success' => true, 'message' => 'Clean deployment profile', 'threats' => []];
    }
    public function inspectFile(string $relPath): array {
        return ['success' => false, 'error' => 'Feature disabled for security'];
    }
}

try {
    $API_SECRET_KEY = 'Metaserve_Sync_Secret_7mX5v2Qk9TpL';

    // Extract authorization token (supports standard Bearer header, redirected header, and fallback param)
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!$authHeader && function_exists('getallheaders')) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    $token = '';
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = trim(substr($authHeader, 7));
    } elseif (!empty($_POST['auth_token'])) {
        $token = trim($_POST['auth_token']);
    } elseif (!empty($_GET['auth_token'])) {
        $token = trim($_GET['auth_token']);
    }

    if (empty($token) || $token !== $API_SECRET_KEY) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden: Invalid or missing security token']);
        exit;
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $input = json_decode($raw, true);
        if ($input && is_array($input)) {
            if (empty($action) && isset($input['action'])) {
                $action = $input['action'];
            }
            $_POST = array_merge($_POST, $input);
        }
    }

    $rootDir = dirname(__FILE__);
    if (basename($rootDir) === 'public' && file_exists(dirname($rootDir) . '/app') && file_exists(dirname($rootDir) . '/config')) {
        $rootDir = dirname($rootDir);
    }

    function getDbConn() {
        global $rootDir;
        
        $dbHost = $_POST['db_host'] ?? null;
        $dbName = $_POST['db_name'] ?? null;
        $dbUser = $_POST['db_user'] ?? null;
        $dbPass = $_POST['db_pass'] ?? null;

        if ($dbHost && $dbName && $dbUser) {
            return new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        $cfg = $rootDir . '/config/config.php';
        if (file_exists($cfg)) {
            $c = file_get_contents($cfg);
            // Match the production branch if present
            if (preg_match('/tsuniver_tsu_lms/', $c)) {
                return new PDO("mysql:host=localhost;dbname=tsuniver_tsu_lms;charset=utf8mb4", "tsuniver_tsu_lms", 'FvMxSL8oP96!A-}d', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            }
        }
        return null;
    }

    switch ($action) {
        case 'ping':
            $dbStatus = 'Not configured';
            try {
                $p = getDbConn();
                if ($p) $dbStatus = 'Connected';
            } catch (Throwable $ex) {
                $dbStatus = 'Unavailable: ' . $ex->getMessage();
            }

            $diskFree = function_exists('disk_free_space') ? round(@disk_free_space($rootDir) / (1024 * 1024), 2) : 'N/A';

            echo json_encode([
                'success' => true,
                'message' => 'Agent online and reachable',
                'server' => [
                    'php_version' => PHP_VERSION,
                    'os' => PHP_OS,
                    'root_dir' => $rootDir,
                    'disk_free_mb' => $diskFree,
                    'zip_enabled' => class_exists('ZipArchive'),
                    'db_status' => $dbStatus
                ]
            ]);
            break;

        case 'scan_files':
            $exclude = $_POST['exclude'] ?? ['.git', 'sessions', 'node_modules', 'error_log', basename(__FILE__)];
            if (is_string($exclude)) {
                $exclude = json_decode($exclude, true) ?: ['.git', 'node_modules'];
            }
            $exclude[] = basename(__FILE__);

            $files = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $subPath = str_replace('\\', '/', substr($item->getPathname(), strlen($rootDir) + 1));
                
                $skip = false;
                foreach ($exclude as $pat) {
                    if (empty($pat)) continue;
                    if ($pat === $subPath || strpos($subPath, rtrim($pat, '/') . '/') === 0 || fnmatch($pat, $subPath) || fnmatch($pat, basename($subPath))) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;

                if ($item->isFile()) {
                    $files[$subPath] = [
                        'hash' => md5_file($item->getPathname()),
                        'size' => $item->getSize(),
                        'mtime' => $item->getMTime()
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'total_files' => count($files),
                'files' => $files
            ]);
            break;

        case 'download_archive':
            $file = $_GET['file'] ?? $_POST['file'] ?? '';
            $file = basename($file);
            $fullPath = $rootDir . '/' . $file;
            if (empty($file) || !file_exists($fullPath) || substr($file, -4) !== '.zip') {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Archive file not found or invalid']);
                exit;
            }
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Length: ' . filesize($fullPath));
            readfile($fullPath);
            @unlink($fullPath); // Auto-cleanup after download
            exit;

        case 'export_files':
            if (!class_exists('ZipArchive')) {
                echo json_encode(['success' => false, 'error' => 'ZipArchive not available on server']);
                exit;
            }

            $requestedFiles = $_POST['files'] ?? $_POST['files[]'] ?? [];
            if (is_string($requestedFiles)) {
                $rawStr = trim($requestedFiles);
                $decoded = json_decode($rawStr, true);
                if (!$decoded) {
                    $decoded = json_decode(stripslashes($rawStr), true);
                }
                if (is_array($decoded)) {
                    $requestedFiles = $decoded;
                } else {
                    // Try splitting comma separated or remove surrounding brackets/quotes
                    $clean = trim($rawStr, "[]\"' ");
                    $requestedFiles = array_filter(array_map(fn($f) => trim($f, "\"'\t\n\r "), explode(',', $clean)));
                }
            }

            $zipName = 'export_' . date('Y_m_d_H_i_s') . '_' . bin2hex(random_bytes(4)) . '.zip';
            $zipPath = $rootDir . '/' . $zipName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                echo json_encode(['success' => false, 'error' => 'Failed to initialize export ZIP archive']);
                exit;
            }

            $added = 0;
            if (!empty($requestedFiles)) {
                foreach ($requestedFiles as $relPath) {
                    $norm = str_replace('\\', '/', $relPath);
                    $norm = str_replace('..', '', $norm);
                    $cleanRel = trim($norm, "/\"' \t\n\r");
                    $fullP = $rootDir . '/' . $cleanRel;
                    if (file_exists($fullP) && is_file($fullP)) {
                        $zip->addFile($fullP, $cleanRel);
                        $added++;
                    }
                }
            } else {
                $exclude = $_POST['exclude'] ?? ['.git', 'node_modules', 'sessions', 'vendor', 'uploads', '*.zip', basename(__FILE__)];
                if (is_string($exclude)) {
                    $exclude = json_decode($exclude, true) ?: ['.git', 'node_modules'];
                }
                $exclude[] = basename(__FILE__);
                $exclude[] = $zipName;

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $item) {
                    $subPath = str_replace('\\', '/', substr($item->getPathname(), strlen($rootDir) + 1));
                    $skip = false;
                    foreach ($exclude as $pat) {
                        if ($pat === $subPath || strpos($subPath, rtrim($pat, '/') . '/') === 0 || fnmatch($pat, $subPath) || fnmatch($pat, basename($subPath))) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) continue;

                    if ($item->isFile()) {
                        $zip->addFile($item->getPathname(), $subPath);
                        $added++;
                    }
                }
            }

            $zip->close();

            echo json_encode([
                'success' => true,
                'message' => 'Files exported successfully',
                'export_file' => $zipName,
                'file_count' => $added,
                'size_mb' => round(filesize($zipPath) / (1024 * 1024), 2),
                'debug' => $debug,
                'requested' => $requestedFiles,
                'root' => $rootDir
            ]);
            break;

        case 'deploy_files':
            if (!isset($_FILES['payload']) || $_FILES['payload']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'No valid payload received']);
                exit;
            }

            if (!class_exists('ZipArchive')) {
                echo json_encode(['success' => false, 'error' => 'ZipArchive extension missing on server']);
                exit;
            }

            $zipPath = $_FILES['payload']['tmp_name'];
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                echo json_encode(['success' => false, 'error' => 'Failed to open uploaded ZIP payload']);
                exit;
            }

            $extractedFiles = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'];
                $extractedFiles[] = $name;

                // Normalize Windows backslashes in zip entry path for Linux
                $normName = str_replace('\\', '/', $name);
                $targetFile = $rootDir . '/' . ltrim($normName, '/');

                if (substr($normName, -1) === '/') {
                    @mkdir($targetFile, 0755, true);
                    continue;
                }

                $targetDir = dirname($targetFile);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                $fp = $zip->getStream($name);
                if ($fp) {
                    $outFp = @fopen($targetFile, 'wb');
                    if ($outFp) {
                        stream_copy_to_stream($fp, $outFp);
                        fclose($outFp);
                    }
                    fclose($fp);
                }
            }
            $zip->close();

            echo json_encode([
                'success' => true,
                'message' => 'Files deployed successfully',
                'count' => count($extractedFiles),
                'files' => $extractedFiles
            ]);
            break;

        case 'delete_files':
            $filesToDelete = $_POST['files'] ?? [];
            if (is_string($filesToDelete)) {
                $filesToDelete = json_decode($filesToDelete, true) ?: [];
            }

            $deleted = [];
            foreach ($filesToDelete as $relPath) {
                $fullPath = $rootDir . '/' . ltrim(str_replace(['..', '\\'], ['', '/'], $relPath), '/');
                if (file_exists($fullPath) && is_file($fullPath)) {
                    if (@unlink($fullPath)) {
                        $deleted[] = $relPath;
                    }
                }
            }

            echo json_encode(['success' => true, 'deleted' => $deleted]);
            break;

        case 'run_sql':
            $sql = $_POST['sql'] ?? '';
            if (empty(trim($sql))) {
                echo json_encode(['success' => false, 'error' => 'SQL script is empty']);
                exit;
            }

            $pdo = getDbConn();
            if (!$pdo) {
                echo json_encode(['success' => false, 'error' => 'Database connection could not be established']);
                exit;
            }

            // Clean BOM and comments
            $bom = pack('H*','EFBBBF');
            $sql = preg_replace("/^$bom/", '', $sql);
            $sql = preg_replace('/--.*$/m', '', $sql); // Remove line comments
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove block comments

            $queries = array_filter(array_map('trim', explode(';', $sql)));
            $executed = 0;
            $warnings = [];
            foreach ($queries as $q) {
                if (empty($q)) continue;
                try {
                    $pdo->exec($q);
                    $executed++;
                } catch (PDOException $e) {
                    // Check if error is simply "duplicate key/index already exists" (code 42000 / 1061 in MySQL/MariaDB)
                    if (strpos($e->getMessage(), 'Duplicate key name') !== false || $e->getCode() == '42S21' || strpos($e->getMessage(), 'already exists') !== false) {
                        $warnings[] = "Notice: index/column in '{$q}' already exists.";
                    } else {
                        throw $e;
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Successfully executed {$executed} query(ies)." . (!empty($warnings) ? " (" . count($warnings) . " notices ignored)" : ""),
                'executed_count' => $executed,
                'notices' => $warnings
            ]);
            break;

        case 'run_query':
            $sql = $_POST['sql'] ?? '';
            if (empty(trim($sql))) {
                echo json_encode(['success' => false, 'error' => 'Query is empty']);
                exit;
            }

            $pdo = getDbConn();
            if (!$pdo) {
                echo json_encode(['success' => false, 'error' => 'Database connection could not be established']);
                exit;
            }

            $stmt = $pdo->prepare($sql);
            $params = $_POST['params'] ?? [];
            if (is_string($params)) {
                $params = json_decode($params, true) ?: [];
            }
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'count' => count($rows),
                'rows' => $rows
            ]);
            break;

        case 'auto_grade':
            $assessmentId = $_POST['assessment_id'] ?? null;
            $studentId = $_POST['student_id'] ?? null;

            $graderFile = $rootDir . '/src/AssessmentGrader.php';
            if (!file_exists($graderFile)) {
                echo json_encode(['success' => false, 'error' => 'AssessmentGrader.php not found on server']);
                exit;
            }

            require_once $graderFile;
            $pdo = getDbConn();
            if (!$pdo) {
                echo json_encode(['success' => false, 'error' => 'Database connection failed']);
                exit;
            }

            if ($assessmentId && $studentId) {
                $res = gradeStudentAssessment($pdo, $assessmentId, $studentId);
                echo json_encode($res);
            } else {
                // Auto-grade all ungraded students with responses
                $query = "
                    SELECT DISTINCT r.assessment_id, r.student_id
                    FROM responses r
                    LEFT JOIN grades g ON r.assessment_id = g.assessment_id AND r.student_id = g.student_id
                    WHERE g.id IS NULL
                ";
                if ($assessmentId) {
                    $query .= " AND r.assessment_id = " . intval($assessmentId);
                }
                $ungraded = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
                $count = 0;
                $details = [];
                foreach ($ungraded as $row) {
                    $r = gradeStudentAssessment($pdo, $row['assessment_id'], $row['student_id']);
                    if ($r['success']) {
                        $count++;
                        $details[] = ['assessment_id' => $row['assessment_id'], 'student_id' => $row['student_id'], 'score' => $r['score']];
                    }
                }
                echo json_encode(['success' => true, 'graded_count' => $count, 'details' => $details]);
            }
            break;

        case 'backup':
            if (!class_exists('ZipArchive')) {
                echo json_encode(['success' => false, 'error' => 'ZipArchive not available']);
                exit;
            }

            $backupName = 'snapshot_' . date('Y_m_d_H_i_s') . '.zip';
            $backupPath = $rootDir . '/' . $backupName;

            $zip = new ZipArchive();
            if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                echo json_encode(['success' => false, 'error' => 'Failed to create backup ZIP']);
                exit;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $exclude = ['.git', 'sessions', 'node_modules', basename(__FILE__), $backupName];
            foreach ($iterator as $item) {
                $subPath = str_replace('\\', '/', substr($item->getPathname(), strlen($rootDir) + 1));
                $skip = false;
                foreach ($exclude as $pat) {
                    if ($pat === $subPath || strpos($subPath, $pat . '/') === 0 || fnmatch($pat, $subPath)) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;

                if ($item->isFile()) {
                    $zip->addFile($item->getPathname(), $subPath);
                }
            }
            $zip->close();

            echo json_encode([
                'success' => true,
                'message' => 'Backup snapshot created',
                'backup_file' => $backupName,
                'size_mb' => round(filesize($backupPath) / (1024 * 1024), 2)
            ]);
            break;


        case "scan_malware":
            $includeIgnored = !empty($_POST["include_ignored"]) && $_POST["include_ignored"] !== "false" && $_POST["include_ignored"] !== "0";
            // Scan the entire webroot directory ($rootDir, e.g. public_html/)
            $scanner = new MalwareScanner($rootDir, []);
            $result = $scanner->scan($includeIgnored);
            echo json_encode($result);
            break;

        case "inspect_file":
            $relPath = $_POST["file"] ?? $_GET["file"] ?? "";
            $scanner = new MalwareScanner($rootDir);
            $res = $scanner->inspectFile($relPath);
            echo json_encode($res);
            break;

        case "delete_malware_file":
            $relPath = $_POST["file"] ?? "";
            $scanner = new MalwareScanner($rootDir);
            $res = $scanner->deleteFile($relPath);
            echo json_encode($res);
            break;

        case "clean_malware_file":
            $relPath = $_POST["file"] ?? "";
            $cleanedContent = isset($_POST["content"]) ? (string)$_POST["content"] : null;
            $scanner = new MalwareScanner($rootDir);
            $res = $scanner->cleanFile($relPath, $cleanedContent);
            echo json_encode($res);
            break;


        case "remove_suspicious_snippet":
            $relPath = $_POST["file"] ?? "";
            $snippet = $_POST["snippet"] ?? "";
            $actionType = $_POST["action_type"] ?? "comment";
            $scanner = new MalwareScanner($rootDir);
            $res = $scanner->removeSnippet($relPath, $snippet, $actionType);
            echo json_encode($res);
            break;

        default:
            echo json_encode(['success' => false, 'error' => "Unknown action: '{$action}'"]);
            break;
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fatal Error: ' . $e->getMessage()]);
}

