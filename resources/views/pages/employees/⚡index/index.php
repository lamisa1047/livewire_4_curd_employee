<?php

use Livewire\Component;
use App\Models\Employee;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Computed;

new #[Title('Employees')] class extends Component {
    use WithPagination;
    use WithFileUploads;

    // Add property for import
    public $importFile;

    // Filters
    public $search = '';
    public $department = '';
    public $status = '';
    public $sortField = 'first_name';
    public $sortDirection = 'asc';

    // Pagination
    public $perPage = 10;

    // Selected employees for bulk actions
    public $selected = [];
    public $selectAll = false;

    // Modal state
    public $showDeleteModal = false;
    public $employeeToDelete = null;

    // Query string for URL persistence
    protected $queryString = [
        'search' => ['except' => ''],
        'department' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'first_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->department, fn($q) => $q->where('department', $this->department))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function departments()
    {
        return Employee::distinct('department')
            ->pluck('department')
            ->sort();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDepartment()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'department', 'status']);
        $this->resetPage();
    }

    public function confirmDelete($employeeId)
    {
        $this->employeeToDelete = $employeeId;
        $this->showDeleteModal = true;
    }

    public function deleteEmployee()
    {
        if ($this->employeeToDelete) {
            Employee::find($this->employeeToDelete)->delete();
            $this->showDeleteModal = false;
            $this->employeeToDelete = null;
            
            session()->flash('message', 'Employee deleted successfully.');
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->employees->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function bulkDelete()
    {
        Employee::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Selected employees deleted successfully.');
    }

    public function exportPdf()
{
    $employees = Employee::query()
        ->when($this->search, fn($q) => $q->search($this->search))
        ->when($this->department, fn($q) => $q->where('department', $this->department))
        ->when($this->status, fn($q) => $q->where('status', $this->status))
        ->orderBy($this->sortField, $this->sortDirection)
        ->get();

    $pdf = Pdf::loadView('employees.pdf', [
        'employees' => $employees
    ]);

    return response()->streamDownload(function() use ($pdf) {
        echo $pdf->stream();
    }, 'employees-' . now()->format('Y-m-d') . '.pdf');
}

public function exportSelected()
{
    $employees = Employee::whereIn('id', $this->selected)->get();

    $pdf = Pdf::loadView('employees.pdf', [
        'employees' => $employees
    ]);

    return response()->streamDownload(function() use ($pdf) {
        echo $pdf->stream();
    }, 'employees-selected-' . now()->format('Y-m-d') . '.pdf');
}

// public function exportExcel()
// {
//     return Excel::download(
//         new EmployeesExport($this->getFilteredEmployees()),
//         'employees-' . now()->format('Y-m-d') . '.xlsx'
//     );
// }

// public function exportSelectedExcel()
// {
//     $employees = Employee::whereIn('id', $this->selected)->get();
    
//     return Excel::download(
//         new EmployeesExport($employees),
//         'employees-selected-' . now()->format('Y-m-d') . '.xlsx'
//     );
// }

// public function import()
// {
//     $this->validate([
//         'importFile' => 'required|mimes:xlsx,xls,csv|max:2048',
//     ]);

//     try {
//         Excel::import(new EmployeesImport, $this->importFile->path());
        
//         session()->flash('message', 'Employees imported successfully.');
        
//         $this->importFile = null;
//     } catch (\Exception $e) {
//         session()->flash('error', 'Import failed: ' . $e->getMessage());
//     }
// }

private function getFilteredEmployees()
{
    return Employee::query()
        ->when($this->search, fn($q) => $q->search($this->search))
        ->when($this->department, fn($q) => $q->where('department', $this->department))
        ->when($this->status, fn($q) => $q->where('status', $this->status))
        ->orderBy($this->sortField, $this->sortDirection)
        ->get();
}
};