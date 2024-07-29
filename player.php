<?php
require 'config.php';

// Get all players
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM player");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($players);
}

// Create a new player
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO PLAYER (PlayerName, Position, Goals, Assists, Department, Programme, MatchesPlayed) 
            VALUES (:PlayerName, :Position, :Goals, :Assists, :Department, :Programme, :MatchesPlayed)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    echo json_encode(['message' => 'Player added successfully']);
}

// Update a player
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE PLAYER SET PlayerName = :PlayerName, Position = :Position, Goals = :Goals, 
            Assists = :Assists, Department = :Department, Programme = :Programme, MatchesPlayed = :MatchesPlayed 
            WHERE PlayerID = :PlayerID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    echo json_encode(['message' => 'Player updated successfully']);
}

// Delete a player
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['PlayerID'];
    $sql = "DELETE FROM PLAYER WHERE PlayerID = :PlayerID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['PlayerID' => $id]);
    echo json_encode(['message' => 'Player deleted successfully']);
}
