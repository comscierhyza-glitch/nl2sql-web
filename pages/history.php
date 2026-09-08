<?php

session_start();

?>

<div class="page-card">

    <h1>
        <i class="fas fa-history"></i>
        Query History
    </h1>

    <p class="page-description">
        View all recently generated SQL queries.
    </p>

    <hr>

    <?php if (empty($_SESSION["history"])): ?>

        <div class="empty-history">

            <i class="fas fa-clock"></i>

            <h3>No Query History</h3>

            <p>Your generated SQL queries will appear here.</p>

        </div>

    <?php else: ?>

        <?php foreach (array_reverse($_SESSION["history"]) as $history): ?>

            <div class="history-card">

                <div class="history-header">

                    <span class="history-command">

                        <?= htmlspecialchars($history["command"]) ?>

                    </span>

                    <span class="history-time">

                        <?= htmlspecialchars($history["time"]) ?>

                    </span>

                </div>

                <div class="history-body">

                    <strong>Natural Language</strong>

                    <p>

                        <?= htmlspecialchars($history["input"]) ?>

                    </p>

                    <strong>Generated SQL</strong>

                    <pre>

<?= htmlspecialchars($history["sql"]) ?>

                </pre>

                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>