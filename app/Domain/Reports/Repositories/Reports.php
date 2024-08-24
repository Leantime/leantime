<?php

namespace Leantime\Domain\Reports\Repositories {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use PDO;

    /**
     *
     */
    class Reports
    {
        private DbCore $db;

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * getSprint - get single sprint
         *
         * @access public
         * @param $projectId
         * @param $sprintId
         * @return array|bool
         * @throws BindingResolutionException
         */
        public function runTicketReport($projectId, $sprintId): array|bool
        {

            $ticketRepo = app()->make(TicketRepository::class);
            $statusGroups = $ticketRepo->getStatusListGroupedByType($projectId);

            //Ticket Reports
            $query = "SELECT ";

            if ($sprintId !== "") {
                $query .= "sprint AS sprintId,";
            } else {
                $query .= "-1 AS sprintId,";
            }

            $query .= "  projectId,
                        DATE(NOW() - INTERVAL 1 DAY) AS date,
                        COUNT(DISTINCT zp_tickets.id) AS sum_todos,
                        SUM(case when status " . $statusGroups["NEW"] . " then 1 else 0 end) as sum_open_todos,
                        SUM(case when (status " . $statusGroups["INPROGRESS"] . ") then 1 else 0 end) as sum_progres_todos,
                        SUM(case when (status " . $statusGroups["DONE"] . ") then 1 else 0 end) as sum_closed_todos,
                        SUM(planHours) as sum_planned_hours,
                        SUM(hourRemaining) as sum_estremaining_hours,

                        SUM(zp_tickets.storypoints) as sum_points,
                        SUM(case when status " . $statusGroups["NEW"] . " then zp_tickets.storypoints else 0 end) as sum_points_open,
                        SUM(case when (status " . $statusGroups["INPROGRESS"] . ") then zp_tickets.storypoints else 0 end) as sum_points_progress,
                        SUM(case when (status " . $statusGroups["DONE"] . ") then zp_tickets.storypoints else 0 end) as sum_points_done,

                        SUM(case when (zp_tickets.storypoints = 1) then 1 else 0 end) as sum_todos_xs,
                        SUM(case when (zp_tickets.storypoints = 2) then 1 else 0 end) as sum_todos_s,
                        SUM(case when (zp_tickets.storypoints = 3) then 1 else 0 end) as sum_todos_m,
                        SUM(case when (zp_tickets.storypoints = 5) then 1 else 0 end) as sum_todos_l,
                        SUM(case when (zp_tickets.storypoints = 8) then 1 else 0 end) as sum_todos_xl,
                        SUM(case when (zp_tickets.storypoints = 13) then 1 else 0 end) as sum_todos_xxl,
                        SUM(case when (zp_tickets.storypoints = '') then 1 else 0 end) as sum_todos_none,
                        GROUP_CONCAT(zp_tickets.id SEPARATOR ',') as tickets,

                        SUM(planHours) / COUNT(zp_tickets.id)   AS daily_avg_hours_planned_todo,
                        SUM(planHours) / SUM(zp_tickets.storypoints) as daily_avg_hours_planned_point,

                        SUM(hourRemaining) / COUNT(zp_tickets.id)   AS daily_avg_hours_remaining_todo,
                        SUM(hourRemaining) / SUM(zp_tickets.storypoints) as daily_avg_hours_remaining_point

                    FROM zp_tickets

                    WHERE projectId = :projectId AND zp_tickets.type <> 'subtask' AND zp_tickets.type <> 'milestone'";

            if ($sprintId !== "") {
                $query .= " AND sprint = :sprint GROUP BY projectId, sprint";
            } else {
                $query .= " GROUP BY projectId";
            }

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);

            if ($sprintId !== "") {
                $stmn->bindValue(':sprint', $sprintId, PDO::PARAM_STR);
            }

            $stmn->execute();

            $valuesTickets = $stmn->fetch();

            $stmn->closeCursor();

            if (isset($valuesTickets['sum_points']) && $valuesTickets['sum_points'] > 0) {
                $storyPoints = $valuesTickets['sum_points'];
            } else {
                $storyPoints = 1;
            }

            //Timesheet Reports
            $query = "SELECT

                        ROUND(SUM(zp_timesheets.hours), 2) as sum_logged_hours,
                        ROUND(SUM(zp_timesheets.hours) / COUNT(DISTINCT zp_tickets.id), 2) AS daily_avg_hours_booked_todo,
                        ROUND(SUM(zp_timesheets.hours) / :storyPoints, 2) as daily_avg_hours_booked_point

                    FROM zp_tickets
                    LEFT JOIN zp_timesheets ON zp_tickets.id = zp_timesheets.ticketId
                    WHERE projectId = :projectId AND zp_tickets.type <> 'subtask' AND zp_tickets.type <> 'milestone'";

            if ($sprintId !== "") {
                $query .= " AND sprint = :sprint GROUP BY projectId, sprint";
            } else {
                $query .= " GROUP BY projectId";
            }

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':storyPoints', $storyPoints, PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);

            if ($sprintId !== "") {
                $stmn->bindValue(':sprint', $sprintId, PDO::PARAM_STR);
            }

            $stmn->execute();

            $valueTimesheets = $stmn->fetch();

            $stmn->closeCursor();


            //Number of users
            $projectService = app()->make(ProjectRepository::class);
            $users = $projectService->getUsersAssignedToProject($projectId);

            if (is_array($users)) {
                $numberOfUsers = count($users);
            } else {
                $numberOfUsers = 0;
            }

