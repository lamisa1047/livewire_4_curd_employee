<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::employees.index')->name('employees.index');
Route::livewire('/employees', 'pages::employees.index')->name('employees.index');
Route::livewire('/employees/create', 'pages::employees.create')->name('employees.create');
Route::livewire('/employees/{employee}/edit', 'pages::employees.edit')->name('employees.edit');