<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
if (!isset($_SESSION['xp'])) $_SESSION['xp'] = 0;
if (!isset($_SESSION['max_level'])) $_SESSION['max_level'] = 1;

$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;

// Win Handler logic
if (isset($_GET['status']) && $_GET['status'] == 'win') {
    $completed_level = (int)$_GET['prev_level'];
    if ($completed_level >= $_SESSION['max_level']) {
        $_SESSION['xp'] += 100;
        $_SESSION['max_level'] = $completed_level + 1;
        $u = $_SESSION['username'];
        $nxp = $_SESSION['xp'];
        $nlvl = $_SESSION['max_level'];
        $conn->query("UPDATE users SET xp=$nxp, max_level=$nlvl WHERE username='$u'");
    }
    header("Location: game.php?level=" . $level);
    exit();
}

$levels_config = [
    1 => ['title' => 'The Starting Line', 'mission' => 'Straight dash! Move 4 times.', 'map' => [[0,0,0,0,2],[1,1,1,1,1],[1,1,1,1,1],[1,1,1,1,1],[1,1,1,1,1]]],
    2 => ['title' => 'Right Turn Only', 'mission' => 'The road bends. Turn at the right time.', 'map' => [[0,0,1,1,1],[1,0,1,1,1],[1,0,0,2,1],[1,1,1,1,1],[1,1,1,1,1]]],
    3 => ['title' => 'The S-Curve', 'mission' => 'Snake through the barricades.', 'map' => [[0,0,1,1,1],[1,0,0,1,1],[1,1,0,0,2],[1,1,1,1,1],[1,1,1,1,1]]],
    4 => ['title' => 'The U-Turn Maze', 'mission' => 'Go around the center block.', 'map' => [[0,0,0,0,0],[1,1,1,1,0],[2,0,0,0,0],[1,1,1,1,1],[1,1,1,1,1]]],
    5 => ['title' => 'Dead End Logic', 'mission' => 'Find the small gap in the middle.', 'map' => [[0,1,1,1,1],[0,0,0,0,1],[1,1,1,0,1],[1,1,1,0,0],[1,1,1,1,2]]],
    6 => ['title' => 'The Spiral', 'mission' => 'Hug the walls to reach the center.', 'map' => [[0,0,0,0,0],[1,1,1,1,0],[0,2,1,1,0],[0,0,0,0,0],[1,1,1,1,1]]],
    7 => ['title' => 'Uber Beginner', 'mission' => 'PICKUP: Stop at 🙋‍♂️ and use pickUp(); then go to 🏠!', 'map' => [[0,0,3,1,1],[1,0,1,1,1],[1,0,0,0,2],[1,1,1,1,1],[1,1,1,1,1]]],
    8 => ['title' => 'Zigzag Pickup', 'mission' => 'Fetch the passenger in the alley using pickUp();', 'map' => [[0,1,1,1,1],[0,0,3,1,1],[1,1,0,1,1],[1,1,0,0,2],[1,1,1,1,1]]],
    9 => ['title' => 'The Late Passenger', 'mission' => 'HURRY! Pick up 🙋‍♂️ and deliver to 🏠 in 60s!', 'map' => [[0,0,0,0,3],[1,1,1,1,0],[2,0,0,0,0],[0,1,1,1,1],[0,0,0,0,0]]],
    10 => ['title' => 'Master Chauffeur', 'mission' => 'Final Exam: Complex maze delivery!', 'map' => [[0,0,1,1,1],[1,0,0,0,0],[1,1,1,1,3],[0,0,0,0,0],[2,1,1,1,1]]]
];

