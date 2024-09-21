<?php

namespace VariableSign\DataTable\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class DataTableMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:datatable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new datatable class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'DataTable';

    private string $dataSource = '';

    private string $withExample = '';

    private string $withModel = '';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = match ($this->dataSource) {
            'Eloquent Builder' => '.eloquent',
            'Query Builder' => '',
            'Collection' => '.collection',
            default => ''
        };

        if ($this->withExample === 'Yes') {
            return $this->resolveStubPath('/stubs/datatable' . $stub . '.example.stub');
        } else {
            return $this->resolveStubPath('/stubs/datatable' . $stub . '.stub');
        }
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../..'.$stub;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $this->dataSource = select(
            label: 'What type of data source will you use?',
            options: ['Eloquent Builder', 'Query Builder', 'Collection'],
            default: 'Eloquent Builder'
        );

        $this->withExample = select(
            label: 'Would you like to include an example code?',
            options: ['Yes', 'No'],
            default: 'Yes'
        );

        $class = class_basename(Str::ucfirst($name));
        $namespaceModel = $this->qualifyModel($this->guessModelName($name));
        $model = class_basename($namespaceModel);
        $namespace = $this->getNamespace(
            Str::replaceFirst($this->rootNamespace(), 'App\\' . config('datatable.directory') . '\\', $this->qualifyClass($this->getNameInput()))
        );

        $replace = [
            '{{ datatableNamespace }}' => $namespace,
            '{{ datatableClass }}' => $class
        ];

        if ($this->dataSource !== 'Collection') {
            $modelName = '';

            $this->withModel = select(
                label: 'Select a Model or manually enter its name.',
                options: [$namespaceModel, 'Manual'],
                default: 'Manual'
            );

            if ($this->withModel === 'Manual') {
                $modelName = text(
                    label: 'What is the Model name?',
                    required: true
                );
            }

            $manualModel = $this->qualifyModel($modelName);
            $modelClass = '\\' . $this->withModel === 'Manual' ? $manualModel : $this->withModel;
            $model = $this->withModel === 'Manual' ? $modelName : $model;
            $namespaceModel = $this->withModel === 'Manual' ? $manualModel : $namespaceModel;
            $defaultColumn = class_exists($modelClass) ? (new $modelClass)->{'getKeyName'}() : 'id';
    
            $splitModelName = implode(' ', Str::ucsplit($model));
            $modelLower = Str::lower($splitModelName);
    
            $replace = array_merge($replace, [
                '{{ namespacedModel }}' => $namespaceModel,
                '{{ model }}' => $model,
                '{{ modelLower }}' => $modelLower,
                '{{ modelLowerPlural }}' => Str::plural($modelLower),
                '{{ defaultColumn }}' => $defaultColumn
            ]);
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = (string) Str::of($name)->replaceFirst($this->rootNamespace(), '');

        return $this->laravel->basePath().'/app/' . config('datatable.directory') . '/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Guess the model name from the Factory name or return a default model name.
     *
     * @param  string  $name
     * @return string
     */
    protected function guessModelName($name)
    {
        if (str_ends_with($name, $this->type)) {
            $name = substr($name, 0, -9);
        }

        if (str_ends_with($name, 'Table')) {
            $name = substr($name, 0, -5);
        }

        $nameOnly = Str::afterLast($name, '\\');
        $modelName = $this->qualifyModel(Str::after($name, $this->rootNamespace()));
        
        if (Str::contains($name, '\\')) {
            $modelName = $this->qualifyModel($nameOnly);
        }

        if (class_exists($modelName)) {
            return $modelName;
        }

        if (is_dir(app_path('Models/'))) {
            return $this->rootNamespace().'Models\\' . $nameOnly;
        }

        return $this->rootNamespace() . $nameOnly;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    // protected function getOptions(): array
    // {
    //     return [
    //         ['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model.'],
    //         ['example', 'e', InputOption::VALUE_NONE, 'Include example code snippets.']
    //     ];
    // }

    // private function getClassName($name)
    // {
    //     return class_basename(Str::ucfirst($name));
    // }
}
