<?php

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

require 'config.php';

// Get all goals with player names
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchGoals'])) {
    $stmt = $pdo->query("SELECT goals.GoalID, goals.PlayerID, goals.Date, goals.MatchID, goals.TimeScored, player.PlayerName, matches.Opponent
                         FROM goals
                         JOIN player ON goals.PlayerID = player.PlayerID
                         JOIN matches ON goals.MatchID = matches.MatchID");
    $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($goals);
}

// Get a single goal by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['GoalID'])) {
    $id = $_GET['GoalID'];
    $stmt = $pdo->prepare("SELECT goals.GoalID, goals.PlayerID, goals.Date, goals.MatchID, goals.TimeScored, player.PlayerName, matches.Opponent
                           FROM goals
                           JOIN player ON goals.PlayerID = player.PlayerID
                           JOIN matches ON goals.MatchID = matches.MatchID
                           WHERE goals.GoalID = :GoalID");
    $stmt->execute(['GoalID' => $id]);
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($goal);
}

// Create a new goal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO goals (PlayerID, Date, MatchID, TimeScored) 
            VALUES (:PlayerID, :Date, :MatchID, :TimeScored)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'PlayerID' => $data['PlayerID'],
        'Date' => $data['Date'],
        'MatchID' => $data['MatchID'],
        'TimeScored' => $data['TimeScored']
    ]);
    echo json_encode(['message' => 'Goal added successfully']);
}

// Update a goal
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE goals SET PlayerID = :PlayerID, Date = :Date, MatchID = :MatchID, TimeScored = :TimeScored 
            WHERE GoalID = :GoalID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'PlayerID' => $data['PlayerID'],
        'Date' => $data['Date'],
        'MatchID' => $data['MatchID'],
        'TimeScored' => $data['TimeScored'],
        'GoalID' => $data['GoalID']
    ]);
    echo json_encode(['message' => 'Goal updated successfully']);
}

// Delete a goal
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['GoalID'];
    $sql = "DELETE FROM goals WHERE GoalID = :GoalID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['GoalID' => $id]);
    echo json_encode(['message' => 'Goal deleted successfully']);
}
