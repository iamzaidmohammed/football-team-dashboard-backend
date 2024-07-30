<?php

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

require 'config.php';

// Get all managers
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['ManagerID'])) {
    $stmt = $pdo->query("SELECT * FROM manager");
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($managers);
}

// Get a single manager by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ManagerID'])) {
    $id = $_GET['ManagerID'];
    $stmt = $pdo->prepare("SELECT * FROM manager WHERE ManagerID = :ManagerID");
    $stmt->execute(['ManagerID' => $id]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($manager);
}

// Create a new manager
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Make sure all required keys are present
    $params = [
        ':ManagerName' => $data['ManagerName'],
        ':Position' => $data['Position'],
    ];

    $sql = "INSERT INTO manager (ManagerName, Position) 
            VALUES (:ManagerName, :Position)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['message' => 'Manager added successfully']);
}


// Update a manager
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE manager SET ManagerName = :ManagerName, Position = :Position WHERE ManagerID = :ManagerID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    echo json_encode(['message' => 'Manager updated successfully']);
}

// Delete a manager
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['ManagerID'];
    $sql = "DELETE FROM manager WHERE ManagerID = :ManagerID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['ManagerID' => $id]);
    echo json_encode(['message' => 'Manager deleted successfully']);
}
