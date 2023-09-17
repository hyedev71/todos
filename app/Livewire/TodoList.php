<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\Todo;
use Livewire\WithPagination;
use Exception;

class TodoList extends Component
{

	use WithPagination;

	#[Rule('required|min:3|max:50')]
	public $name;

	public $search;

	public $edit_todo_id;

	#[Rule('required|min:3|max:50')]
	public $edit_name;

	public function create()
	{
		// validate
		$validated = $this->validateOnly('name');

		// create the todo
		Todo::create($validated);

		// clear the input 
		$this->reset('name');

		// send flash message
		session()->flash('success', 'Created.');

		// reset pagination
		$this->resetPage();
	}

	public function toggle($todo_id)
	{
		try 
		{
			$todo = Todo::findOrfail($todo_id);
			$todo->completed = !$todo->completed;
			$todo->save();
		}
		catch (Exception $e)
		{
			session()->flash('error', "Failed to update todo completed!");
			return;
		}
	}

	public function edit($todo_id)
	{
		$this->edit_todo_id = $todo_id;
		$this->edit_name = Todo::find($todo_id)->name;
	}

	public function cancelEdit()
	{
		$this->reset('edit_todo_id', 'edit_name');
	}

	public function update()
	{
		$this->validateOnly('edit_name');

		try
		{
			Todo::findOrfail($this->edit_todo_id)->update([
				'name' => $this->edit_name,
			]);
		}
		catch (Exception $e)
		{
			session()->flash('error', 'Failed to update todo!');
			return;
		}

		$this->cancelEdit();
	}

	public function delete($todo_id)
	{
		try 
		{
			Todo::findOrfail($todo_id)->delete();
		}
		catch (Exception $e)
		{
			session()->flash('error', 'Failed to delete todo!');
			return;
		}
	}

    public function render()
    {
		return view('livewire.todo-list',[
			'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(3),
		]);
    }
}
