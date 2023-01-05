<?php

namespace leantime\domain\models {

    class reports
    {
        public $sprintId;
        public $projectId;
        public $date;
        public $sum_todos;
        public $sum_open_todos;
        public $sum_progres_todos;
        public $sum_closed_todos;
        public $sum_planned_hours;
        public $sum_estremaining_hours;
        public $sum_logged_hours;
        public $sum_points;
        public $sum_points_done;
        public $sum_points_progress;
        public $sum_points_open;
        public $sum_todos_xs;
        public $sum_todos_s;
        public $sum_todos_m;
        public $sum_todos_l;
        public $sum_todos_xl;
        public $sum_todos_xxl;
        public $sum_todos_none;
        public $tickets;
        public $daily_avg_hours_booked_todo;
        public $daily_avg_hours_booked_point;
        public $daily_avg_hours_planned_todo;
        public $daily_avg_hours_planned_point;
        public $daily_avg_hours_remaining_point;
        public $daily_avg_hours_remaining_todo;
        public $sum_teammembers;

        public function __construct()
        {
        }
    }

}
