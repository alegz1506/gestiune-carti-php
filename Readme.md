# Documentație Proiect: Aplicație Web pentru Gestiunea Cărților

**Link GitHub (Repository):** [https://github.com/alegz1506/gestiune-carti-php](https://github.com/alegz1506/gestiune-carti-php)

---

## 1. Enunțul temei

Să se realizeze o aplicație web responsive pentru gestiunea cărților dintr-o bibliotecă. Aplicația trebuie să permită operațiile CRUD (Creare, Citire, Actualizare, Ștergere) și căutarea în orice câmp al bazei de date. Se vor folosi PHP și MySQL pentru partea de backend, iar pentru interfața utilizator (frontend) se vor utiliza HTML, CSS și JavaScript (inclusiv biblioteci externe pentru design responsive). S-a permis utilizarea asistenței AI pentru realizarea proiectului.

---

## 2. Structura bazei de date

Aplicația utilizează o bază de date relațională MySQL (MariaDB) numită `biblioteca`. În cadrul acesteia, datele sunt stocate într-un singur tabel principal denumit `carti`.

**Tabelul `carti`** are următoarea structură:

| Câmp | Tip | Constrângeri | Descriere |
|------|-----|--------------|-----------|
| `id` | INT | Primary Key, AUTO_INCREMENT | Identificatorul unic al cărții |
| `titlu` | VARCHAR(255) | NOT NULL | Titlul cărții |
| `autor` | VARCHAR(255) | NOT NULL | Autorul cărții |
| `an_publicare` | INT | NOT NULL | Anul în care a fost publicată cartea |
| `gen` | VARCHAR(100) | NOT NULL | Genul literar al cărții |

**Scriptul SQL pentru crearea structurii:**

```sql
CREATE DATABASE IF NOT EXISTS biblioteca;
USE biblioteca;

CREATE TABLE IF NOT EXISTS carti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titlu VARCHAR(255) NOT NULL,
    autor VARCHAR(255) NOT NULL,
    an_publicare INT NOT NULL,
    gen VARCHAR(100) NOT NULL
);
```

---

## 3. Explicații de realizare

Aplicația a fost construită respectând arhitectura de tip SPA (Single Page Application), separând clar logica de backend de interfața de frontend.

**Backend (PHP + MySQL):** S-a creat un API RESTful (`api.php`) care gestionează cererile HTTP (GET, POST, PUT, DELETE). Conexiunea la baza de date este realizată exclusiv prin extensia PDO (PHP Data Objects). Această abordare a fost aleasă pentru a asigura securitatea aplicației împotriva atacurilor de tip SQL Injection, folosind instrucțiuni pregătite (prepared statements). Comunicarea de date se face în format JSON.

**Frontend (HTML + CSS + JS):** Interfața este construită într-un singur fișier (`index.html`). Pentru un design modern și complet responsive pe orice dispozitiv (telefon, tabletă, desktop), s-a utilizat framework-ul Bootstrap 5. Interactivitatea și comunicarea cu serverul PHP se realizează asincron, fără reîncărcarea paginii, utilizând Fetch API din Vanilla JavaScript.

---

## 4. Manualul de utilizare

### Cerințe de sistem

- Un server web care poate rula PHP (ex. Apache, XAMPP sau serverul nativ PHP integrat `php -S`).
- Un server de baze de date MySQL sau MariaDB.
- Extensia `pdo_mysql` activată în fișierul `php.ini`.

### Instalare și Rulare

1. Se clonează repository-ul de pe GitHub sau se descarcă fișierele local.
2. Se creează baza de date `biblioteca` și tabelul `carti` pe serverul MySQL folosind scriptul de la punctul 2.
3. Se creează un utilizator cu drepturi de acces la baza de date și se actualizează datele de conectare (user și parolă) în primele rânduri din fișierul `api.php`.
4. Se pornește serverul web (ex: rulând `php -S localhost:8000` în terminalul deschis în folderul proiectului).
5. Se accesează aplicația în browser la adresa: [http://localhost:8000](http://localhost:8000).

### Utilizarea aplicației

**Adăugarea unei cărți:** Se apasă butonul albastru **„+ Adaugă Carte"**. Se va deschide o fereastră modală în care utilizatorul trebuie să completeze Titlul, Autorul, Anul Publicării și Genul. La apăsarea butonului „Salvează", cartea va apărea instantaneu în tabel.

**Editarea unei cărți:** În tabel, pe rândul corespunzător cărții dorite, se apasă butonul galben **„Editează"**. Fereastra modală se va deschide pre-completată cu datele existente, permițând modificarea și salvarea acestora.

**Ștergerea unei cărți:** Se apasă butonul roșu **„Șterge"** de pe rândul cărții. Aplicația va cere o confirmare înainte de a elimina definitiv înregistrarea.

**Căutarea:** În bara de căutare din partea stângă sus se poate introduce orice text. Tabelul se va filtra automat, afișând doar cărțile care conțin textul respectiv în titlu, autor, an sau gen.

---

## 5. Listarea codului sursă integral

### Fișierul `api.php` (Backend)

```php
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
```
---

### Fișierul `index.html` (Frontend)

```html
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestiune Cărți</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4 text-center">Aplicație Gestiune Cărți</h2>

    <div class="row mb-3">
        <div class="col-md-8 mb-2">
            <input type="text" id="searchInput" class="form-control" placeholder="Caută în orice câmp (titlu, autor, an, gen)..." onkeyup="loadBooks()">
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary" onclick="openModal()">+ Adaugă Carte</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Titlu</th>
                    <th>Autor</th>
                    <th>An Publicare</th>
                    <th>Gen</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody id="bookTableBody">
                </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="bookModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Adaugă Carte</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="bookForm">
            <input type="hidden" id="bookId">
            <div class="mb-3">
                <label>Titlu</label>
                <input type="text" id="titlu" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Autor</label>
                <input type="text" id="autor" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>An Publicare</label>
                <input type="number" id="an" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Gen</label>
                <input type="text" id="gen" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Salvează</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const apiUrl = 'api.php';
    let bookModal = new bootstrap.Modal(document.getElementById('bookModal'));

    // Funcția Read & Search
    async function loadBooks() {
        try {
            const query = document.getElementById('searchInput').value;
            const res = await fetch(`${apiUrl}?q=${encodeURIComponent(query)}`);
            const data = await res.json();
            
            // Verificăm dacă PHP-ul ne-a trimis o eroare în loc de lista de cărți
            if (data.error) {
                console.error("Eroare de la PHP:", data.error);
                document.getElementById('bookTableBody').innerHTML = `<tr><td colspan="6" class="text-danger text-center fw-bold">Eroare Bază de Date: ${data.error}</td></tr>`;
                return; // Oprim funcția aici
            }

            // Verificăm dacă data este cu adevărat o listă
            if (!Array.isArray(data)) {
                console.error("Format de date invalid returnat de PHP:", data);
                return;
            }
            
            let html = '';
            data.forEach(book => {
                html += `<tr>
                    <td>${book.id}</td>
                    <td>${book.titlu}</td>
                    <td>${book.autor}</td>
                    <td>${book.an_publicare}</td>
                    <td>${book.gen}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editBook(${book.id}, '${book.titlu}', '${book.autor}', ${book.an_publicare}, '${book.gen}')">Editează</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteBook(${book.id})">Șterge</button>
                    </td>
                </tr>`;
            });
            document.getElementById('bookTableBody').innerHTML = html;
        } catch (error) {
            console.error("Eroare la procesarea cererii:", error);
        }
    }

    // Funcția Delete
    async function deleteBook(id) {
        if(confirm('Ești sigur că vrei să ștergi această carte?')) {
            await fetch(`${apiUrl}?id=${id}`, { method: 'DELETE' });
            loadBooks();
        }
    }

    // Deschide Modal pt Adăugare
    function openModal() {
        document.getElementById('bookForm').reset();
        document.getElementById('bookId').value = '';
        document.getElementById('modalTitle').innerText = 'Adaugă Carte';
        bookModal.show();
    }

    // Populează Modal pt Editare
    function editBook(id, titlu, autor, an, gen) {
        document.getElementById('bookId').value = id;
        document.getElementById('titlu').value = titlu;
        document.getElementById('autor').value = autor;
        document.getElementById('an').value = an;
        document.getElementById('gen').value = gen;
        document.getElementById('modalTitle').innerText = 'Editează Carte';
        bookModal.show();
    }

    // Funcția Create / Update (la submiterea formularului)
    document.getElementById('bookForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('bookId').value;
        const payload = {
            id: id,
            titlu: document.getElementById('titlu').value,
            autor: document.getElementById('autor').value,
            an_publicare: document.getElementById('an').value,
            gen: document.getElementById('gen').value
        };

        const method = id ? 'PUT' : 'POST';

        await fetch(apiUrl, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        bookModal.hide();
        loadBooks();
    });

    // Încarcă datele inițial
    loadBooks();
</script>

</body>
</html>
```