<?php



set_time_limit(0);
$root = __DIR__ . '/../';
$extensions = ['php','html','htm','js','css','md'];
$backupDir = $root . 'backups/file_backup_' . date('Ymd_His') . '/';
$excludeDirs = ['backups', 'node_modules', '.git'];

function shouldExclude($path, $excludeDirs) {
    foreach ($excludeDirs as $d) {
        if (strpos($path, DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR) !== false) return true;
    }
    return false;
}

function ensureDir($path) {
    if (!is_dir($path)) mkdir($path, 0777, true);
}

function backupFile($src, $backupRoot) {
    $dest = $backupRoot . ltrim(substr($src, strlen(realpath(__DIR__ . '/../'))), DIRECTORY_SEPARATOR);
    $destDir = dirname($dest);
    if (!is_dir($destDir)) mkdir($destDir, 0777, true);
    copy($src, $dest);
}

function stripPhpComments($code) {
    $tokens = token_get_all($code);
    $out = '';
    foreach ($tokens as $token) {
        if (is_array($token)) {
            $id = $token[0];
            $text = $token[1];
            if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
                
                continue;
            }
            if ($id === T_INLINE_HTML) {
                
                $text = preg_replace('/<!--([\s\S]*?)-->/', '', $text);
            }
            $out .= $text;
        } else {
            $out .= $token;
        }
    }
    return $out;
}

function stripJsCssComments($s) {
    $len = strlen($s);
    $out = '';
    $i = 0;
    $inString = false;
    $stringChar = '';
    $escaped = false;
    while ($i < $len) {
        $c = $s[$i];
        $next = ($i+1 < $len) ? $s[$i+1] : '';
        if ($inString) {
            $out .= $c;
            if ($escaped) { $escaped = false; }
            else if ($c === '\\') { $escaped = true; }
            else if ($c === $stringChar) { $inString = false; $stringChar = ''; }
            $i++;
            continue;
        }
        if (($c === '"' || $c === "'" || $c === '`')) {
            $inString = true; $stringChar = $c; $out .= $c; $i++; continue;
        }
        
        if ($c === '/' && $next === '/') {
            
            $i += 2;
            while ($i < $len && $s[$i] !== "\n") $i++;
            continue;
        }
        
        if ($c === '/' && $next === '*') {
            $i += 2;
            while ($i < $len && !($s[$i] === '*' && isset($s[$i+1]) && $s[$i+1] === '/')) $i++;
            $i += 2; continue;
        }
        $out .= $c;
        $i++;
    }
    return $out;
}

function stripHtmlComments($s) {
    return preg_replace('/<!--([\s\S]*?)-->/', '', $s);
}


$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (shouldExclude($path, $excludeDirs)) continue;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, $extensions)) $files[] = $path;
}

ensureDir($backupDir);

$changed = [];
foreach ($files as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $orig = file_get_contents($file);
    $new = $orig;
    if ($ext === 'php') {
        $new = stripPhpComments($orig);
    } else if (in_array($ext, ['js','css'])) {
        $new = stripJsCssComments($orig);
    } else if (in_array($ext, ['html','htm'])) {
        
        $tmp = stripHtmlComments($orig);
        
        $tmp = preg_replace_callback('#<script(.*?)>([\s\S]*?)</script>#i', function($m){
            $inner = $m[2];
            $inner = stripJsCssComments($inner);
            return '<script'.$m[1].'>' . $inner . '</script>';
        }, $tmp);
        $tmp = preg_replace_callback('#<style(.*?)>([\s\S]*?)</style>#i', function($m){
            $inner = $m[2];
            $inner = stripJsCssComments($inner);
            return '<style'.$m[1].'>' . $inner . '</style>';
        }, $tmp);
        $new = $tmp;
    } else if ($ext === 'md') {
        $new = stripHtmlComments($orig);
    }

    if ($new !== $orig) {
        backupFile($file, $backupDir);
        file_put_contents($file, $new);
        $changed[] = $file;
        echo "Modified: $file\n";
    }
}

echo "\nSummary:\n";
echo "Files scanned: " . count($files) . "\n";
echo "Files modified: " . count($changed) . "\n";
if (!empty($changed)) {
    echo "Backup location: $backupDir\n";
}

return 0;
