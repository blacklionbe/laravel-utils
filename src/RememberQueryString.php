<?php

namespace BlackLion\LaravelUtils;

trait RememberQueryString
{
    public $remember;

    protected $queryStringRememberQueryString = ['remember'];

    public function mountRememberQueryString()
    {
        if (request('remember') === 'forget') {
            session()->remove($this->getRememberQueryStringSessionName());
            $this->remember = null;

            return;
        }

        $data = session()->get($this->getRememberQueryStringSessionName()) ?? [];
        $instance = new static($this->id);

        $this->getQueryStringKeysToRemember()->each(function ($key) use ($data, $instance) {
            if (isset($data[$key]) && $this->{$key} === $instance->{$key}) {
                if ($key === 'page' && in_array('Livewire\WithPagination', class_uses_recursive($this))) {
                    $this->setPage($data[$key]);
                } else {
                    $this->{$key} = $data[$key];
                }
            }
        });
    }

    public function dehydrateRememberQueryString()
    {
        session()->put(
            $this->getRememberQueryStringSessionName(),
            $this->getQueryStringKeysToRemember()->mapWithKeys(function ($key) {
                return [$key => $this->{$key}];
            })->toArray(),
        );
    }

    protected function getRememberQueryStringSessionName()
    {
        return 'remember_query_string.'.str_replace('.', '-', $this->getName());
    }

    protected function getQueryStringKeysToRemember()
    {
        return collect($this->getQueryString())
            ->map(function ($value, $key) {
                if (! is_string($key)) {
                    $key = $value;
                    $value = [];
                }

                if ($key !== 'remember' && ($value['remember'] ?? true)) {
                    return $key;
                }
            })
            ->filter()
            ->values();
    }
}
