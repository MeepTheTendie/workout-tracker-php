<?php
/**
 * Cardio Tracker Page
 * Log AM/PM bike sessions per trainer directive
 */

$userId = currentUserId();
$today = strtotime('today') * 1000;
$tomorrow = strtotime('tomorrow') * 1000;

// Get today's cardio sessions
$todaySessions = dbFetchAll(
    "SELECT * FROM cardio_sessions 
     WHERE user_id = ? AND completed_at >= ? AND completed_at < ?
     ORDER BY completed_at DESC",
    [$userId, $today, $tomorrow]
);

$amSession = null;
$pmSession = null;
foreach ($todaySessions as $session) {
    $hour = date('G', $session['completed_at'] / 1000);
    if ($hour < 12 && !$amSession) {
        $amSession = $session;
    } elseif ($hour >= 12 && !$pmSession) {
        $pmSession = $session;
    }
}

// Get recent history (last 7 days)
$weekAgo = (strtotime('today') - (7 * 86400)) * 1000;
$recentSessions = dbFetchAll(
    "SELECT * FROM cardio_sessions 
     WHERE user_id = ? AND completed_at >= ?
     ORDER BY completed_at DESC
     LIMIT 20",
    [$userId, $weekAgo]
);

// Calculate streak
$streak = 0;
$currentDate = strtotime('today');
while (true) {
    $dayStart = $currentDate * 1000;
    $dayEnd = ($currentDate + 86400) * 1000;
    $count = dbFetchOne(
        "SELECT COUNT(*) as count FROM cardio_sessions 
         WHERE user_id = ? AND completed_at >= ? AND completed_at < ?",
        [$userId, $dayStart, $dayEnd]
    );
    if ($count['count'] >= 2) {
        $streak++;
        $currentDate -= 86400;
    } else {
        break;
    }
}