            if (is_array($valuesTickets) && is_array($valueTimesheets)) {
                $values = array_merge($valuesTickets, $valueTimesheets);
                $values["sum_teammembers"] = $numberOfUsers;
            } else {
                $values = false;
            }

            return $values;
        }

        /**
         * @param $projectId
         * @return array|false
         */
        /**
         * @param $projectId
         * @return array|false
         */
        public function checkLastReportEntries($projectId): false|array
        {

            $query = "SELECT * FROM zp_stats WHERE DATE(date) = DATE(NOW() - INTERVAL 1 DAY) AND projectId = :projectId LIMIT 2";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, \Leantime\Domain\Reports\Models\Reports::class);
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * @param $report
         * @return void
         */
        /**
         * @param $report
         * @return void
         */
        public function addReport($report): void
        {
            $report = (object)$report;

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
                           daily_avg_hours_remaining_todo,
                           sum_teammembers)

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
                               :daily_avg_hours_remaining_todo,
                               :sum_teammembers)";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':sprintId', $report->sprintId, PDO::PARAM_INT);
            $stmn->bindValue(':projectId', $report->projectId, PDO::PARAM_INT);
            $stmn->bindValue(':date', $report->date, PDO::PARAM_STR);
            $stmn->bindValue(':sum_todos', $report->sum_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_open_todos', $report->sum_open_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_progres_todos', $report->sum_progres_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_closed_todos', $report->sum_closed_todos, PDO::PARAM_INT);
            $stmn->bindValue(':sum_planned_hours', $report->sum_planned_hours, PDO::PARAM_STR);
            $stmn->bindValue(':sum_estremaining_hours', $report->sum_estremaining_hours, PDO::PARAM_STR);
            $stmn->bindValue(':sum_logged_hours', $report->sum_logged_hours, PDO::PARAM_STR);
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
            $stmn->bindValue(':daily_avg_hours_booked_todo', $report->daily_avg_hours_booked_todo, PDO::PARAM_STR);
            $stmn->bindValue(':daily_avg_hours_booked_point', $report->daily_avg_hours_booked_point, PDO::PARAM_STR);
            $stmn->bindValue(':daily_avg_hours_planned_todo', $report->daily_avg_hours_planned_todo, PDO::PARAM_STR);
            $stmn->bindValue(':daily_avg_hours_planned_point', $report->daily_avg_hours_planned_point, PDO::PARAM_STR);
            $stmn->bindValue(':daily_avg_hours_remaining_point', $report->daily_avg_hours_remaining_point, PDO::PARAM_STR);
            $stmn->bindValue(':daily_avg_hours_remaining_todo', $report->daily_avg_hours_remaining_todo, PDO::PARAM_STR);
            $stmn->bindValue(':sum_teammembers', $report->sum_teammembers, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
        }

        /**
         * @param $sprint
         * @return array
         */
        public function getSprintReport($sprint): array|false
        {

            $query = "SELECT * FROM zp_stats WHERE sprintId = :sprint ORDER BY date ASC";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':sprint', $sprint, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, \Leantime\Domain\Reports\Models\Reports::class);
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * @param $project
         * @return array|false
         */
        public function getBacklogReport($project): false|array
        {

            $query = "SELECT * FROM zp_stats WHERE projectId = :project AND sprintId = 0 ORDER BY date ASC LIMIT 95 ";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':project', $project, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, \Leantime\Domain\Reports\Models\Reports::class);
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * @param $project
         * @return array|false
         */
        public function getFullReport($project): false|array
        {

            $query = "SELECT
                           date,
                           SUM(sum_todos) AS sum_todos,
                           SUM(sum_open_todos) AS sum_open_todos,
                           SUM(sum_progres_todos) AS sum_progres_todos,
                           SUM(sum_closed_todos) AS sum_closed_todos,
                           SUM(sum_planned_hours) AS sum_planned_hours,
                           SUM(sum_estremaining_hours) AS sum_estremaining_hours,
                           ROUND(SUM(sum_logged_hours), 2) AS sum_logged_hours,
                           SUM(sum_points) AS sum_points,
                           SUM(sum_points_done) AS sum_points_done,
                           SUM(sum_points_progress) AS sum_points_progress,
                           SUM(sum_points_open) AS sum_points_open,
                           SUM(sum_todos_xs) AS sum_todos_xs,
                           SUM(sum_todos_s) AS sum_todos_s,
                           SUM(sum_todos_m) AS sum_todos_m,
                           SUM(sum_todos_l) AS sum_todos_l,
                           SUM(sum_todos_xl) AS sum_todos_xl,
                           SUM(sum_todos_xxl) AS sum_todos_xxl,
                           SUM(sum_todos_none) AS sum_todos_none,
                           SUM(tickets) AS tickets,
                           SUM(daily_avg_hours_booked_todo) AS daily_avg_hours_booked_todo,
                           SUM(daily_avg_hours_booked_point) AS daily_avg_hours_booked_point,
                           SUM(daily_avg_hours_planned_todo) AS daily_avg_hours_planned_todo,
                           SUM(daily_avg_hours_planned_point) AS daily_avg_hours_planned_point,
                           SUM(daily_avg_hours_remaining_point) AS daily_avg_hours_remaining_point,
                           SUM(daily_avg_hours_remaining_todo) AS daily_avg_hours_remaining_todo

                        FROM zp_stats WHERE projectId = :project AND (sprintId < 1 || sprintId IS NULL)
                        GROUP BY date
                        ORDER BY date DESC LIMIT 120";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':project', $project, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, \Leantime\Domain\Reports\Models\Reports::class);
            $value = $stmn->fetchAll();

            $stmn->closeCursor();

            return $value;
        }
    }

}