$config = $levels_config[$level] ?? $levels_config[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LearnQuest | Level <?php echo $level; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --dark-green: #00441B; --mid-green: #238845; --light-green: #A1D99B; --bg: #F7FCF5; }
        body { background: var(--bg); font-family: 'Inter', sans-serif; display: flex; overflow: hidden; }
        .sidebar { width: 260px; height: 100vh; background: var(--dark-green); color: white; position: fixed; padding: 30px 20px; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 30px; height: 100vh; display: flex; flex-direction: column; }
        #game-canvas { width: 350px; height: 350px; background: #1a1a1a; display: grid; grid-template-columns: repeat(5, 1fr); border: 8px solid var(--dark-green); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .cell { border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-size: 2rem; transition: all 0.2s; }
        .blocks-inventory, .drop-zone { background: white; border: 2px dashed var(--light-green); border-radius: 15px; padding: 15px; min-height: 250px; }
        .drop-zone { border-style: solid; background: #fdfdfd; border-color: var(--mid-green); height: 350px; overflow-y: auto; }
        .code-block { background: var(--mid-green); color: white; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; cursor: grab; font-family: 'Courier New', monospace; font-weight: bold; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 0 #1a6634; }
        .code-block:active { transform: translateY(2px); box-shadow: 0 2px 0 #1a6634; }
        .btn-run { background: #ffc107; color: #000; border: none; padding: 15px; border-radius: 12px; font-weight: 800; width: 100%; transition: 0.3s; margin-top: 10px; border-bottom: 5px solid #d39e00; }
        .btn-run:hover { background: #ffca2c; }
        .btn-run:active { transform: translateY(3px); border-bottom: 2px solid #d39e00; }
        .timer-box { font-size: 1.8rem; font-weight: 800; color: #dc3545; background: white; padding: 5px 15px; border-radius: 10px; border: 2px solid #dc3545; display: inline-block; }
        .blink { animation: blinker 0.6s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
        .status-card { transition: all 0.3s; }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
    <h3 class="fw-bold mb-5"><i class="fa-solid fa-taxi me-2"></i>LearnQuest</h3>
    <nav class="nav flex-column mb-auto">
        <a class="nav-link text-white mb-3" href="dashboard.php"><i class="fa-solid fa-house me-2"></i> Dashboard</a>
        <div class="small opacity-50 text-uppercase fw-bold">Level <?php echo $level; ?></div>
        
        <?php if ($level == 9): ?>
        <div id="timer-ui" class="mt-3 text-center">
            <div id="countdown" class="timer-box">01:00</div>
        </div>
        <?php endif; ?>

        <div id="passenger-status" class="mt-4 p-2 rounded bg-dark text-center fw-bold text-warning" style="font-size: 0.8rem;">
            <?php echo ($level >= 7) ? "NO PASSENGER ❌" : "WALKTHROUGH"; ?>
        </div>
    </nav>
</div>

<div class="main-content">
    <div class="bg-white rounded-4 p-4 shadow-sm border mb-4 status-card" id="mission-box">
        <h2 class="fw-bold"><?php echo $config['title']; ?></h2>
        <p class="text-muted mb-0"><i class="fa-solid fa-circle-info text-success"></i> <?php echo $config['mission']; ?></p>
    </div>

    <div class="row g-4 flex-grow-1">
        <div class="col-md-4 text-center">
            <div class="bg-white p-4 rounded-4 shadow-sm border h-100">
                <div id="game-canvas" class="mx-auto"></div>
                <div id="status-text" class="mt-3 fw-bold text-success">SYSTEM READY</div>
                <button class="btn-run" onclick="runLogic()">START MISSION <i class="fa-solid fa-play ms-2"></i></button>
                <button class="btn btn-sm btn-outline-secondary mt-3 w-100" onclick="initGame()">RESET LEVEL</button>
            </div>
        </div>
        
        <div class="col-md-3">
            <h6 class="fw-bold text-muted small"><i class="fa-solid fa-box-open"></i> AVAILABLE BLOCKS</h6>
            <div class="blocks-inventory shadow-sm" id="inventory">
                <div class="code-block" draggable="true" ondragstart="drag(event)" data-cmd="move">move();</div>
                <div class="code-block" draggable="true" ondragstart="drag(event)" data-cmd="right">turnRight();</div>
                <div class="code-block" draggable="true" ondragstart="drag(event)" data-cmd="left">turnLeft();</div>
                <div class="code-block" draggable="true" ondragstart="drag(event)" data-cmd="pickup" style="background:#e67e22; box-shadow: 0 4px 0 #a35d1a;">pickUp();</div>
            </div>
        </div>

        <div class="col-md-5">
            <h6 class="fw-bold text-muted small"><i class="fa-solid fa-code"></i> SEQUENCE</h6>
            <div class="drop-zone shadow-sm" id="logic-sequence" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
        </div>
    </div>
</div>

<script>
    let carPos = { x: 0, y: 0 }; 
    let carDir = "east"; 
    let hasPassenger = false;
    let isMoving = false; 
    let timeLeft = 60;
    let timerInterval = null;

    const currentLevel = <?php echo $level; ?>;
    const mapData = <?php echo json_encode($config['map']); ?>;

    // AUDIO CONTEXT - Para sigurado ang sounds kahit walang MP3 files
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function playBeep(freq, type, duration) {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    }

    function soundEngine() { playBeep(150, 'sawtooth', 0.2); }
    function soundPickup() { playBeep(880, 'sine', 0.1); setTimeout(() => playBeep(1200, 'sine', 0.2), 100); }
    function soundWin() { playBeep(523, 'sine', 0.1); setTimeout(() => playBeep(659, 'sine', 0.1), 150); setTimeout(() => playBeep(783, 'sine', 0.3), 300); }
    function soundCrash() { playBeep(100, 'square', 0.4); }

    window.onload = () => { if(currentLevel === 9) startTimer(); renderMap(); };

    function startTimer() {
        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            timeLeft--;
            let display = document.getElementById('countdown');
            if(display) {
                display.innerText = `00:${timeLeft.toString().padStart(2, '0')}`;
                if(timeLeft <= 10) display.classList.add('blink');
                if(timeLeft <= 0) {
                    clearInterval(timerInterval);
                    soundCrash();
                    updateStatus("TIME'S UP!", "text-danger");
                }
            }
        }, 1000);
    }

    function drag(ev) { ev.dataTransfer.setData("cmd", ev.target.getAttribute("data-cmd")); ev.dataTransfer.setData("txt", ev.target.innerText); }
    function allowDrop(ev) { ev.preventDefault(); }
    function drop(ev) {
        ev.preventDefault();
        const cmd = ev.dataTransfer.getData("cmd");
        const txt = ev.dataTransfer.getData("txt");
        const seq = document.getElementById('logic-sequence');
        const newBlock = document.createElement('div');
        newBlock.className = 'code-block';
        newBlock.setAttribute('data-cmd', cmd);
        if(cmd === 'pickup') newBlock.style.background = "#e67e22";
        newBlock.innerHTML = `${txt} <i class="fa-solid fa-trash-can remove-btn ms-2" onclick="this.parentElement.remove()" style="cursor:pointer; opacity:0.7"></i>`;
        seq.appendChild(newBlock);
    }

    function updateStatus(msg, colorClass) {
        const st = document.getElementById('status-text');
        st.innerText = msg;
        st.className = "mt-3 fw-bold " + colorClass;
    }

    function initGame() { 
        carPos = { x: 0, y: 0 }; carDir = "east"; hasPassenger = false; isMoving = false;
        updateStatus("SYSTEM READY", "text-success");
        if(currentLevel >= 7) document.getElementById('passenger-status').innerText = "NO PASSENGER ❌";
        if(currentLevel === 9) { timeLeft = 60; startTimer(); }
        renderMap(); 
    }

    function renderMap() {
        const canvas = document.getElementById('game-canvas'); canvas.innerHTML = '';
        mapData.forEach((row, y) => row.forEach((val, x) => {
            const cell = document.createElement('div'); cell.className = 'cell';
            if (x === carPos.x && y === carPos.y) { 
                cell.innerText = hasPassenger ? "🚕" : "🚗"; 
                cell.style.transform = getRot();
                cell.style.filter = "drop-shadow(0 0 5px gold)";
            }
            else if (val === 1) cell.innerText = "🚧"; 
            else if (val === 2) cell.innerText = (currentLevel >= 7) ? "🏠" : "🏁";
            else if (val === 3 && !hasPassenger) cell.innerText = "🙋‍♂️";
            canvas.appendChild(cell);
        }));
    }

    function getRot() { return carDir === "south" ? "rotate(90deg)" : carDir === "west" ? "rotate(180deg)" : carDir === "north" ? "rotate(-90deg)" : "rotate(0deg)"; }

    async function runLogic() {
        if(isMoving || (currentLevel === 9 && timeLeft <= 0)) return;
        isMoving = true;
        soundEngine();

        const blocks = document.querySelectorAll('#logic-sequence .code-block');
        for (let b of blocks) {
            if(!isMoving || (currentLevel === 9 && timeLeft <= 0)) break;
            const cmd = b.getAttribute('data-cmd');
            try {
                if (cmd === "move") await moveCar();
                else if (cmd === "right") { carDir = turn(carDir, "R"); renderMap(); }
                else if (cmd === "left") { carDir = turn(carDir, "L"); renderMap(); }
                else if (cmd === "pickup") await pickUpPassenger();
                await new Promise(r => setTimeout(r, 500));
            } catch(e) { isMoving = false; return; }
        }
        isMoving = false;
    }

    function turn(curr, side) {
        const d = ["north", "east", "south", "west"];
        let i = d.indexOf(curr);
        return (side === "R") ? d[(i + 1) % 4] : d[(i + 3) % 4];
    }

    async function pickUpPassenger() {
        if (mapData[carPos.y][carPos.x] === 3) {
            hasPassenger = true;
            soundPickup();
            document.getElementById('passenger-status').innerText = "PASSENGER ONBOARD ✅";
            document.getElementById('passenger-status').classList.replace('text-warning', 'text-success');
            renderMap();
        } else {
            updateStatus("PICKUP FAILED: NO ONE HERE", "text-warning");
        }
    }

    async function moveCar() {
        let nX = carPos.x + (carDir === "east" ? 1 : carDir === "west" ? -1 : 0);
        let nY = carPos.y + (carDir === "south" ? 1 : carDir === "north" ? -1 : 0);
        
        if (nX >= 0 && nX < 5 && nY >= 0 && nY < 5 && mapData[nY][nX] !== 1) {
            carPos.x = nX; carPos.y = nY;
            renderMap();
            if (mapData[carPos.y][carPos.x] === 2) {
                if (currentLevel >= 7 && !hasPassenger) {
                    soundCrash();
                    updateStatus("FAILED: NO PASSENGER!", "text-danger");
                    isMoving = false; throw "err";
                }
                handleWin();
            }
        } else { 
            soundCrash();
            updateStatus("BOOM! CRASHED!", "text-danger");
            isMoving = false; throw "err"; 
        }
    }

    function handleWin() {
        clearInterval(timerInterval);
        isMoving = false;
        soundWin();
        updateStatus("MISSION COMPLETE! 🏆", "text-success");
        setTimeout(() => window.location.href = `game.php?level=<?php echo $level+1; ?>&status=win&prev_level=<?php echo $level; ?>`, 1500);
    }
</script>
</body>
</html>