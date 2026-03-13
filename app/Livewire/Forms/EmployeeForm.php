<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use App\Models\Employee;

class EmployeeForm extends Form
{
    public ?Employee $employee = null;

    #[Validate('required|min:2')]
    public $first_name = '';

    #[Validate('required|min:2')]
    public $last_name = '';

    #[Validate('required|email|unique:employees,email')]
    public $email = '';

    #[Validate('nullable|string')]
    public $phone = '';

    #[Validate('required')]
    public $department = '';

    #[Validate('required')]
    public $position = '';

    #[Validate('required|numeric|min:0')]
    public $salary = '';

    #[Validate('required|date')]
    public $hire_date = '';

    #[Validate('required|in:active,inactive')]
    public $status = 'active';

    public function setEmployee(Employee $employee)
    {
        $this->employee = $employee;
        
        $this->first_name = $employee->first_name;
        $this->last_name = $employee->last_name;
        $this->email = $employee->email;
        $this->phone = $employee->phone;
        $this->department = $employee->department;
        $this->position = $employee->position;
        $this->salary = $employee->salary;
        $this->hire_date = $employee->hire_date->format('Y-m-d');
        $this->status = $employee->status;
    }

    public function store()
    {
        $this->validate();

        Employee::create($this->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'department',
            'position',
            'salary',
            'hire_date',
            'status',
        ]));

        $this->reset();
    }

    public function update()
    {
        // Update email validation to ignore current employee
        $this->validate([
            'email' => "required|email|unique:employees,email,{$this->employee->id}",
        ]);

        $this->employee->update($this->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'department',
            'position',
            'salary',
            'hire_date',
            'status',
        ]));
    }
}