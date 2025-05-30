<?php

namespace App\Console;

use App\Http\Controllers\Sadmin\UploadFirebaseController;
use App\Http\Traits\SendMailTrait;
use App\Models\MailLog;
use App\Models\OpenStaseTask;
use App\Models\Presence;
use App\Models\Student;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    use SendMailTrait;
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Menghapus form penilain lebih dari 2 bulan.
        $schedule->call(function () {
            OpenStaseTask::whereDate('created_at', '<', date('Y-m-d H:i:s', strtotime(now() . ' -6 month')))
                ->doesntHave('files')
                ->delete();
        })->daily();

        //Kirim email Konfirmasi
        $schedule->call(function () {
           $fb = new UploadFirebaseController();
           $fb->uploadDocument();
           $fb->uploadFile();
           $fb->uploadPresenceCheckin();
           $fb->uploadStudentLog();
        })->everyMinute();

        $schedule->call(function () {
            $students = Student::get();
            $count = 0;
            foreach ($students as $student){
                $presence = Presence::whereStudentId($student->id)
                    ->orderByDesc('id')
                    ->first();
                if($presence != null){
                    if ($presence->checkout === null) {

                        if ((time() - strtotime($presence->checkin)) > (60 * 60 * 12)) {
                            $presence->update([
                                'checkout'      => now(),
                                'checkout_data' => "{}",
                                'checkout_note' => 'by_system',
                            ]);

                            $count++;
                        }
                    }
                }
            }

        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected function osProcessIsRunning($needle)
    {
        // get process status. the "-ww"-option is important to get the full output!
        exec('ps aux -ww', $process_status);

        // search $needle in process status
        $result = array_filter($process_status, function($var) use ($needle) {
            return strpos($var, $needle);
        });

        // if the result is not empty, the needle exists in running processes
        if (!empty($result)) {
            return true;
        }
        return false;
    }
}
