<?php 

namespace VariableSign\DataTable\Traits;

use Closure;

trait InteractsWithFilter
{
    private ?object $builder = null;

    private array $options = [];

    public function builder(?Closure $callback = null): self
    {
        $this->builder = $callback;
        
        return $this;
    }

    public function collection(?Closure $callback = null): self
    {
        $this->builder = $callback;
        
        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function __call($name, $arguments)
    {
        return call_user_func([$this, $name], ...$arguments);
    }
	
    public function __get($name)
	{
		return $this->{$name};
	}
}
