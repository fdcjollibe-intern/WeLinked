
<?php
// Edit DB credentials to match your environment
$DB_HOST = 'db';
$DB_NAME = 'welinked_db';
$DB_USER = 'welinked';
$DB_PASS = 'welinked@password';
$DB_DSN  = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$users = [
    ['username'=>'alice','full_name'=>'Alice Morgan','email'=>'alice@example.test'],
    ['username'=>'bob','full_name'=>'Bob Carter','email'=>'bob@example.test'],
    ['username'=>'carol','full_name'=>'Carol Nguyen','email'=>'carol@example.test'],
    ['username'=>'dave','full_name'=>'Dave Brooks','email'=>'dave@example.test'],
    ['username'=>'eve','full_name'=>'Eve Summers','email'=>'eve@example.test'],
    ['username'=>'frank','full_name'=>'Frank Ortiz','email'=>'frank@example.test'],
    ['username'=>'grace','full_name'=>'Grace Hill','email'=>'grace@example.test'],
    ['username'=>'heidi','full_name'=>'Heidi Park','email'=>'heidi@example.test'],
    ['username'=>'ivan','full_name'=>'Ivan Petrov','email'=>'ivan@example.test'],
    ['username'=>'judy','full_name'=>'Judy Blake','email'=>'judy@example.test'],
    ['username'=>'mallory','full_name'=>'Mallory Grant','email'=>'mallory@example.test'],
    ['username'=>'oscar','full_name'=>'Oscar Reed','email'=>'oscar@example.test'],
    ['username'=>'peggy','full_name'=>'Peggy Lane','email'=>'peggy@example.test'],
    ['username'=>'trent','full_name'=>'Trent Mason','email'=>'trent@example.test'],
    ['username'=>'victor','full_name'=>'Victor Hale','email'=>'victor@example.test'],
    ['username'=>'walter','full_name'=>'Walter Fox','email'=>'walter@example.test'],
    ['username'=>'sybil','full_name'=>'Sybil Rhodes','email'=>'sybil@example.test'],
    ['username'=>'tricia','full_name'=>'Tricia Cole','email'=>'tricia@example.test'],
    ['username'=>'rachel','full_name'=>'Rachel Stone','email'=>'rachel@example.test'],
    ['username'=>'zack','full_name'=>'Zack Porter','email'=>'zack@example.test'],
];

try {
    $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, full_name, password_hash, profile_photo_path, gender, created_at, updated_at)
        VALUES (:username, :email, :full_name, :password_hash, :profile_photo_path, :gender, :created_at, :updated_at)
    ");

    $now = (new DateTime())->format('Y-m-d H:i:s');
    $defaultPassword = 'Password123!'; // change if needed
    $hashed = password_hash($defaultPassword, PASSWORD_DEFAULT);

    $inserted = 0;
    foreach ($users as $u) {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
        $check->execute([':username'=>$u['username'], ':email'=>$u['email']]);
        if ($check->fetch()) continue;

        $stmt->execute([
            ':username' => $u['username'],
            ':email' => $u['email'],
            ':full_name' => $u['full_name'],
            ':password_hash' => $hashed,
            ':profile_photo_path' => null,
            ':gender' => 'Prefer not to say',
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $inserted++;
    }

    echo "Inserted {$inserted} users. Default password: {$defaultPassword}\n";
} catch (Exception $e) {
    fwrite(STDERR, "Seeder error: " . $e->getMessage() . "\n");
    exit(1);
}
?>
// ...existing code...