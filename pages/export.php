<?php
/**
 * Data Export Page
 * 
 * Export workout data in CSV or JSON format
 */

require_once __DIR__ . '/../includes/analytics.php';

$userId = currentUserId();

// Handle export requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    $format = $_POST['format'] ?? 'csv';
    $exportType = $_POST['export_type'] ?? 'workouts';
    
    if ($exportType === 'workouts') {
        $data = exportWorkoutData($userId);
        
        if ($format === 'csv') {
            // Flatten data for CSV
            $flatData = [];
            foreach ($data as $workout) {
                $flatData[] = [
                    'date' => $workout['date'],
                    'workout_name' => $workout['workout_name'],
                    'start_time' => $workout['start_time'],
                    'end_time' => $workout['end_time'],
                    'duration_minutes' => $workout['duration_minutes'],
                    'total_volume_lbs' => $workout['total_volume'],
                    'total_sets' => $workout['total_sets'],
                    'exercises' => implode(', ', $workout['exercises'])
                ];
            }
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="workouts-' . date('Y-m-d') . '.csv"');
            echo formatAsCSV($flatData);
            exit;
            
        } else {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="workouts-' . date('Y-m-d') . '.json"');
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit;
        }
        
    } elseif ($exportType === 'sets') {
        // Export individual sets
        $sets = dbFetchAll(
            "SELECT 
                DATE(FROM_UNIXTIME(w.started_at/1000)) as workout_date,
                COALESCE(NULLIF(w.notes, ''), 'Freestyle') as workout_name,
                e.name as exercise,
                e.category,
                ws.set_number,
                ws.reps,
                ws.weight,
                ws.weight * ws.reps as volume
             FROM workout_sets ws
             JOIN workouts w ON ws.workout_id = w.id
             JOIN exercises e ON ws.exercise_id = e.id
             WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL
             ORDER BY w.started_at DESC, ws.id ASC",
            [$userId]
        );
        
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="sets-' . date('Y-m-d') . '.csv"');
            echo formatAsCSV($sets);
            exit;
        } else {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="sets-' . date('Y-m-d') . '.json"');
            echo json_encode($sets, JSON_PRETTY_PRINT);
            exit;
        }
    }
}

// Get stats for display
$totalWorkouts = dbFetchOne(
    "SELECT COUNT(*) as count FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$totalSets = dbFetchOne(
    "SELECT COUNT(*) as count FROM workout_sets ws 
     JOIN workouts w ON ws.workout_id = w.id 
     WHERE w.user_id = ? AND w.ended_at IS NOT NULL AND ws.completed_at IS NOT NULL",
    [$userId]
)['count'] ?? 0;

$dateRange = dbFetchOne(
    "SELECT 
        MIN(DATE(FROM_UNIXTIME(started_at/1000))) as earliest,
        MAX(DATE(FROM_UNIXTIME(started_at/1000))) as latest
     FROM workouts WHERE user_id = ? AND ended_at IS NOT NULL",
    [$userId]
);

renderPage('Export Data', function() use ($totalWorkouts, $totalSets, $dateRange) {
    ?>
    <h1>Export Data</h1>
    
    <div class="card" style="padding: 20px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center;">
            <div>
                <div style="font-size: 24px; font-weight: 700; color: var(--accent);"><?= $totalWorkouts ?></div>
                <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Workouts</div>
            </div>
            <div>
                <div style="font-size: 24px; font-weight: 700; color: var(--accent);"><?= number_format($totalSets) ?></div>
                <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Sets</div>
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 600; color: var(--text);">
                    <?= $dateRange['earliest'] ? date('M Y', strtotime($dateRange['earliest'])) : 'N/A' ?>
                </div>
                <div style="font-size: 11px; color: var(--text-dim); text-transform: uppercase;">Since</div>
            </div>
        </div>
    </div>
    
    <div class="card" style="padding: 20px; margin-bottom: 24px;">
        <h2 style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Export Workouts</h2>
        
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="export_type" value="workouts">
            
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">Format</div>
                <div style="display: flex; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; background: var(--surface-elevated); border: 1px solid var(--border); border-radius: 8px; flex: 1;">
                        <input type="radio" name="format" value="csv" checked style="accent-color: var(--accent);">
                        <span>CSV (Spreadsheet)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; background: var(--surface-elevated); border: 1px solid var(--border); border-radius: 8px; flex: 1;">
                        <input type="radio" name="format" value="json" style="accent-color: var(--accent);">
                        <span>JSON (Data)</span>
                    </label>
                </div>
            </div>
            
            <div style="background: var(--surface-elevated); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">Includes:</div>
                <ul style="font-size: 13px; color: var(--text); margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Workout dates and names</li>
                    <li>Duration and volume totals</li>
                    <li>List of exercises performed</li>
                </ul>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                📥 Download Workouts
            </button>
        </form>
    </div>
    
    <div class="card" style="padding: 20px;">
        <h2 style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Export Individual Sets</h2>
        
        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="export_type" value="sets">
            
            <div style="margin-bottom: 20px;">
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">Format</div>
                <div style="display: flex; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; background: var(--surface-elevated); border: 1px solid var(--border); border-radius: 8px; flex: 1;">
                        <input type="radio" name="format" value="csv" checked style="accent-color: var(--accent);">
                        <span>CSV (Spreadsheet)</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; background: var(--surface-elevated); border: 1px solid var(--border); border-radius: 8px; flex: 1;">
                        <input type="radio" name="format" value="json" style="accent-color: var(--accent);">
                        <span>JSON (Data)</span>
                    </label>
                </div>
            </div>
            
            <div style="background: var(--surface-elevated); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 8px;">Includes:</div>
                <ul style="font-size: 13px; color: var(--text); margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Every set with reps and weight</li>
                    <li>Exercise names and categories</li>
                    <li>Volume calculations per set</li>
                </ul>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                📥 Download Sets
            </button>
        </form>
    </div>
    <?php
});
