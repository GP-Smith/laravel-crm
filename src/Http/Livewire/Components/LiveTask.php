<?php

namespace VentureDrake\LaravelCrm\Http\Livewire\Components;

use Carbon\Carbon;
use Livewire\Component;
use VentureDrake\LaravelCrm\Models\Task;
use VentureDrake\LaravelCrm\Traits\HasGlobalSettings;
use VentureDrake\LaravelCrm\Traits\NotifyToast;

class LiveTask extends Component
{
    use NotifyToast;
    use HasGlobalSettings;
    
    public $task;
    public $editMode = false;
    public $name;
    public $description;
    public $due_at;
    public $view;

    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];
    
    public function mount(Task $task, $view = 'task')
    {
        $this->task = $task;
        $this->name = $task->name;
        $this->description = $task->description;
        $this->due_at = ($task->due_at) ? $task->due_at->format($this->dateFormat().' H:i') : null;
        $this->view = $view;
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'name' => "required",
            'description' => "nullable",
            'due_at' => "nullable",
        ];
    }

    public function update()
    {
        $this->validate();
        $this->task->update([
            'name' => $this->name,
            'description' => $this->description,
            'due_at' => $this->due_at,
        ]);
        $this->toggleEditMode();
        $this->emit('refreshComponent');
        $this->notify(
            ucfirst(trans('laravel-crm::lang.task_updated'))
        );
    }

    public function complete()
    {
        $this->task->update([
            'completed_at' => Carbon::now(),
        ]);

        $this->emit('taskCompleted');
        $this->notify(
            ucfirst(trans('laravel-crm::lang.task_completed'))
        );
    }

    public function delete()
    {
        $this->task->delete();

        $this->emit('taskDeleted');
        $this->notify(
            ucfirst(trans('laravel-crm::lang.task_deleted'))
        );
    }

    public function toggleEditMode()
    {
        $this->editMode = ! $this->editMode;
        
        $this->dispatchBrowserEvent('taskEditModeToggled');
    }
    
    public function render()
    {
        return view('laravel-crm::livewire.components.'.$this->view);
    }
}
