<?php

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

require 'config.php';

// Get all players
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['PlayerID'])) {
    $stmt = $pdo->query("SELECT * FROM player");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($players);
}

// Get a single player by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['PlayerID'])) {
    $id = $_GET['PlayerID'];
    $stmt = $pdo->prepare("SELECT * FROM player WHERE PlayerID = :PlayerID");
    $stmt->execute(['PlayerID' => $id]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($player);
}

// Create a new player
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Make sure all required keys are present
    $params = [
        ':PlayerName' => $data['PlayerName'],
        ':Position' => $data['Position'],
        ':Goals' => $data['Goals'],
        ':Assists' => $data['Assists'],
        ':Department' => $data['Department'],
        ':Programme' => $data['Programme'],
        ':MatchesPlayed' => $data['MatchesPlayed']
    ];

    $sql = "INSERT INTO player (PlayerName, Position, Goals, Assists, Department, Programme, MatchesPlayed) 
            VALUES (:PlayerName, :Position, :Goals, :Assists, :Department, :Programme, :MatchesPlayed)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['message' => 'Player added successfully']);
}


// Update a player
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE player SET PlayerName = :PlayerName, Position = :Position, Goals = :Goals, 
            Assists = :Assists, MatchesPlayed = :MatchesPlayed, Department = :Department, Programme = :Programme 
            WHERE PlayerID = :PlayerID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    echo json_encode(['message' => 'Player updated successfully']);
}

// Delete a player
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['PlayerID'];
    $sql = "DELETE FROM player WHERE PlayerID = :PlayerID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['PlayerID' => $id]);
    echo json_encode(['message' => 'Player deleted successfully']);
}
