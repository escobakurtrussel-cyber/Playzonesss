<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnQuest | Level 2: Traffic Mastery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --sidebar-bg: #111827; 
            --main-bg: #f9fafb; 
            --accent-green: #10b981;
            --accent-purple: #a855f7;
        }
        body { background-color: var(--main-bg); color: #1f2937; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        
        /* Sidebar */
        .sidebar { width: 240px; height: 100vh; background: var(--sidebar-bg); color: white; position: fixed; padding: 20px; z-index: 100; }
        .sidebar .nav-link { color: #9ca3af; margin-bottom: 10px; border-radius: 8px; transition: 0.3s; padding: 12px; text-decoration: none; display: block; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #1f2937; color: var(--accent-green); }

        /* Main Content */
        .content { margin-left: 240px; padding: 30px; }
        
        /* Game Components */
        .game-card { background: white; border-radius: 15px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 20px; }
        #game-canvas { 
            width: 380px; height: 380px; 
            background: #222; border: 4px solid #444;
            display: grid; grid-template-columns: repeat(5, 1fr);
            grid-template-rows: repeat(5, 1fr);
            margin: 0 auto; border-radius: 8px;
        }
        .cell { border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; position: relative; }
        .road { background-color: #2a2a2a; }
        .wall { background-color: #441111; }
        .obstacle-cell { background-color: #551a1a !important; transition: background 0.3s; }

        /* Editor */
        #editor { 
            width: 100%; height: 280px; 
            font-family: 'Courier New', monospace; 
            background: #000; color: #0f0; 
            padding: 15px; border-radius: 12px; border: 1px solid #333;
            resize: none; outline: none;
        }
        .status-msg { height: 40px; font-weight: bold; margin-bottom: 10px; font-size: 1.1rem; }
        
        /* Buttons */
        .btn-run { background-color: var(--accent-green); color: white; font-weight: bold; border-radius: 10px; padding: 12px; border: none; width: 100%; }
        .btn-run:hover { background-color: #059669; color: white; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="mb-5"><i class="fa-solid fa-leaf text-success me-2"></i> LearnQuest</h4>
    <nav class="nav flex-column">
        <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-house me-2"></i> Dashboard</a>
        <a class="nav-link active" href="#"><i class="fa-solid fa-car me-2"></i> Level 2</a>
        <a class="nav-link" href="#"><i class="fa-solid fa-ranking-star me-2"></i> Leaderboard</a>
    </nav>
</div>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Level 2: Traffic Mastery</h2>
            <p class="text-muted">Master timing and the <code>wait()</code> command.</p>
        </div>
        <div class="badge p-3 text-white" style="background: var(--accent-purple); border-radius: 10px;">
            <i class="fa-solid fa-bolt me-2"></i> Reward: +250 XP
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 text-center">
            <div class="game-card">
                <div id="status" class="status-msg text-warning">Ready to race!</div>
                <div id="game-canvas"></div>
                <div class="mt-4 row g-2">
                    <div class="col-8">
                        <button class="btn btn-run" onclick="runCode()"><i class="fa-solid fa-play me-2"></i> RUN SCRIPT</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-outline-danger w-100 h-100" onclick="initGame()"><i class="fa-solid fa-rotate-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="game-card">
                <div class="alert alert-warning py-2">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <strong>Mission:</strong> A patrol 🚔 is blocking the road. Use <code>wait();</code> to let it pass!
                </div>
                
                <h6 class="fw-bold mb-2 text-secondary">Terminal</h6>
                <textarea id="editor" spellcheck="false">// Strategy:
move(2);
wait();
move(2);
turn("right");
move(4);</textarea>
                
                <div class="mt-3 p-3 bg-light rounded border">
                    <h6 class="fw-bold small">📖 Documentation</h6>
                    <div class="row small">
                        <div class="col-6"><code>move(n);</code> - Forward</div>
                        <div class="col-6"><code>wait();</code> - Pause 1 turn</div>
                        <div class="col-6"><code>turn("right");</code> - Rotate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let carPos = { x: 0, y: 0 };
    let carDir = "east";
    let obstaclePos = { x: 2, y: 1 }; 
    let obstacleDir = 1; 
    const gridSize = 5;
    
    const map = [
        [0, 0, 0, 0, 0],
        [1, 1, 0, 1, 1],
        [1, 1, 0, 1, 1],
        [1, 1, 0, 1, 1],
        [1, 1, 0, 0, 2] 
    ];

    function initGame() {
        carPos = { x: 0, y: 0 };
        carDir = "east";
        obstaclePos = { x: 2, y: 1 };
        obstacleDir = 1;
        const statusDiv = document.getElementById('status');
        statusDiv.className = "status-msg text-warning";
        statusDiv.innerText = "Ready to race!";
        renderMap();
    }

    function renderMap() {
        const canvas = document.getElementById('game-canvas');
        canvas.innerHTML = '';
        for (let y = 0; y < gridSize; y++) {
            for (let x = 0; x < gridSize; x++) {
                const cell = document.createElement('div');
                cell.className = 'cell ' + (map[y][x] === 1 ? 'wall' : 'road');
                
                if (x === carPos.x && y === carPos.y) {
                    cell.innerText = getCarEmoji();
                } 
                else if (x === obstaclePos.x && y === obstaclePos.y) {
                    cell.innerText = '🚔';
                    cell.classList.add('obstacle-cell');
                }
                else if (map[y][x] === 2) {
                    cell.innerText = '🏁';
                }
                else if (map[y][x] === 1) {
                    cell.innerText = '🚧';
                }
                canvas.appendChild(cell);
            }
        }
    }

    function getCarEmoji() {
        if (carDir === "east") return "🚗";
        if (carDir === "west") return "🏎️";
        if (carDir === "south") return "🚙";
        return "🚕";
    }

    function moveObstacle() {
        obstaclePos.y += obstacleDir;
        if (obstaclePos.y >= 4 || obstaclePos.y <= 0) {
            obstacleDir *= -1; 
        }
    }

    // --- PROGRESS SAVING ---
    function saveProgress(xp, lvlCompleted) {
        let data = new FormData();
        data.append('xp', xp);
        data.append('level_completed', lvlCompleted);

        fetch('update_progress.php', {
            method: 'POST',
            body: data
        }).then(() => console.log("Cloud Save Successful"));
    }

    // --- PLAYER COMMANDS ---
    async function wait() {
        moveObstacle();
        renderMap();
        document.getElementById('status').innerText = "Waiting...";
        await new Promise(r => setTimeout(r, 600));
    }

    async function move(steps) {
        for (let i = 0; i < steps; i++) {
            let nextX = carPos.x;
            let nextY = carPos.y;

            if (carDir === "east") nextX++;
            else if (carDir === "west") nextX--;
            else if (carDir === "north") nextY--;
            else if (carDir === "south") nextY++;

            if (nextX < 0 || nextX >= gridSize || nextY < 0 || nextY >= gridSize || map[nextY][nextX] === 1) {
                document.getElementById('status').innerText = "💥 CRASH! You hit a wall!";
                throw "Crashed";
            }

            carPos.x = nextX;
            carPos.y = nextY;
            
            if (carPos.x === obstaclePos.x && carPos.y === obstaclePos.y) {
                renderMap();
                document.getElementById('status').innerText = "🚔 BUSTED! The patrol caught you!";
                throw "Crashed";
            }

            moveObstacle();
            renderMap();
            await new Promise(r => setTimeout(r, 600));

            if (map[carPos.y][carPos.x] === 2) {
                document.getElementById('status').className = "status-msg text-success";
                document.getElementById('status').innerText = "🏆 WINNER! +250 XP Gained!";
                saveProgress(250, 2); // Saves to Database
                return;
            }
        }
    }

    function turn(dir) {
        if (dir === "right") {
            if (carDir === "east") carDir = "south";
            else if (carDir === "south") carDir = "west";
            else if (carDir === "west") carDir = "north";
            else carDir = "east";
        } else if (dir === "left") {
            if (carDir === "east") carDir = "north";
            else if (carDir === "north") carDir = "west";
            else if (carDir === "west") carDir = "south";
            else carDir = "east";
        }
        renderMap();
    }

    async function runCode() {
        initGame();
        const code = document.getElementById('editor').value;
        try {
            await eval("(async () => {" + code + "})()");
        } catch (err) {
            if (err !== "Crashed") {
                document.getElementById('status').innerText = "❌ Error: Check your code!";
            }
        }
    }

    initGame();
</script>

</body>
</html>