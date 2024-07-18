<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Notification;


use App\Mail\TaskReminderEmail;
use Illuminate\Support\Facades\Response;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Support\Facades\Mail;

class TaskController extends Controller
{ public function index(Request $request)
    {
        $tasks = Task::query();

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'complete') {
                $tasks->where('completed', true);
            } elseif ($status === 'incomplete') {
                $tasks->where('completed', false);
            }
        }

        if ($request->has('due_date')) {
            $dueDate = $request->input('due_date');
            $tasks->whereDate('due_date', $dueDate);
        }

        if ($request->has('priority')) {
            $priority = $request->input('priority');
            $tasks->where('priority', $priority);
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $tasks->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        $filteredTasks = $tasks->get();

        return response()->json(['tasks' => $filteredTasks]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:Low,Medium,High',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'Medium',
        ]);

        return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:Low,Medium,High',
            // 'completed' => 'nullable|boolean',
        ]);

        $task->update($request->only(['title', 'description', 'due_date', 'priority']));

        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'completed' => 'nullable|boolean',
        ]);

        $status = 'Backlog';

        if ($request->completed === true) {
            $status = 'Done';
        } elseif ($request->completed === false) {
            $status = 'In Progress';
        }

        $task->status = $status;
        $task->save();

        if ($task->status === 'Done') {
            Notification::route('mail', 'example@example.com')->notify(new TaskCompletedNotification($task));
        }

        return response()->json(['message' => 'Task status updated successfully', 'task' => $task]);
    }

    public function exportCsv(Request $request)
    {
        $tasks = Task::query();

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'complete') {
                $tasks->where('completed', true);
            } elseif ($status === 'incomplete') {
                $tasks->where('completed', false);
            }
        }

        if ($request->has('due_date')) {
            $dueDate = $request->input('due_date');
            $tasks->whereDate('due_date', $dueDate);
        }

        if ($request->has('priority')) {
            $priority = $request->input('priority');
            $tasks->where('priority', $priority);
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $tasks->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        $filteredTasks = $tasks->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tasks.csv"',
        ];

        $callback = function () use ($filteredTasks) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Title', 'Description', 'Due Date', 'Priority', 'Status']);

            foreach ($filteredTasks as $task) {
                fputcsv($file, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->due_date,
                    $task->priority,
                    $task->status
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }


    public function exportJson(Request $request)
    {

        $tasks = Task::query();

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'complete') {
                $tasks->where('completed', true);
            } elseif ($status === 'incomplete') {
                $tasks->where('completed', false);
            }
        }

        if ($request->has('due_date')) {
            $dueDate = $request->input('due_date');
            $tasks->whereDate('due_date', $dueDate);
        }

        if ($request->has('priority')) {
            $priority = $request->input('priority');
            $tasks->where('priority', $priority);
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $tasks->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        $filteredTasks = $tasks->get();

        return response()->json(['tasks' => $filteredTasks]);
    }


    public function show($id)
    {
        return response()->json(['message' => 'This is a placeholder']);
    }


}
