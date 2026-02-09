<?php
// =========================
// TRANSFER MINI WEB (privado por link)
// =========================
$ACCESS_KEY = "frecwd6980"; // <--- CAMBIÁ ESTO
$UPLOAD_DIR = __DIR__ . "/uploads/";
$MAX_MB = 50;
$MAX_BYTES = $MAX_MB * 1024 * 1024;

function deny() {
  http_response_code(403);
  echo "403 - Acceso denegado.";
  exit;
}

if (!isset($_GET["k"]) || $_GET["k"] !== $ACCESS_KEY) deny();

if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);

$msg = "";

// ==== SUBIDA (Drag & Drop / Input) ====
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!isset($_FILES["files"])) {
    $msg = "No llegaron archivos.";
  } else {
    $count = count($_FILES["files"]["name"]);
    $ok = 0;
    $fail = 0;

    for ($i=0; $i<$count; $i++) {
      $err = $_FILES["files"]["error"][$i];
      if ($err !== UPLOAD_ERR_OK) { $fail++; continue; }

      $size = $_FILES["files"]["size"][$i];
      if ($size > $MAX_BYTES) { $fail++; continue; }

      $orig = basename($_FILES["files"]["name"][$i]);
      $orig = preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);

      // permitir solo cosas típicas (excel/pdf/img/zip/txt)
      $allowed = ['xlsx','xls','csv','pdf','jpg','jpeg','png','webp','zip','rar','7z','txt','doc','docx'];
      $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed, true)) { $fail++; continue; }

      $destName = date("Ymd_His") . "_" . $orig;
      $dest = $UPLOAD_DIR . $destName;

      if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $dest)) $ok++;
      else $fail++;
    }

    $msg = "✅ Subidos: $ok | ❌ Fallidos: $fail";
  }
}

// ==== LISTADO ====
$files = array_values(array_filter(glob($UPLOAD_DIR . "*"), "is_file"));
rsort($files);

$k = urlencode($_GET["k"]);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Transfer</title>
  <style>
    body{font-family:system-ui,Arial;max-width:900px;margin:20px auto;padding:0 14px}
    .card{border:1px solid #ddd;border-radius:16px;padding:14px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    .row{display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap}
    .muted{opacity:.7;font-size:.9em}
    .msg{margin:12px 0;padding:10px;border-radius:12px;background:#f6f6f6}
    .drop{
      margin-top:12px;
      border:2px dashed #bbb;border-radius:16px;padding:22px;text-align:center;
      transition:.15s;
      user-select:none;
    }
    .drop.drag{border-color:#333;background:#fafafa}
    button{padding:10px 14px;border-radius:12px;border:0;cursor:pointer}
    .btn{background:#111;color:#fff}
    .btn2{background:#eaeaea}
    ul{padding-left:18px}
    li{margin:8px 0}
    a{word-break:break-all}
    .actions{display:flex;gap:10px;align-items:center}
    .danger{background:#ffebeb}
  </style>
</head>
<body>

<h2>Transfer (privado por link)</h2>
<div class="card">
  <div class="row">
    <div>
      <div class="muted">Arrastrá tus archivos (Excel, PDF, imágenes, ZIP…). Máx <?= $MAX_MB ?> MB por archivo.</div>
      <div class="muted">Link para la otra PC: <b><?= htmlspecialchars((isset($_SERVER['HTTPS'])?'https':'http') . "://".$_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/?k=" . $_GET["k"]) ?></b></div>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="msg"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form id="upForm" method="post" enctype="multipart/form-data">
    <input id="fileInput" type="file" name="files[]" multiple style="display:none" />
    <div id="drop" class="drop">
      <b>📥 Soltá los archivos acá</b><br/>
      <span class="muted">o tocá para elegir</span>
    </div>
    <br/>
    <button class="btn" type="submit">Subir</button>
    <button class="btn2" type="button" id="refresh">Actualizar lista</button>
  </form>
</div>

<br/>

<div class="card">
  <h3>Archivos disponibles</h3>
  <?php if (!$files): ?>
    <div class="muted">No hay archivos todavía.</div>
  <?php else: ?>
    <ul>
      <?php foreach ($files as $f):
        $base = basename($f);
        $size = filesize($f);
        $kb = round($size/1024);
      ?>
        <li class="row">
          <div>
            <b><?= htmlspecialchars($base) ?></b>
            <span class="muted"> (<?= $kb ?> KB)</span>
          </div>
          <div class="actions">
            <a class="btn2" href="download.php?k=<?= $k ?>&f=<?= urlencode($base) ?>">Descargar</a>
            <a class="btn2 danger" href="delete.php?k=<?= $k ?>&f=<?= urlencode($base) ?>" onclick="return confirm('¿Borrar este archivo?')">Borrar</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<script>
const drop = document.getElementById('drop');
const input = document.getElementById('fileInput');
const refresh = document.getElementById('refresh');

drop.addEventListener('click', () => input.click());

drop.addEventListener('dragover', (e) => {
  e.preventDefault();
  drop.classList.add('drag');
});
drop.addEventListener('dragleave', () => drop.classList.remove('drag'));
drop.addEventListener('drop', (e) => {
  e.preventDefault();
  drop.classList.remove('drag');
  if (e.dataTransfer.files && e.dataTransfer.files.length) {
    input.files = e.dataTransfer.files;
  }
});

refresh.addEventListener('click', () => location.reload());
</script>

</body>
</html>