renderPage('Cardio Tracker', function() use ($amSession, $pmSession, $recentSessions, $streak, $todaySessions) {
    ?>
    <h1>🚴 Cardio Tracker</h1>
    
    <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="font-size: 14px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Current Streak</div>
        <div style="font-size: 48px; font-weight: 700; color: var(--accent);"><?= $streak ?> days</div>
        <div style="font-size: 14px; color: var(--text-dim); margin-top: 4px;">AM + PM sessions completed</div>
    </div>
    
    <h2 style="margin-bottom: 16px;">Today's Sessions</h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px;">
        <!-- AM Session -->
        <div style="background: var(--bg-card); border: 2px solid <?= $amSession ? 'var(--success)' : 'var(--border)' ?>; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">🌅</div>
            <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">AM Session</div>
            <div style="font-size: 14px; color: var(--text-dim); margin-bottom: 16px;">Before 12 PM</div>
            
            <?php if ($amSession): ?>
                <div style="font-size: 32px; font-weight: 700; color: var(--success); margin-bottom: 8px;">✓ DONE</div>
                <div style="font-size: 14px; color: var(--text-dim);">
                    <?= $amSession['duration_minutes'] ?> min @ <?= $amSession['resistance_level'] ?? '?' ?> resistance<br>
                    <?= $amSession['calories_burned'] ? $amSession['calories_burned'] . ' cal' : '' ?>
                </div>
            <?php else: ?>
                <form method="POST" action="/action/cardio/add">
                    <?= csrfField() ?>
                    <input type="hidden" name="session_type" value="bike">
                    <input type="hidden" name="duration_minutes" value="15">
                    <input type="hidden" name="notes" value="AM Session">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">LOG AM</button>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- PM Session -->
        <div style="background: var(--bg-card); border: 2px solid <?= $pmSession ? 'var(--success)' : 'var(--border)' ?>; border-radius: 12px; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">🌙</div>
            <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">PM Session</div>
            <div style="font-size: 14px; color: var(--text-dim); margin-bottom: 16px;">After 12 PM</div>
            
            <?php if ($pmSession): ?>
                <div style="font-size: 32px; font-weight: 700; color: var(--success); margin-bottom: 8px;">✓ DONE</div>
                <div style="font-size: 14px; color: var(--text-dim);">
                    <?= $pmSession['duration_minutes'] ?> min @ <?= $pmSession['resistance_level'] ?? '?' ?> resistance<br>
                    <?= $pmSession['calories_burned'] ? $pmSession['calories_burned'] . ' cal' : '' ?>
                </div>
            <?php else: ?>
                <form method="POST" action="/action/cardio/add">
                    <?= csrfField() ?>
                    <input type="hidden" name="session_type" value="bike">
                    <input type="hidden" name="duration_minutes" value="15">
                    <input type="hidden" name="notes" value="PM Session">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">LOG PM</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Detailed Log Form -->
    <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 32px;">
        <h3 style="margin-bottom: 16px;">Log Detailed Session</h3>
        <form method="POST" action="/action/cardio/add">
            <?= csrfField() ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 4px;">SESSION</label>
                    <select name="notes" class="form-select" style="width: 100%;">
                        <option value="AM Session">AM Session (Before 12 PM)</option>
                        <option value="PM Session">PM Session (After 12 PM)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 4px;">TYPE</label>
                    <select name="session_type" class="form-select" style="width: 100%;">
                        <option value="bike">Exercise Bike</option>
                        <option value="treadmill">Treadmill</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 4px;">DURATION (MIN)</label>
                    <input type="number" name="duration_minutes" value="15" min="1" max="60" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 4px;">RESISTANCE (1-10)</label>
                    <input type="number" name="resistance_level" value="5" min="1" max="10" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 4px;">CALORIES</label>
                    <input type="number" name="calories_burned" placeholder="150" min="0" class="form-input" style="width: 100%;">
                </div>
            </div>
            <button type="submit" class="btn btn-success" style="width: 100%;">LOG SESSION</button>
        </form>
    </div>
    
    <!-- Recent History -->
    <h2 style="margin-bottom: 16px;">Recent History</h2>
    
    <?php if (empty($recentSessions)): ?>
        <p style="color: var(--text-dim); text-align: center; padding: 32px;">No cardio logged yet. Start with today's AM session!</p>
    <?php else: ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
            <?php 
            $currentDay = null;
            foreach ($recentSessions as $session): 
                $sessionDay = date('M j', $session['completed_at'] / 1000);
                if ($sessionDay !== $currentDay):
                    $currentDay = $sessionDay;
            ?>
                <div style="background: var(--bg); padding: 12px 20px; font-weight: 700; border-bottom: 1px solid var(--border);">
                    <?= $sessionDay ?>
                </div>
            <?php endif; ?>
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600;">
                            <?= $session['notes'] ?? 'Cardio Session' ?> 
                            <span style="color: var(--text-dim); font-weight: 400;">(<?= date('g:i A', $session['completed_at'] / 1000) ?>)</span>
                        </div>
                        <div style="font-size: 14px; color: var(--text-dim);">
                            <?= $session['duration_minutes'] ?> min 
                            <?= $session['session_type'] ? '• ' . ucfirst($session['session_type']) : '' ?>
                            <?= $session['resistance_level'] ? '• Resistance ' . $session['resistance_level'] : '' ?>
                        </div>
                    </div>
                    <?php if ($session['calories_burned']): ?>
                        <div style="text-align: right;">
                            <div style="font-size: 24px; font-weight: 700; color: var(--accent);"><?= $session['calories_burned'] ?></div>
                            <div style="font-size: 12px; color: var(--text-dim);">calories</div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 32px; padding: 20px; background: var(--bg); border: 1px dashed var(--border); border-radius: 8px; text-align: center;">
        <div style="font-size: 14px; color: var(--text-dim); margin-bottom: 8px;">Trainer Directive</div>
        <div style="font-weight: 700; color: var(--accent);">15 min AM + 15 min PM. Every day. No exceptions.</div>
    </div>
    <?php
});
