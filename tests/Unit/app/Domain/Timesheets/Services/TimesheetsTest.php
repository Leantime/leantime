<?php
namespace Tests\Unit\App\Domain\Timesheets\Services;

use PHPUnit\Framework\TestCase;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetsRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Illuminate\Support\Facades\Session;
use Leantime\Domain\Tickets\Models\Tickets as Tickets;
use Mockery;


class TimesheetsTest extends TestCase
{
    private Timesheets $timesheets;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepoMock = $this->createMock(UserRepository::class);
        $timesheetsRepoMock = $this->createMock(TimesheetsRepository::class);

        $this->timesheets = new Timesheets($timesheetsRepoMock, $userRepoMock);
    }

    public function test_parsing_seven_times_ten_minutes(): void
    {
        $ticketId = 123; 
        $params = [
            'userId' => 1,
            'date' => '2025-11-03',
            'kind' => 'work',
            'hours' => '10m',
            'description' => 'Test log'
        ];

        $totalHours = 0;

        for ($i = 0; $i < 7; $i++) {
            $totalHours += $this->timesheets->parseTimeToDecimal($params['hours']);
        }

        $expected = round(7 * (10 / 60), 4);
        $this->assertEquals(round($expected, 4), $this->timesheets->parseTimeToDecimal($totalHours), 'Total logged time should be 1h10m in decimal');
    }

    public function test_parsing_seventy_times_ten_minutes(): void
    {
        $toleration = 0.0002;
        $ticketId = 123; 
        $params = [
            'userId' => 1,
            'date' => '2025-11-03',
            'kind' => 'work',
            'hours' => '10m',
            'description' => 'Test log'
        ];

        $totalHours = 0;

        for ($i = 0; $i < 70; $i++) {
            $totalHours += $this->timesheets->parseTimeToDecimal($params['hours']);
        }

        $expected = round(70 * (10 / 60), 4);
        $this->assertEquals(round($expected + $toleration, 4), $this->timesheets->parseTimeToDecimal($totalHours), 'Total logged time should be 1h10m in decimal');
    }

    public function test_get_sum_logged_hours_for_ticket(): void
{
    $ticketId = 123;

    $timesheetsMock = $this->getMockBuilder(Timesheets::class)
        ->onlyMethods(['getLoggedHoursForTicketByDate'])
        ->disableOriginalConstructor()
        ->getMock();

    $timesheetsMock->method('getLoggedHoursForTicketByDate')
        ->with($ticketId)
        ->willReturn([
            ['summe' => 2.5],
            ['summe' => 1.75],
            ['summe' => 0],      
            ['summe' => null],   
        ]);

    $sum = $timesheetsMock->getSumLoggedHoursForTicket($ticketId);

    $this->assertEquals(4.25, $sum, 'Sum of logged hours should be correct');
}

public function test_get_remaining_hours_returns_zero_when_over_budget()
{
    $ticket = new Tickets();
    $ticket->id = 2;
    $ticket->planHours = 10;
    
    $repoMock = $this->getMockBuilder(TimesheetsRepository::class)
        ->disableOriginalConstructor()
        ->getMock();
    
    $repoMock->method('getLoggedHoursForTicket')
             ->with($ticket->id)
             ->willReturn([
                 ['summe' => 10],
                 ['summe' => 5]
             ]);
    
    $userRepoMock = $this->getMockBuilder(UserRepository::class)
        ->disableOriginalConstructor()
        ->getMock();
    
    $service = new Timesheets($repoMock, $userRepoMock);
    
    $remaining = $service->getRemainingHours($ticket);
    
    $this->assertEquals(0, $remaining);
}

public function test_get_remaining_hours_returns_positive_number_of_hours()
{
    $ticket = new Tickets();
    $ticket->id = 2;
    $ticket->planHours = 10;
    
    $repoMock = $this->getMockBuilder(TimesheetsRepository::class)
        ->disableOriginalConstructor()
        ->getMock();
    
    $repoMock->method('getLoggedHoursForTicket')
             ->with($ticket->id)
             ->willReturn([
                 ['summe' => 4],
                 ['summe' => 5]
             ]);
    
    $userRepoMock = $this->getMockBuilder(UserRepository::class)
        ->disableOriginalConstructor()
        ->getMock();
    
    $service = new Timesheets($repoMock, $userRepoMock);
    
    $remaining = $service->getRemainingHours($ticket);
    
    $this->assertEquals(1, $remaining);
}
}