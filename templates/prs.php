<?php
$prs = localApi('prs');
?>
<div class="page-header">
    <div class="page-title">PERSONAL RECORDS</div>
</div>

<div class="section">
    <div class="section-header">
        <span class="section-title">Best Performance</span>
    </div>
    
    <?php if (empty($prs)): ?>
        <div class="empty">No workout data yet</div>
    <?php else: ?>
        <?php foreach ($prs as $pr): ?>
            <div class="card">
                <div style="font-weight: 700; font-size: 14px; margin-bottom: 8px;">
                    <?= strtoupper(h($pr['exercise_name'])) ?>
                </div>
                <div style="display: flex; gap: 16px; font-size: 12px;">
                    <div>
                        <div style="color: var(--text-dim);">Best Weight</div>
                        <div style="font-weight: 700;"><?= h($pr['max_weight']) ?> lbs</div>
                    </div>
                    <div>
                        <div style="color: var(--text-dim);">Best Reps</div>
                        <div style="font-weight: 700;"><?= h($pr['max_reps']) ?> reps</div>
                    </div>
                    <div>
                        <div style="color: var(--text-dim);">Best Set</div>
                        <div style="font-weight: 700;"><?= h($pr['max_volume']) ?> lbs</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
