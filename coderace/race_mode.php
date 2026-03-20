<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }

$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LearnQuest | Race Mode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #050505; color: #fff; font-family: 'Inter', sans-serif; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: #111; padding: 25px; border-right: 2px solid #0d6efd; }
        .main-content { flex-grow: 1; padding: 30px; }
        
        /* Race Theme Elements */
        #game-canvas { 
            width: 400px; height: 400px; background: #000; display: grid; 
            grid-template-columns: repeat(5, 1fr); border: 4px solid #0d6efd; 
            box-shadow: 0 0 20px rgba(13, 110, 253, 0.5); margin: 0 auto;
        }
        .cell { border: 1px solid #111; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }
        
        .code-block { 
            background: #0d6efd; color: white; padding: 12px; margin-bottom: 10px; 
            border-radius: 8px; cursor: grab; font-weight: bold; border-left: 5px solid #004fb1;
        }
        .drop-zone { background: #111; border: 2px dashed #0d6efd; min-height: 350px; border-radius: 15px; }
        
        /* Fuel/Block Counter */
        .fuel-container { background: #222; border-radius: 10px; padding: 15px; border: 1px solid #333; }
        .block-count { font-size: 2rem; font-weight: 800; color: #0d6efd; }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h3 class="text-primary fw-bold mb-4"><i class="fa-solid fa-bolt"></i> RACE MODE</h3>
    <div class="fuel-container text-center mb-4">
        <div class="small text-uppercase opacity-50">Block Limit</div>
        <div id="counter" class="block-count">0 / 8</div>
        <div class="progress mt-2" style="height: 10px;">
            <div id="fuel-bar" class="progress-bar bg-primary" style="width: 0%"></div>
        </div>
    </div>
    <a href="dashboard.php" class="btn btn-outline-light w-100 mt-auto">Back to Dashboard</a>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-md-5">
            <div class="bg-dark p-4 rounded-4 border border-secondary mb-3 text-center">
                <div id="game-canvas"></div>
                <h4 id="status-text" class="mt-3 text-primary fw-bold">READY TO RACE?</h4>
                <button class="btn btn-primary btn-lg w-100 mt-2 fw-bold" onclick="runRace()">START RACE <i class="fa-solid fa-flag-checkered ms-2"></i></button>
            </div>
        </div>

        <div class="col-md-3">
            <h6 class="text-secondary fw-bold">COMMANDS</h6>
            <div id="inventory">
                <div class="code-block" onclick="addRaceBlock('move')">move();</div>
                <div class="code-block" onclick="addRaceBlock('right')">turnRight();</div>
                <div class="code-block" onclick="addRaceBlock('left')">turnLeft();</div>
            </div>
        </div>

        <div class="col-md-4">
            <h6 class="text-secondary fw-bold">OPTIMIZED SEQUENCE</h6>
            <div id="logic-sequence" class="drop-zone p-3"></div>
            <button class="btn btn-sm btn-outline-danger mt-2 w-100" onclick="document.getElementById('logic-sequence').innerHTML=''; updateCounter();">Clear All</button>
        </div>
    </div>
</div>

<script>
    let carPos = { x: 0, y: 4 }; // Start sa baba
    let carDir = "north";
    const limit = 8; // Maximum blocks allowed
    
    // Simple Race Map (0 = Road, 1 = Wall, 2 = Finish)
    const map = [
        [1, 1, 1, 1, 2],
        [1, 1, 1, 1, 0],
        [0, 0, 0, 0, 0],
        [0, 1, 1, 1, 1],
        [0, 0, 0, 1, 1]
    ];

    function render() {
        const canvas = document.getElementById('game-canvas');
        canvas.innerHTML = '';
        map.forEach((row, y) => row.forEach((val, x) => {
            const cell = document.createElement('div');
            cell.className = 'cell';
            if(x === carPos.x && y === carPos.y) {
                cell.innerText = "🏎️";
                cell.style.transform = getRotation();
            }
            else if(val === 2) cell.innerText = "🏁";
            else if(val === 1) cell.style.background = "#111";
            canvas.appendChild(cell);
        }));
    }

    function getRotation() {
        if(carDir === "east") return "rotate(90deg)";
        if(carDir === "south") return "rotate(180deg)";
        if(carDir === "west") return "rotate(-90deg)";
        return "rotate(0deg)";
    }

    function addRaceBlock(cmd) {
        const zone = document.getElementById('logic-sequence');
        if(zone.children.length >= limit) {
            alert("Warning: Out of fuel/blocks!");
            return;
        }
        const div = document.createElement('div');
        div.className = 'code-block';
        div.innerText = cmd + '();';
        div.onclick = function() { this.remove(); updateCounter(); };
        zone.appendChild(div);
        updateCounter();
    }

    function updateCounter() {
        const count = document.getElementById('logic-sequence').children.length;
        document.getElementById('counter').innerText = `${count} / ${limit}`;
        document.getElementById('fuel-bar').style.width = (count / limit * 100) + "%";
    }

    async function runRace() {
        const blocks = document.getElementById('logic-sequence').children;
        if(blocks.length === 0) return;

        for(let b of blocks) {
            const cmd = b.innerText;
            if(cmd === 'move();') {
                if(carDir === "north") carPos.y--;
                else if(carDir === "east") carPos.x++;
                else if(carDir === "south") carPos.y++;
                else if(carDir === "west") carPos.x--;
            } else if(cmd === 'turnRight();') {
                const dirs = ["north", "east", "south", "west"];
                carDir = dirs[(dirs.indexOf(carDir) + 1) % 4];
            }
            
            render();
            //