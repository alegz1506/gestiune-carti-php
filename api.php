<?php
header("Content-Type: application/json; charset=UTF-8");

// Setări conexiune baza de date
$host = 'localhost';
$db   = 'biblioteca';
$user = 'root'; // Modifică dacă ai alt user
$pass = '';     // Modifică dacă ai parolă
$user = 'student'; // Noul user creat
$pass = 'student123'; // Parola setată

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Eroare conectare baza de date: ' . $e->getMessage()]));
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET': // Citire (Read) și Căutare (Search)
        $search = isset($_GET['q']) ? $_GET['q'] : '';
        if ($search) {
            // Căutare în orice câmp
            $stmt = $pdo->prepare("SELECT * FROM carti WHERE titlu LIKE :q OR autor LIKE :q OR an_publicare LIKE :q OR gen LIKE :q");
            $stmt->execute(['q' => "%$search%"]);
        } else {
            // Toate cărțile
            $stmt = $pdo->query("SELECT * FROM carti ORDER BY id DESC");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST': // Creare (Create)
        $stmt = $pdo->prepare("INSERT INTO carti (titlu, autor, an_publicare, gen) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$input['titlu'], $input['autor'], $input['an_publicare'], $input['gen']])) {
            echo json_encode(['message' => 'Carte adăugată cu succes!']);
        }
        break;

    case 'PUT': // Actualizare (Update)
        $stmt = $pdo->prepare("UPDATE carti SET titlu=?, autor=?, an_publicare=?, gen=? WHERE id=?");
        if ($stmt->execute([$input['titlu'], $input['autor'], $input['an_publicare'], $input['gen'], $input['id']])) {
            echo json_encode(['message' => 'Carte actualizată cu succes!']);
        }
        break;

    case 'DELETE': // Ștergere (Delete)
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        $stmt = $pdo->prepare("DELETE FROM carti WHERE id=?");
        if ($stmt->execute([$id])) {
            echo json_encode(['message' => 'Carte ștearsă cu succes!']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Metodă invalidă']);
        break;
}
?>