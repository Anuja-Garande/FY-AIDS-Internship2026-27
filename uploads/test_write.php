<?php
// TEMPORARY DIAGNOSTIC SCRIPT — delete this file once you're done testing.
// Place it in the SAME folder as profile.php, then open it in your browser
// (e.g. http://localhost/yourproject/test_write.php).

$uploadDir = __DIR__ . "/uploads/";

echo "<h3>Upload folder write test</h3>";
echo "Checking: " . htmlspecialchars($uploadDir) . "<br><br>";

if (!is_dir($uploadDir)) {
    echo "❌ Folder does not exist. Attempting to create it...<br>";
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Folder created.<br>";
    } else {
        echo "❌ Could not create folder. Check the PARENT folder's permissions.<br>";
    }
}

echo "is_dir(): " . (is_dir($uploadDir) ? "✅ yes" : "❌ no") . "<br>";
echo "is_writable(): " . (is_writable($uploadDir) ? "✅ yes" : "❌ no") . "<br><br>";

$testFile = $uploadDir . "phptest_" . time() . ".txt";
$result = @file_put_contents($testFile, "test write at " . date("c"));

if ($result !== false) {
    echo "✅ SUCCESS: PHP wrote a file here (" . htmlspecialchars(basename($testFile)) . ").<br>";
    echo "This means the uploads folder itself is fine — your earlier issue was likely something else (already fixed, or a stale error).<br>";
    @unlink($testFile);
    echo "(test file cleaned up automatically)";
} else {
    echo "❌ FAILED: PHP could not write to this folder.<br>";
    echo "This confirms it's a permissions issue on the folder itself, not your upload code.<br>";
    echo "Windows tip: right-click the 'uploads' folder → Properties → Security tab → make sure your user (or 'Everyone', for local dev) has Full Control.";
}
