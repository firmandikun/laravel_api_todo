<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailable as BaseMailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;

class TaskReminderEmail extends BaseMailable implements Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $reminderType;

    /**
     * Create a new message instance.
     *
     * @param Task $task The task instance
     * @param string $reminderType The reminder type ('due', 'completed', etc.)
     * @return void
     */
    public function __construct(Task $task, $reminderType)
    {
        $this->task = $task;
        $this->reminderType = $reminderType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.task_reminder')
                    ->subject('Reminder: ' . $this->task->title)
                    ->with([
                        'task' => $this->task,
                        'reminderType' => $this->reminderType,
                    ]);
    }
}
