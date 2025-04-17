
# Lucrarea de laborator nr. 8: Integrare continuă cu Github Actions
# Covrig Andrei, grupa I2301
# 2025

## Scopul lucrării

Familiarizarea cu configurarea integrării continue cu ajutorul Github Actions.

## Sarcina

Crearea unei aplicații Web, scrierea testelor pentru aceasta și configurarea integrării continue cu ajutorul Github Actions pe baza containerelor.

## Pregătire

Am instalat pe computer [Docker](https://www.docker.com/).

## Execuție

Am creat un repozitoriu `containers08` și l-am copiat pe computer.

În directorul `containers08` am creat directorul `./site`. În directorul `./site` am plasat aplicația Web pe baza PHP.

### Crearea aplicației Web

Am creat în directorul `./site` aplicația Web pe baza PHP cu următoarea structură:

```text
site
├── modules/
│   ├── database.php
│   └── page.php
├── templates/
│   └── index.tpl
├── styles/
│   └── style.css
├── config.php
└── index.php
```

Fișierul `modules/database.php` conține clasa `Database` pentru lucru cu baza de date. Pentru lucru cu baza de date am folosit SQLite. Clasa conține metodele:

- `__construct($path)` - constructorul clasei, primește calea către fișierul bazei de date SQLite;
- `Execute($sql)` - execută interogarea SQL;
- `Fetch($sql)` - execută interogarea SQL și returnează rezultatul sub formă de tablou asociativ;
- `Create($table, $data)` - creează înregistrare în tabelul `$table` cu datele din tabloul asociativ `$data` și returnează identificatorul înregistrării create;
- `Read($table, $id)` - returnează înregistrarea din tabelul `$table` după identificatorul `$id`;
- `Update($table, $id, $data)` - actualizează înregistrarea în tabelul `$table` după identificatorul `$id` cu datele din tabloul asociativ `$data`;
- `Delete($table, $id)` - șterge înregistrarea din tabelul `$table` după identificatorul `$id`;
- `Count($table)` - returnează numărul înregistrărilor din tabelul `$table`.

Fișierul `modules/page.php` conține clasa `Page` pentru lucru cu paginile. Clasa conține metodele:

- `__construct($template)` - constructorul clasei, primește calea către șablonul paginii;
- `Render($data)` - afișează pagina, înlocuind datele din tabloul asociativ `$data` în șablon.

Fișierul `templates/index.tpl` conține șablonul paginii.

Fișierul `styles/style.css` conține stilurile pentru pagina.

Fișierul `index.php` conține codul pentru afișarea paginii. Un exemplu de cod pentru fișierul `index.php`:

```php
<?php

require_once __DIR__ . '/modules/database.php';
require_once __DIR__ . '/modules/page.php';

require_once __DIR__ . '/config.php';

$db = new Database($config["db"]["path"]);

$page = new Page(__DIR__ . '/templates/index.tpl');

// bad idea, not recommended
$pageId = $_GET['page'];

$data = $db->Read("page", $pageId);

echo $page->Render($data);
```

Fișierul `config.php` conține setările pentru conectarea la baza de date.

### Pregătirea fișierului SQL pentru baza de date

Am creat în directorul `./site` directorul `./sql`. În directorul creat am creat fișierul `schema.sql` cu următorul conținut:

```sql
CREATE TABLE page (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    content TEXT
);

INSERT INTO page (title, content) VALUES ('Page 1', 'Content 1');
INSERT INTO page (title, content) VALUES ('Page 2', 'Content 2');
INSERT INTO page (title, content) VALUES ('Page 3', 'Content 3');
```

### Crearea testelor

Am creat în rădăcina directorului `containers08` directorul `./tests`. În directorul creat am creat fișierul `testframework.php` cu următorul conținut:

```php
<?php

function message($type, $message) {
    $time = date('Y-m-d H:i:s');
    echo "{$time} [{$type}] {$message}" . PHP_EOL;
}

function info($message) {
    message('INFO', $message);
}

function error($message) {
    message('ERROR', $message);
}

function assertExpression($expression, $pass = 'Pass', $fail = 'Fail'): bool {
    if ($expression) {
        info($pass);
        return true;
    }
    error($fail);
    return false;
}

class TestFramework {
    private $tests = [];
    private $success = 0;

    public function add($name, $test) {
        $this->tests[$name] = $test;
    }

    public function run() {
        foreach ($this->tests as $name => $test) {
            info("Running test {$name}");
            if ($test()) {
                $this->success++;
            }
            info("End test {$name}");
        }
    }

    public function getResult() {
        return "{$this->success} / " . count($this->tests);
    }
}
```

Am creat în directorul `./tests` fișierul `tests.php` cu următorul conținut:

```php
<?php

require_once __DIR__ . '/testframework.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$testFramework = new TestFramework();

// test 1: check database connection
function testDbConnection() {
    global $config;
    // ...
}

// test 2: test count method
function testDbCount() {
    global $config;
    // ...
}

// test 3: test create method
function testDbCreate() {
    global $config;
    // ...
}

// test 4: test read method
function testDbRead() {
    global $config;
    // ...
}

// add tests
$tests->add('Database connection', 'testDbConnection');
$tests->add('table count', 'testDbCount');
$tests->add('data create', 'testDbCreate');
// ...

// run tests
$tests->run();

echo $tests->getResult();
```

Am adăugat în fișierul `./tests/tests.php` teste pentru toate metodele clasei `Database`, precum și pentru metodele clasei `Page`.

### Crearea Dockerfile

Am creat în directorul rădăcină al proiectului fișierul `Dockerfile` cu următorul conținut:

```dockerfile
FROM php:7.4-fpm as base

RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite

VOLUME ["/var/www/db"]

COPY sql/schema.sql /var/www/db/schema.sql

RUN echo "prepare database" && \
    cat /var/www/db/schema.sql | sqlite3 /var/www/db/db.sqlite && \
    chmod 777 /var/www/db/db.sqlite && \
    rm -rf /var/www/db/schema.sql && \
    echo "database is ready"

COPY site /var/www/html
```

### Configurarea Github Actions

Am creat în directorul rădăcină al proiectului fișierul `.github/workflows/main.yml` cu următorul conținut:

```yaml
name: CI

on:
  push:
    branches:
      - main

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Build the Docker image
        run: docker build -t containers07 .
      - name: Create `container`
        run: docker create --name container --volume database:/var/www/db containers07
      - name: Copy tests to the container
        run: docker cp ./tests container:/var/www/html
      - name: Up the container
        run: docker start container
      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php
      - name: Stop the container
        run: docker stop container
      - name: Remove the container
        run: docker rm container
```

## Pornire și testare

Am trimis modificările în repozitoriul și m-am asigurat că testele trec cu succes. Pentru aceasta, am trecut la fila `Actions` în repozitoriu și am așteptat finalizarea sarcinii.

## Pregătirea raportului

Am creat în directorul `containers07` fișierul `README.md` care conține executarea pas cu pas a proiectului.

## Răspuns la întrebări

1. Ce este integrarea continuă?
2. Pentru ce sunt necesare testele unitare? Cât de des trebuie să fie executate?
3. Care modificări trebuie făcute în fișierul `.github/workflows/main.yml` pentru a rula testele la fiecare solicitare de trage (Pull Request)?
4. Ce trebuie adăugat în fișierul `.github/workflows/main.yml` pentru a șterge imaginile create după testare?
