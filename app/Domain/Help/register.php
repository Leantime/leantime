<?php

use Leantime\Core\Events\EventDispatcher;

EventDispatcher::addEventListener('leantime.domain.auth.*.userSignUpSuccess', function ($params) {

    $userId = session('userdata.id');

    // Create Project
    $projectService = app()->make(\Leantime\Domain\Projects\Services\Projects::class);

    $values = [
        'name' => 'My Project',
        'details' => 'Welcome to your first project in Leantime!<br />This is your space to organize tasks, track goals, and plan your work. Feel free to modify anything here or create additional projects as you grow. This project is just for you to get started',
        'clientId' => 0,
        'hourBudget' => $values['hourBudget'] ?? 0,
        'assignedUsers' => ['id' => session('userdata.id'), 'projectRole' => ''],
        'dollarBudget' => 0,
        'psettings' => 'restricted',
        'type' => 'project',
        'start' => null,
        'end' => null,
    ];

    $projectId = $projectService->addProject($values);

    // Create Milestone
    $ticketService = app()->make(\Leantime\Domain\Tickets\Services\Tickets::class);
    $values = [
        'headline' => '🚀 Getting Started',
        'projectId' => $projectId,
        'editorId' => $userId,
        'userId' => $userId,
        'date' => dtHelper()->userNow()->formatDateTimeForDb(),
        'editFrom' => dtHelper()->userNow()->formatDateTimeForDb(),
        'editTo' => dtHelper()->userNow()->addDays(14)->formatDateTimeForDb(),
        'tags' => '#124F7D',
    ];
    $milestoneId = $ticketService->quickAddMilestone($values);

    // Create Tasks
    $values = [
        'headline' => '',
        'description' => '',
        'projectId' => $projectId,
        'editorId' => $userId,
        'userId' => $userId,
        'dateToFinish' => dtHelper()->userNow()->addDays(3)->formatDateTimeForDb(),
        'milestone' => $milestoneId,
    ];

    $values['headline'] = '💬 Join our community chat';
    $values['description'] = 'Our community chat is a great resource to ask questions and get feedback on project set up. <a href="https://discord.gg/4zMzJtAq9z" target="_blank">Community Chat</a>';
    $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
    $ticketService->quickAddTicket($values);

    if (session('userdata.role') == 'admin'
        || session('userdata.role') == 'owner'
        || session('userdata.role') == 'manager') {

        $values['headline'] = '👥 Invite your team mates';
        $values['description'] = 'Whether you are working with someone or just need an accountability buddy. Using Leantime as a group helps to stay on track and motivated <a href="'.BASE_URL.'/users/showAll">User Management</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);
    }

    $values['headline'] = '🎯 Learn More about Leantime\'s Project Structure';
    $values['description'] = 'We have a lot of additional resources on our help documentation. To learn more about project structure in Leantime and best practices visit: <a href="https://support.leantime.io/en/article/getting-started-in-leantime-an-introduction-to-setting-structure-to-the-work-14t1qip/" target="_blank">https://help.leantime.io</a>';
    $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
    $ticketService->quickAddTicket($values);

    $values['headline'] = '🎯 Create a Goal';
    $values['description'] = 'Goals are used to track and measure long term objectives. They should be measurable using metrics you can update on a regular basis. Goals and Milestones can be connected to view the execution progress while viewing the metric progress <a href="'.BASE_URL.'/goalcanvas/dashboard">Project Goals</a>';
    $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
    $ticketService->quickAddTicket($values);

    $values['headline'] = '🚩 Create a Milestone';
    $values['description'] = 'Milestones allow you to categorize phases of your projects into discrete outcomes. Each milestone has a start and end date and should deliver some output <a href="'.BASE_URL.'/tickets/roadmap/">Project Milestone</a>';
    $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
    $ticketService->quickAddTicket($values);

    $values['headline'] = '🗺️ Explore your Personal Project';
    $values['description'] = 'Your personal project is a space where you can organize your tasks, goals and work. You can access it via the project selector on the top or by clicking this link here: <a href="'.BASE_URL.'/projects/changeCurrentProject/'.$projectId.'/">My Project</a>';
    $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
    $ticketService->quickAddTicket($values);

    $values['headline'] = '🖼️ Complete my Leantime profile';
    $values['description'] = 'Update profile picture and complete work preferences to personalize my experience. <a href="'.BASE_URL.'/users/editOwn/">My Profile</a>';
    $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
    $ticketService->quickAddTicket($values);

    $values['headline'] = '📌 Create your first task';
    $values['description'] = '';
    $values['dateToFinish'] = dtHelper()->userNow();
    $values['status'] = 0;
    $ticketService->quickAddTicket($values);

    // Create Goal
    $goalService = app()->make(\Leantime\Domain\Goalcanvas\Services\Goalcanvas::class);
    $values = [
        'title' => 'My Goals',
        'author' => session('userdata.id'),
        'projectId' => $projectId,
    ];
    $currentCanvasId = $goalService->createGoalboard($values);

    $values = [
        'description' => 'Tasks completed on time', // Metric
        'title' => 'Build My Productivity System', // Objective
        'box' => 'goal',
        'author' => session('userdata.id'),
        'canvasId' => $currentCanvasId,
        'milestoneId' => $milestoneId,
        'startDate' => dtHelper()->userNow()->formatDateForUser(),
        'endDate' => dtHelper()->userNow()->addMonths(2)->formatDateForUser(),
        'metricType' => 'percent',
        'assignedTo' => session('userdata.id'),
        'startValue' => '0',
        'currentValue' => '0',
        'endValue' => '80',
    ];

    $goalService->createGoal($values);

});
