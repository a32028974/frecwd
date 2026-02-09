<?php
$ACCESS_KEY = "frecwd6980"; // <--- MISMO QUE index.php
$UPLOAD_DIR = __DIR__ . "/uploads/";

function deny() {
  http_response_code(403);
  exit("403 - Acceso denegado.");
}

if (!isset($_GET["k"]) || $_GET["k"] !== $ACCESS_KEY) deny();
if (!isset($_GET["f"])) { http_response_code(400); exit("Falta f."); }

$f = basename($_GET["f"]);
$path = $UPLOAD_DIR . $f;

if (is_file($path)) unlink($path);

header("Location: ./?k=" . urlencode($_GET["k"]));
exit;
