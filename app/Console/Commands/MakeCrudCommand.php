<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud {name : Nama Model (contoh: User, Department, Product)}';
    protected $description = 'Membuat modul CRUD lengkap (Model, Controller, Service, Request, Views) sesuai standar enterprise dengan fitur skip jika file sudah ada';

    public function handle(): void
    {
        $name = $this->argument('name');

        // Konvensi Penamaan yang Konsisten
        $modelName = Str::studly(Str::singular($name));
        $pluralKebab = Str::kebab(Str::plural($modelName));
        $pluralSnake = Str::snake(Str::plural($modelName));
        $singularSnake = Str::snake(Str::singular($modelName));
        $variableName = Str::camel(Str::plural($modelName));
        $singularVariable = Str::camel(Str::singular($modelName));

        $this->info("Memproses modul CRUD untuk: {$modelName}...");

        // 0. Generate Model
        $this->generateModel($modelName);

        // 1. Generate Form Request
        $this->generateRequest($modelName, $singularVariable, $pluralSnake);

        // 2. Generate Service
        $this->generateService($modelName, $variableName, $singularVariable);

        // 3. Generate Controller
        $this->generateController($modelName, $pluralKebab, $pluralSnake, $variableName, $singularVariable);

        // 4. Generate Views (Index & Form Modal)
        $this->generateViews($pluralKebab, $singularVariable, $variableName, $modelName);

        $this->info("Proses scaffolding modul [{$modelName}] selesai!");
    }

    protected function generateModel(string $modelName): void
    {
        $path = app_path("Models/{$modelName}.php");

        if (File::exists($path)) {
            $this->line("<comment>Skipped Model (sudah ada):</comment> {$path}");
            return;
        }

        $stub = File::get(base_path('stubs/custom-crud/model.stub'));
        $stub = str_replace([
            '{{modelName}}'
        ], [
            $modelName
        ], $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("<info>Created Model:</info> {$path}");
    }

    protected function generateRequest(string $modelName, string $singularVariable, string $pluralSnake): void
    {
        $path = app_path("Http/Requests/{$modelName}Request.php");

        if (File::exists($path)) {
            $this->line("<comment>Skipped Request (sudah ada):</comment> {$path}");
            return;
        }

        $stub = File::get(base_path('stubs/custom-crud/request.stub'));
        $stub = str_replace([
            '{{class}}',
            '{{singularVariable}}',
            '{{pluralSnake}}'
        ], [
            "{$modelName}Request",
            $singularVariable,
            $pluralSnake
        ], $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->line("<info>Created Request:</info> {$path}");
    }

    protected function generateService(string $modelName, string $variableName, string $singularVariable): void
    {
        $path = app_path("Services/{$modelName}Service.php");

        if (File::exists($path)) {
            $this->line("<comment>Skipped Service (sudah ada):</comment> {$path}");
            return;
        }

        $stub = File::get(base_path('stubs/custom-crud/service.stub'));
        $stub = str_replace([
            '{{modelName}}',
            '{{variableName}}',
            '{{singularVariable}}'
        ], [
            $modelName,
            $variableName,
            $singularVariable
        ], $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->line("<info>Created Service:</info> {$path}");
    }

    protected function generateController(string $modelName, string $pluralKebab, string $pluralSnake, string $variableName, string $singularVariable): void
    {
        $path = app_path("Http/Controllers/{$modelName}Controller.php");

        if (File::exists($path)) {
            $this->line("<comment>Skipped Controller (sudah ada):</comment> {$path}");
            return;
        }

        $stub = File::get(base_path('stubs/custom-crud/controller.stub'));
        $stub = str_replace([
            '{{modelName}}',
            '{{pluralKebab}}',
            '{{pluralSnake}}',
            '{{variableName}}',
            '{{singularVariable}}'
        ], [
            $modelName,
            $pluralKebab,
            $pluralSnake,
            $variableName,
            $singularVariable
        ], $stub);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $stub);

        $this->info("<info>Created Controller:</info> {$path}");
    }

    protected function generateViews(string $pluralKebab, string $singularVariable, string $variableName, string $modelName): void
    {
        $viewPath = resource_path("views/{$pluralKebab}");
        $indexPath = "{$viewPath}/index.blade.php";
        $formPath = "{$viewPath}/partials/{$pluralKebab}-form.blade.php";

        File::ensureDirectoryExists($viewPath);
        File::ensureDirectoryExists("{$viewPath}/partials");

        // Index View
        if (File::exists($indexPath)) {
            $this->line("<comment>Skipped Index View (sudah ada):</comment> {$indexPath}");
        } else {
            $indexStub = File::get(base_path('stubs/custom-crud/index.stub'));
            $indexStub = str_replace([
                '{{pluralKebab}}',
                '{{singularVariable}}',
                '{{variableName}}',
                '{{modelName}}'
            ], [
                $pluralKebab,
                $singularVariable,
                $variableName,
                $modelName
            ], $indexStub);
            File::put($indexPath, $indexStub);
            $this->line("<info>Created Index View:</info> {$indexPath}");
        }

        // Form Partial View
        if (File::exists($formPath)) {
            $this->line("<comment>Skipped Form View (sudah ada):</comment> {$formPath}");
        } else {
            $formStub = File::get(base_path('stubs/custom-crud/form.stub'));
            $formStub = str_replace([
                '{{pluralKebab}}',
                '{{singularVariable}}',
                '{{modelName}}'
            ], [
                $pluralKebab,
                $singularVariable,
                $modelName
            ], $formStub);
            File::put($formPath, $formStub);
            $this->line("<info>Created Form View:</info> {$formPath}");
        }
    }
}
