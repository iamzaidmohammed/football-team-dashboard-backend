<?php

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit(0);
}

require 'config.php';

// Function to check if a record exists in a table
function recordExists($pdo, $table, $column, $value)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = :value");
    $stmt->execute(['value' => $value]);
    return $stmt->fetchColumn() > 0;
}

// Function to check if required fields are empty
function validateFields($data, $requiredFields)
{
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return false;
        }
    }
    return true;
}

// Get all matches with location
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchMatches'])) {
    $stmt = $pdo->query("SELECT matches.MatchID, matches.Date, matches.Opponent, matches.Results, matches.GoalsScored, matches.GoalsConceded, location.Location
                         FROM matches
                         JOIN location ON matches.Location = location.Location");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($matches);
}

// Get a single goal by ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['MatchID'])) {
    $id = $_GET['MatchID'];
    $stmt = $pdo->prepare("SELECT matches.MatchID, matches.Date, matches.Opponent, matches.Results, matches.GoalsScored, matches.GoalsConceded, location.Location
                           FROM matches
                           JOIN location ON matches.Location = location.Location
                           WHERE matches.MatchID = :MatchID");
    $stmt->execute(['MatchID' => $id]);
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($goal);
}

// Create a new goal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $requiredFields = [
        'Date',
        'Opponent',
        'Results',
        'Location',
        'GoalsScored',
        'GoalsConceded'
    ];

    // Check for empty fields
    if (!validateFields($data, $requiredFields)) {
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    // Check if the location exists
    if (!recordExists($pdo, 'location', 'location', $data['Location'])) {
        // Insert new location if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO location (location) VALUES (:location)");
        $stmt->execute(['location' => $data['Location']]);
    }


    $sql = "INSERT INTO matches (Date, Opponent, Results, Location, GoalsScored, GoalsConceded) 
            VALUES (:Date, :Opponent, :Results, :Location, :GoalsScored, :GoalsConceded)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'Date' => $data['Date'],
        'Opponent' => $data['Opponent'],
        'Results' => $data['Results'],
        'Location' => $data['Location'],
        'GoalsScored' => $data['GoalsScored'],
        'GoalsConceded' => $data['GoalsConceded'],
    ]);
    echo json_encode(['message' => 'Goal added successfully']);
}

// Update a goal
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $requiredFields = [
        'Date',
        'Opponent',
        'Results',
        'Location',
        'GoalsScored',
        'GoalsConceded'
    ];

    // Check for empty fields
    if (!validateFields($data, $requiredFields)) {
        echo json_encode(['error' => 'All fields are required']);
        exit;
    }

    // Check if the location exists
    if (!recordExists($pdo, 'location', 'location', $data['Location'])) {
        // Insert new location if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO location (location) VALUES (:location)");
        $stmt->execute(['location' => $data['Location']]);
    }

    $sql = "UPDATE matches SET Date= :Date, Opponent= :Opponent, Results= :Results, Location= :Location,GoalsScored= :GoalsScored, GoalsConceded= :GoalsConceded 
            WHERE MatchID = :MatchID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'Date' => $data['Date'],
        'Opponent' => $data['Opponent'],
        'Results' => $data['Results'],
        'Location' => $data['Location'],
        'GoalsScored' => $data['GoalsScored'],
        'GoalsConceded' => $data['GoalsConceded'],
        'MatchID' => $data['MatchID'],
    ]);
    echo json_encode(['message' => 'Goal updated successfully']);
}

// Delete a goal
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['MatchID'];
    $sql = "DELETE FROM matches WHERE MatchID = :MatchID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['MatchID' => $id]);
    echo json_encode(['message' => 'Goal deleted successfully']);
}
