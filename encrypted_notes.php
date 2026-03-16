<?php

$dir = __DIR__ . '/notes';
if (!is_dir($dir)) mkdir($dir, 0755, true);

function derive_keys($password)
{
    $hash = hash('sha512', $password, true);

    return [
        'enc' => substr($hash, 0, 32),
        'mac' => substr($hash, 32, 32)
    ];
}

function encrypt_note($plaintext, $password)
{
    $keys = derive_keys($password);

    $iv = random_bytes(16);

    $cipher = openssl_encrypt(
        $plaintext,
        'AES-256-CBC',
        $keys['enc'],
        OPENSSL_RAW_DATA,
        $iv
    );

    $mac = hash_hmac('sha256', $iv . $cipher, $keys['mac'], true);

    return base64_encode($iv . $mac . $cipher);
}

function decrypt_note($data, $password)
{
    $keys = derive_keys($password);

    $decoded = base64_decode($data, true);
    if ($decoded === false || strlen($decoded) < 48) {
        return false;
    }

    $iv = substr($decoded, 0, 16);
    $mac = substr($decoded, 16, 32);
    $cipher = substr($decoded, 48);

    $calc_mac = hash_hmac('sha256', $iv . $cipher, $keys['mac'], true);

    if (!hash_equals($mac, $calc_mac)) {
        return false;
    }

    return openssl_decrypt(
        $cipher,
        'AES-256-CBC',
        $keys['enc'],
        OPENSSL_RAW_DATA,
        $iv
    );
}

$message = '';
$notes_list = [];

/* SAVE NOTE */

if (isset($_POST['save'])) {

    $note = trim($_POST['note'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($note && $pass) {

        $files = glob("$dir/*.enc");
        $ids = array_map(fn($f) => (int)basename($f,'.enc'), $files);
        $next = $ids ? max($ids)+1 : 1;

        $enc = encrypt_note($note,$pass);

        file_put_contents("$dir/$next.enc",$enc);

        $message = "Note #$next saved";
    } else {
        $message = "Fill note and password";
    }
}

/* DELETE NOTE */

if (isset($_POST['delete'])) {

    $id = (int)($_POST['delete_id'] ?? 0);
    $pass = $_POST['delete_password'] ?? '';

    $file = "$dir/$id.enc";

    if ($id && file_exists($file)) {

        $data = file_get_contents($file);

        if (decrypt_note($data,$pass) !== false) {

            unlink($file);

            $message = "Note #$id deleted";

        } else {
            $message = "Wrong password";
        }

    }
}

/* VIEW NOTES */

if (isset($_POST['view'])) {

    $pass = $_POST['view_password'] ?? '';

    if ($pass) {

        $files = glob("$dir/*.enc");

        usort($files,function($a,$b){
            return (int)basename($b,'.enc') - (int)basename($a,'.enc');
        });

        foreach ($files as $file) {

            $id = (int)basename($file,'.enc');

            $data = file_get_contents($file);

            $dec = decrypt_note($data,$pass);

            if ($dec !== false) {

                $notes_list[]=[
                    'id'=>$id,
                    'text'=>$dec
                ];

            }
        }

    }

}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Encrypted Notes</title>

<style>

body{
font-family:Verdana;
max-width:800px;
margin:auto;
}

textarea,input{
width:100%;
padding:8px;
margin:6px 0;
}

button{
padding:10px 16px;
cursor:pointer;
}

.note{
border:1px solid #ddd;
padding:14px;
margin:16px 0;
background:#fafafa;
border-radius:6px;
}

.note-header{
display:flex;
justify-content:space-between;
}

.msg{
font-weight:bold;
margin:15px 0;
}

</style>

</head>
<body>

<h1>Encrypted Notes</h1>

<form method="post">

<h3>Add Note</h3>

<textarea name="note" rows="5" required></textarea>

<input type="password" name="password" placeholder="Password" required>

<button name="save">Save</button>

</form>

<?php if($message): ?>

<div class="msg"><?=htmlspecialchars($message)?></div>

<?php endif; ?>

<hr>

<form method="post">

<h3>View Notes</h3>

<input type="password" name="view_password" placeholder="Password" required>

<button name="view">Show Notes</button>

</form>

<?php if(isset($_POST['view'])): ?>

<h3>Found: <?=count($notes_list)?></h3>

<?php if(!$notes_list): ?>

<p>No notes for this password</p>

<?php endif; ?>

<?php foreach($notes_list as $n): ?>

<div class="note">

<div class="note-header">

<b>Note #<?=$n['id']?></b>

<form method="post">

<input type="hidden" name="delete_id" value="<?=$n['id']?>">

<input type="password" name="delete_password" placeholder="Password to delete" required>

<button name="delete">Delete</button>

</form>

</div>

<div style="white-space:pre-wrap">
<?=htmlspecialchars($n['text'])?>
</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</body>
</html>
