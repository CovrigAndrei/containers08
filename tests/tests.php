<?php
require_once __DIR__ . '/testframework.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../modules/database.php';
require_once __DIR__ . '/../modules/page.php';

$testFramework = new TestFramework();

// Test 1: Verificarea conexiunii la baza de date
function testDbConnection() {
    global $config;
    $db = new Database($config['db']['path']);
    return assertExpression(
        $db !== null,
        "Database connection successful",
        "Failed to connect to database"
    );
}

// Test 2: Verificarea metodei Count
function testDbCount() {
    global $config;
    $db = new Database($config['db']['path']);
    $count = $db->Count('page');
    return assertExpression(
        $count >= 3,
        "Table count is $count",
        "Table count is less than expected"
    );
}

// Test 3: Verificarea metodei Create
function testDbCreate() {
    global $config;
    $db = new Database($config['db']['path']);
    $data = ['title' => 'Test Page', 'content' => 'Test Content'];
    $id = $db->Create('page', $data);
    return assertExpression(
        $id > 0,
        "Record created with ID $id",
        "Failed to create record"
    );
}

// Test 4: Verificarea metodei Read
function testDbRead() {
    global $config;
    $db = new Database($config['db']['path']);
    $data = $db->Read('page', 1);
    return assertExpression(
        isset($data['title']) && $data['title'] === 'Page 1',
        "Read record with title Page 1",
        "Failed to read record"
    );
}

// Test 5: Verificarea metodei Update
function testDbUpdate() {
    global $config;
    $db = new Database($config['db']['path']);
    $data = ['title' => 'Updated Page', 'content' => 'Updated Content'];
    $result = $db->Update('page', 1, $data);
    $updated = $db->Read('page', 1);
    return assertExpression(
        $updated['title'] === 'Updated Page',
        "Record updated successfully",
        "Failed to update record"
    );
}

// Test 6: Verificarea metodei Delete
function testDbDelete() {
    global $config;
    $db = new Database($config['db']['path']);
    $data = ['title' => 'Page to Delete', 'content' => 'Content to Delete'];
    $id = $db->Create('page', $data);
    $result = $db->Delete('page', $id);
    $deleted = $db->Read('page', $id);
    return assertExpression(
        $result && !$deleted,
        "Record deleted successfully",
        "Failed to delete record"
    );
}

// Test 7: Verificarea metodei Render din clasa Page
function testPageRender() {
    $page = new Page(__DIR__ . '/../templates/index.tpl');
    $data = ['title' => 'Test Title', 'content' => 'Test Content'];
    $output = $page->Render($data);
    return assertExpression(
        strpos($output, 'Test Title') !== false && strpos($output, 'Test Content') !== false,
        "Page rendered correctly",
        "Failed to render page"
    );
}

// Adăugarea testelor
$testFramework->add('Database connection', 'testDbConnection');
$testFramework->add('Table count', 'testDbCount');
$testFramework->add('Data create', 'testDbCreate');
$testFramework->add('Data read', 'testDbRead');
$testFramework->add('Data update', 'testDbUpdate');
$testFramework->add('Data delete', 'testDbDelete');
$testFramework->add('Page render', 'testPageRender');

// Rularea testelor
$testFramework->run();
echo $testFramework->getResult();