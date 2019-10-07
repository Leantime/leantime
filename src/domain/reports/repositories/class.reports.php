<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class reports
    {



        /**
         * __construct - get database connection
         *
         * @access public
         */
        function __construct()
        {

            $this->db = core\db::getInstance();

        }

        /**
         * getSprint - get single sprint
         *
         * @access public
         * @return array
         */
        public function runTicketReport($projectId, $sprintId)
        {

            $query = "SELECT 
                        sprint AS sprintId,
                        projectId,
                        DATE(NOW() - INTERVAL 1 DAY) AS date,
                        COUNT(zp_tickets.id) AS sum_todos,
                        SUM(case when status =3 then 1 else 0 end) as sum_open_todos,
                        SUM(case when (status = 1 OR status = 4 OR status = 2) then 1 else 0 end) as sum_progres_todos,
                        SUM(case when (status < 1) then 1 else 0 end) as sum_closed_todos,
                        SUM(planHours) as sum_planned_hours,
                        SUM(hourRemaining) as sum_estremaining_hours,
                        SUM(zp_timesheets.hours) as sum_logged_hours,
                        SUM(zp_tickets.storypoints) as sum_points,
                        SUM(case when status =3 then zp_tickets.storypoints else 0 end) as sum_points_open,
                        SUM(case when (status = 1 OR status = 4 OR status = 2) then zp_tickets.storypoints else 0 end) as sum_points_progress,
                        SUM(case when (status < 1) then zp_tickets.storypoints else 0 end) as sum_points_done,
                        
                        SUM(case when (zp_tickets.storypoints = 1) then 1 else 0 end) as sum_todos_xs,
                        SUM(case when (zp_tickets.storypoints = 2) then 1 else 0 end) as sum_todos_s,
                        SUM(case when (zp_tickets.storypoints = 3) then 1 else 0 end) as sum_todos_m,
                        SUM(case when (zp_tickets.storypoints = 5) then 1 else 0 end) as sum_todos_l,
                        SUM(case when (zp_tickets.storypoints = 8) then 1 else 0 end) as sum_todos_xl,
                        SUM(case when (zp_tickets.storypoints = 13) then 1 else 0 end) as sum_todos_xxl,
                        SUM(case when (zp_tickets.storypoints = '') then 1 else 0 end) as sum_todos_none,
                        GROUP_CONCAT(zp_tickets.id SEPARATOR ',') as tickets,
                        SUM(zp_timesheets.hours) / COUNT(zp_tickets.id)   AS daily_avg_hours_booked_todo,
                        SUM(zp_timesheets.hours) / SUM(zp_tickets.storypoints) as daily_avg_hours_booked_point,
                        
                        SUM(planHours) / COUNT(zp_tickets.id)   AS daily_avg_hours_planned_todo,
                        SUM(planHours) / SUM(zp_tickets.storypoints) as daily_avg_hours_planned_point,
                        
                        SUM(hourRemaining) / COUNT(zp_tickets.id)   AS daily_avg_hours_remaining_todo,
                        SUM(hourRemaining) / SUM(zp_tickets.storypoints) as daily_avg_hours_remaining_point
                        
                    FROM zp_tickets 
                    LEFT JOIN zp_timesheets ON zp_tickets.id = zp_timesheets.ticketId
                    WHERE projectId = :projectId";

            if($sprintId !== "") {
                $query .= " AND sprint = :sprint GROUP BY projectId, sprint";

            }else{
                $query .= " AND (sprint = '' || sprint = -1 || sprint IS NULL) GROUP BY projectId, sprint";
            }

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);

            if($sprintId !== "") {
                $stmn->bindValue(':sprint', $sprintId, PDO::PARAM_STR);
            }

            $stmn->execute();

            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        public function checkLastReportEntries($projectId)
        {

            $query = "SELECT * FROM zp_stats WHERE DATE(date) = DATE(NOW() - INTERVAL 1 DAY) AND projectId = :projectId LIMIT 2";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\reports");
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;
        }

        public function addReport($report)
        {
            $report = (object) $report;

            $query = "INSERT INTO zp_stats 
                          (sprintId,
                           projectId,
                           date,
                           sum_todos,
                           sum_open_todos,
                           sum_progres_todos,
                           sum_closed_todos,
                           sum_planned_hours,
                           sum_estremaining_hours,
                           sum_logged_hours,
                           sum_points,
                           sum_points_done,
                           sum_points_progress,
                           sum_points_open,
                           sum_todos_xs,
                           sum_todos_s,
                           sum_todos_m,
                           sum_todos_l,
                           sum_todos_xl,
                           sum_todos_xxl,
                           sum_todos_none,
                           tickets,
                           daily_avg_hours_booked_todo,
                           daily_avg_hours_booked_point,
                           daily_avg_hours_planned_todo,
                           daily_avg_hours_planned_point,
                           daily_avg_hours_remaining_point,
                           daily_avg_hours_remaining_todo)
                           
                           VALUES (
                               :sprintId,
                               :projectId,
                               :date,
                               :sum_todos,
                               :sum_open_todos,
                               :sum_progres_todos,
                               :sum_closed_todos,
                               :sum_planned_hours,
                               :sum_estremaining_hours,
                               :sum_logged_hours,
                               :sum_points,
                               :sum_points_done,
                               
                               :sum_points_progress,
                               :sum_points_open,
                               :sum_todos_xs,
                               :sum_todos_s,
                               :sum_todos_m,
                               :sum_todos_l,
                               :sum_todos_xl,
                               :sum_todos_xxl,
                               :sum_todos_none,
                               :tickets,
                               :daily_avg_hours_booked_todo,
                               :daily_avg_hours_booked_point,
                               :daily_avg_hours_planned_todo,
                               :daily_avg_hours_planned_point,
                               :daily_avg_hours_remaining_point,
                               :daily_avg_hours_remaining_todo)";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':sprintId', $report->sprintId, PDO::PARAM_INT);
            $stmn->bindValue(':projectId', $report->projectId, PDO::PARAM_INT);
            $stmn->bindValue(':date', $report->date, PDO::PARAM_STR);
            $stmn->bindValue(':sum_todos', $report->sum_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_open_todos', $report->sum_open_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_progres_todos', $report->sum_progres_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_closed_todos', $report->sum_closed_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_planned_hours', $report->sum_planned_hours, PDO::PARAM_INT);
            $stmn->bindValue(':sum_estremaining_hours', $report->sum_estremaining_hours, PDO::PARAM_INT);
            $stmn->bindValue(':sum_logged_hours', $report->sum_logged_hours, PDO::PARAM_INT);
            $stmn->bindValue(':sum_points', $report->sum_points, PDO::PARAM_INT);
            $stmn->bindValue(':sum_points_done', $report->sum_points_done, PDO::PARAM_INT);

            $stmn->bindValue(':sum_points_progress', $report->sum_points_progress, PDO::PARAM_INT);
            $stmn->bindValue(':sum_points_open', $report->sum_points_open, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_xs', $report->sum_todos_xs, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_s', $report->sum_todos_s, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_m', $report->sum_todos_m, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_l', $report->sum_todos_l, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_xl', $report->sum_todos_xl, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_xxl', $report->sum_todos_xxl, PDO::PARAM_INT);
            $stmn->bindValue(':sum_todos_none', $report->sum_todos_none, PDO::PARAM_INT);
            $stmn->bindValue(':tickets', $report->tickets, PDO::PARAM_STR);
            $stmn->bindValue(':daily_avg_hours_booked_todo', $report->daily_avg_hours_booked_todo, PDO::PARAM_INT);
            $stmn->bindValue(':daily_avg_hours_booked_point', $report->daily_avg_hours_booked_point, PDO::PARAM_INT);
            $stmn->bindValue(':daily_avg_hours_planned_todo', $report->daily_avg_hours_planned_todo, PDO::PARAM_INT);
            $stmn->bindValue(':daily_avg_hours_planned_point', $report->daily_avg_hours_planned_point, PDO::PARAM_INT);
            $stmn->bindValue(':daily_avg_hours_remaining_point', $report->daily_avg_hours_remaining_point, PDO::PARAM_INT);
            $stmn->bindValue(':daily_avg_hours_remaining_todo', $report->daily_avg_hours_remaining_todo, PDO::PARAM_INT);
            $stmn->execute();

            $stmn->closeCursor();

        }

        public function getSprintReport($sprint)
        {

            $query = "SELECT * FROM zp_stats WHERE sprintId = :sprint ORDER BY date ASC";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':sprint', $sprint, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\reports");
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;

        }

        public function getBacklogReport($project)
        {

            $query = "SELECT * FROM zp_stats WHERE projectId = :project AND sprintId = 0 ORDER BY date ASC LIMIT 95 ";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':project', $project, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\reports");
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;

        }

    }

}
