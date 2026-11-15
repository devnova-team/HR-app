<?php

/*
 * Burnout level = employee's completed tasks in the current sprint vs. the average.
 *   ratio = tasksCompleted / max(tasksAverage, 1)
 *   ratio >= poor_ratio    -> poor
 *   ratio >= warning_ratio -> warning
 *   otherwise              -> good
 * Below min_tasks completed tasks the level is always "good" (avoids noise on tiny numbers).
 * progress (0.0-1.0) = min(1, ratio / poor_ratio): how close the employee is to "poor".
 */
return [
    'warning_ratio' => (float) env('BURNOUT_WARNING_RATIO', 1.5),
    'poor_ratio' => (float) env('BURNOUT_POOR_RATIO', 2.0),
    'min_tasks' => (int) env('BURNOUT_MIN_TASKS', 3),
];
