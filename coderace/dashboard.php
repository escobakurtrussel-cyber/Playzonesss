<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}

$current_user = $_SESSION['username'];

// Fetch user data
$user_query = $conn->query("SELECT xp, max_level FROM users WHERE username = '$current_user'");
$user_data = $user_query->fetch_assoc();

$xp = $user_data['xp'];
$max_lvl = $user_data['max_level'];

$_SESSION['xp'] = $xp;
$_SESSION['max_level'] = $max_lvl;

// Rank Logic
$rank = "Novice Coder";
$rank_icon = "fa-baby";
if ($xp >= 500) { $rank = "Code Warrior"; $rank_icon = "fa-shield-halved"; }
if ($xp >= 1500) { $rank = "Logic Master"; $rank_icon = "fa-brain"; }
if ($xp >= 3000) { $rank = "Master Quest"; $rank_icon = "fa-crown"; }

// Leaderboard
$leaderboard_query = $conn->query("SELECT username as name, xp, max_level as lvl FROM users ORDER BY xp DESC LIMIT 10");
$leaderboard = [];
while($row = $leaderboard_query->fetch_assoc()) {
    $leaderboard[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnQuest | Dashboard v2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --dark-green: #00441B;
            --mid-green: #238845;
            --accent-green: #41AB5D;
            --soft-green: #E5F5E0;
            --main-bg: #F7FCF5;
        }
        body { background: var(--main-bg); font-family: 'Inter', sans-serif; padding-bottom: 50px; }
        .nav-custom { background: white; border-bottom: 2px solid var(--soft-green); padding: 15px 0; margin-bottom: 30px; }
        
        /* Updated Game Card Styles */
        .mission-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .m-card { 
            background: #111; color: white; border-radius: 20px; padding: 30px; 
            border-left: 8px solid; transition: 0.3s; text-decoration: none; display: block;
        }
        .m-card:hover { transform: scale(1.02); color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .m-taxi { border-color: #198754; }
        .m-race { border-color: #0d6efd; }
        
        .stat-card { 
            background: white; border-radius: 20px; padding: 25px; 
            box-shadow: 0 10px 25px rgba(0, 68, 27, 0.05); 
            border: 1px solid var(--soft-green); height: 100%;
        }
        .xp-bar-container { height: 12px; background: var(--soft-green); border-radius: 10px; overflow: hidden; }
        .xp-bar-fill { background: linear-gradient(90deg, var(--mid-green), var(--accent-green)); height: 100%; }
        .leaderboard-table { background: white; border-radius: 20px; overflow: hidden; border: 1px solid var(--soft-green); }
    </style>
</head>
<body>

    <nav class="nav-custom">
        <div class="container d-flex justify-content-between align-items-center">
            <h3 class="fw-bold text-success mb-0"><i class="fa-solid fa-leaf"></i> LearnQuest</h3>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold">Hi, <?php echo $current_user; ?>!</span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-flag-checkered me-2 text-success"></i>Choose Your Mode</h4>
        
        <div class="mission-grid">
            <a href="game.php?level=<?php echo $max_lvl; ?>" class="m-card m-taxi shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold mb-1 text-success"><i class="fa-solid fa-taxi me-2"></i> Taxi Mission</h2>
                        <p class="opacity-75">Gamitin ang logic para i-deliver ang pasahero.</p>
                    </div>
                    <span class="badge bg-success px-3">CLASSIC</span>
                </div>
                <div class="btn btn-success w-100 mt-3 fw-bold">CONTINUE MISSION</div>
            </a>

            <a href="race_mode.php" class="m-card m-race shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-bolt me-2"></i> Speed Race</h2>
                        <p class="opacity-75">Limitadong blocks! Makarating ka kaya sa dulo?</p>
                    </div>
                    <span class="badge bg-primary px-3">NEW</span>
                </div>
                <div class="btn btn-primary w-100 mt-3 fw-bold">START SPEED RACE</div>
            </a>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-8">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-1 text-success"><i class="fa-solid <?php echo $rank_icon; ?>"></i></div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Current Rank</h5>
                                <span class="badge bg-primary px-3 py-2 rounded-pill"><?php echo $rank; ?></span>
                            </div>
                        </div>
                        <div class="text-end">
                            <h2 class="display-6 fw-bold text-success mb-0"><?php echo number_format($xp); ?> XP</h2>
                            <p class="text-muted small mb-0">Total Experience</p>
                        </div>
                    </div>
                    <div class="xp-bar-container">
                        <div class="xp-bar-fill" style="width: <?php echo min(($xp / 5000) * 100, 100); ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center bg-success text-white d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase small opacity-75 fw-bold">Max Level Reached</h6>
                    <h1 class="display-2 fw-bold mb-0"><?php echo $max_lvl; ?></h1>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-trophy text-warning me-2"></i>Global Leaderboard</h4>
        <div class="leaderboard-table shadow-sm">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Rank</th>
                        <th>Player Name</th>
                        <th>Level</th>
                        <th class="text-end pe-4">Total XP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $player): 
                        $is_me = ($player['name'] == $current_user); ?>
                    <tr class="<?php echo $is_me ? 'table-primary' : ''; ?>">
                        <td class="ps-4 fw-bold">#<?php echo $index + 1; ?></td>
                        <td>
                            <i class="fa-solid fa-circle-user text-muted me-2"></i>
                            <span class="<?php echo $is_me ? 'fw-bold text-primary' : ''; ?>">
                                <?php echo $player['name']; ?> <?php if($is_me) echo "(You)"; ?>
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark border">Lvl <?php echo $player['lvl']; ?></span></td>
                        <td class="text-end pe-4 text-success fw-bold"><?php echo number_format($player['xp']); ?> XP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>